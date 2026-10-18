<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace mod_aiqbank\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Builds prompts, calls the AI client in batches and normalises the answers.
 *
 * @package    mod_aiqbank
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_generator {

    /** @var ai_client */
    protected $client;
    /** @var int */
    protected $batchsize;
    /** @var string[] */
    protected $warnings = [];

    /**
     * Constructor.
     *
     * @param ai_client $client AI client.
     * @param int $batchsize Questions requested per call.
     */
    public function __construct(ai_client $client, int $batchsize = 10) {
        $this->client = $client;
        $this->batchsize = max(1, $batchsize);
    }

    /**
     * Warnings produced during the last generate() call (e.g. partial results).
     *
     * @return string[]
     */
    public function get_warnings(): array {
        return $this->warnings;
    }

    /**
     * Describe the audience for a given age.
     *
     * @param int $age Age in years.
     * @return string
     */
    public static function age_descriptor(int $age): string {
        if ($age <= 5) {
            return "preschool children about {$age} years old. Use very short, simple sentences and concrete everyday ideas "
                . '(colours, animals, family, objects, places). No abstract concepts. Options must be only a few words long. '
                . 'Cognitive complexity: only recognising and remembering (Bloom: remember).';
        }
        if ($age <= 8) {
            return "early primary school children about {$age} years old. Use short sentences, common words and concrete examples. "
                . 'Cognitive complexity: remember and basic understanding.';
        }
        if ($age <= 12) {
            return "primary school students about {$age} years old. Use clear language and simple reasoning appropriate for that age. "
                . 'Cognitive complexity: remember, understand and simple application with everyday examples.';
        }
        if ($age <= 17) {
            return "secondary school students about {$age} years old. Use appropriate subject terminology and moderate abstraction. "
                . 'Cognitive complexity: understand, apply and basic analysis (cause and effect, comparing, classifying), '
                . 'with only some pure recall questions.';
        }
        return "university-level or adult learners about {$age} years old. Use precise technical terminology. "
            . 'Cognitive complexity: mostly apply, analyse and evaluate (case-based or scenario questions, comparing approaches, '
            . 'identifying errors); avoid pure memorisation and trivial distractors.';
    }

    /**
     * Generate questions.
     *
     * @param string[] $topics Topics to cover.
     * @param string[] $stems Teacher questions that still need answer options.
     * @param int $total Total number of questions wanted (stems are included in the total).
     * @param int $age Target age.
     * @param string $qtype multichoice|truefalse|mixed
     * @param string $language Language name for the questions (e.g. "Spanish").
     * @return array[] Normalised questions.
     * @throws \moodle_exception If nothing could be generated.
     */
    public function generate(array $topics, array $stems, int $total, int $age, string $qtype, string $language): array {
        $this->warnings = [];
        $questions = [];
        $seen = [];

        try {
            // Phase 1: complete the teacher's own questions with answer options.
            foreach (array_chunk(array_values($stems), $this->batchsize) as $chunk) {
                $items = $this->run_batch(count($chunk), $topics, $chunk, $age, $qtype, $language, $questions);
                $this->add_unique($items, $questions, $seen, count($chunk));
            }

            // Phase 2: generate the rest from the topics.
            $needed = max(0, $total - count($stems));
            $generated = 0;
            $attempts = 0;
            $maxattempts = (int) ceil($needed / $this->batchsize) + 2;
            while ($generated < $needed && $attempts < $maxattempts) {
                $n = min($this->batchsize, $needed - $generated);
                $before = count($questions);
                $items = $this->run_batch($n, $topics, [], $age, $qtype, $language, $questions);
                $this->add_unique($items, $questions, $seen, $n);
                $generated += count($questions) - $before;
                $attempts++;
            }
        } catch (\moodle_exception $e) {
            if (empty($questions)) {
                throw $e;
            }
            $this->warnings[] = $e->getMessage();
        }

        return $questions;
    }

    /**
     * Run one AI call and return the decoded question items.
     *
     * @param int $n Questions requested.
     * @param string[] $topics
     * @param string[] $stems
     * @param int $age
     * @param string $qtype
     * @param string $language
     * @param array[] $existing Questions already produced (to avoid repeats).
     * @return array[]
     */
    protected function run_batch(int $n, array $topics, array $stems, int $age, string $qtype,
            string $language, array $existing): array {
        $avoid = [];
        foreach (array_slice($existing, -40) as $q) {
            $avoid[] = \core_text::substr($q['text'], 0, 90);
        }
        $system = 'You are an expert instructional designer who writes accurate, well-balanced assessment questions '
            . 'for a school or university LMS. You always reply with one single valid JSON object and nothing else.';
        $prompt = $this->build_prompt($n, $topics, $stems, $age, $qtype, $language, $avoid);
        return $this->parse($this->client->complete($system, $prompt));
    }

    /**
     * Build the user prompt.
     *
     * @param int $n
     * @param string[] $topics
     * @param string[] $stems
     * @param int $age
     * @param string $qtype
     * @param string $language
     * @param string[] $avoid
     * @return string
     */
    protected function build_prompt(int $n, array $topics, array $stems, int $age, string $qtype,
            string $language, array $avoid): string {
        switch ($qtype) {
            case 'truefalse':
                $typerule = 'Use ONLY "truefalse" questions (a statement that is either true or false).';
                break;
            case 'mixed':
                $typerule = 'Mix the types: about 70% "multichoice" and 30% "truefalse".';
                break;
            default:
                $typerule = 'Use ONLY "multichoice" questions with exactly 4 options.';
        }

        $lines = [];
        if ($stems) {
            $lines[] = "The teacher wrote {$n} question(s) WITHOUT answer options. Keep each question's meaning "
                . '(you may only fix spelling or grammar) and create the answer options and feedback for each of them. '
                . 'Return them in the same order, and return exactly ' . $n . ' items. Use a "multichoice" question with '
                . '4 options, unless the text is clearly a statement to judge and true/false questions are allowed.';
            $lines[] = 'Teacher questions:';
            foreach ($stems as $i => $stem) {
                $lines[] = ($i + 1) . '. ' . $stem;
            }
            if ($topics) {
                $lines[] = 'General context of the course topics: ' . implode('; ', $topics) . '.';
            }
        } else {
            $lines[] = "Create exactly {$n} assessment questions.";
            $lines[] = 'Topics (cover them evenly): ' . implode('; ', $topics) . '.';
            $lines[] = $typerule;
        }

        $lines[] = "Language of all the text: {$language}.";
        $lines[] = 'Target audience: ' . self::age_descriptor($age);
        $lines[] = 'Rules:';
        $lines[] = '- Facts must be correct. Questions must not repeat each other.';
        $lines[] = '- "multichoice": exactly 4 options, exactly ONE correct. Distractors must be plausible and similar in length '
            . 'to the correct option. Never use "all of the above" or "none of the above". Vary the position of the correct option.';
        $lines[] = '- "truefalse": "answer" is a JSON boolean.';
        $lines[] = '- "feedback": one short sentence explaining why the correct answer is right.';
        $lines[] = '- Plain text only: no markdown, no HTML, no numbering or letters in front of options.';
        if ($avoid) {
            $lines[] = 'Do NOT repeat or rephrase these existing questions: ' . implode(' | ', $avoid);
        }
        $lines[] = 'Reply with JSON in exactly this shape:';
        $lines[] = '{"questions":[{"type":"multichoice","text":"...","options":[{"text":"...","correct":false},'
            . '{"text":"...","correct":true},{"text":"...","correct":false},{"text":"...","correct":false}],"feedback":"..."},'
            . '{"type":"truefalse","text":"...","answer":true,"feedback":"..."}]}';

        return implode("\n", $lines);
    }

    /**
     * Decode the JSON returned by the model.
     *
     * @param string $raw Raw model output.
     * @return array[] Raw question items.
     * @throws \moodle_exception If it is not valid JSON.
     */
    protected function parse(string $raw): array {
        $raw = trim($raw);
        $raw = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $raw);
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            $start = strpos($raw, '{');
            $end = strrpos($raw, '}');
            if ($start !== false && $end !== false && $end > $start) {
                $data = json_decode(substr($raw, $start, $end - $start + 1), true);
            }
        }
        if (!is_array($data)) {
            throw new \moodle_exception('error_badjson', 'mod_aiqbank');
        }
        $items = $data['questions'] ?? (array_is_list($data) ? $data : []);
        return is_array($items) ? $items : [];
    }

    /**
     * Normalise and append valid, non-duplicated items.
     *
     * @param array $items Raw items.
     * @param array $questions Accumulated questions (by reference).
     * @param array $seen Lower-cased texts already used (by reference).
     * @param int $max Maximum number of items to add.
     */
    protected function add_unique(array $items, array &$questions, array &$seen, int $max): void {
        foreach ($items as $item) {
            if ($max <= 0) {
                break;
            }
            if (!is_array($item)) {
                continue;
            }
            $q = $this->normalise($item);
            if ($q === null) {
                continue;
            }
            $key = \core_text::strtolower($q['text']);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $questions[] = $q;
            $max--;
        }
    }

    /**
     * Validate one question item and convert it to the internal structure.
     *
     * @param array $item Raw item.
     * @return array|null Normalised question or null if invalid.
     */
    protected function normalise(array $item): ?array {
        $text = $this->clean((string) ($item['text'] ?? $item['question'] ?? ''));
        if ($text === '') {
            return null;
        }
        $feedback = $this->clean((string) ($item['feedback'] ?? $item['general_feedback'] ?? ''));
        $type = strtolower(trim((string) ($item['type'] ?? 'multichoice')));

        if (in_array($type, ['truefalse', 'true_false', 'true-false', 'tf', 'boolean'], true)) {
            $answer = $item['answer'] ?? null;
            if (is_string($answer)) {
                $answer = in_array(strtolower(trim($answer)), ['true', 'verdadero', 'v', 'si', 'sí', '1', 'yes'], true);
            } else if ($answer === null) {
                return null;
            }
            return ['type' => 'truefalse', 'text' => $text, 'answer' => (bool) $answer, 'feedback' => $feedback];
        }

        $options = [];
        $correct = 0;
        foreach ((array) ($item['options'] ?? []) as $opt) {
            if (is_string($opt)) {
                continue;
            }
            $otext = $this->clean((string) ($opt['text'] ?? ''));
            $otext = preg_replace('/^\s*[A-Ea-e1-5][\)\.\:]\s+/u', '', $otext);
            if ($otext === '') {
                continue;
            }
            $iscorrect = !empty($opt['correct']);
            $correct += $iscorrect ? 1 : 0;
            $options[] = ['text' => $otext, 'correct' => $iscorrect];
        }
        if (count($options) < 3 || $correct !== 1) {
            return null;
        }
        return ['type' => 'multichoice', 'text' => $text, 'options' => $options, 'feedback' => $feedback];
    }

    /**
     * Strip tags and extra whitespace.
     *
     * @param string $text
     * @return string
     */
    protected function clean(string $text): string {
        return trim(preg_replace('/[ \t]+/', ' ', strip_tags($text)));
    }
}
