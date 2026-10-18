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

global $CFG;
require_once($CFG->libdir . '/questionlib.php');

use core_question\local\bank\question_version_status;

/**
 * Saves generated questions into a Moodle question category.
 *
 * @package    mod_aiqbank
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_saver {

    /**
     * Save questions in the given category.
     *
     * @param array[] $questions Normalised questions from question_generator.
     * @param \stdClass $category Row of question_categories.
     * @param bool $draft Save as draft (needs review) instead of ready.
     * @return array ['created' => int, 'failed' => int, 'ids' => int[]]
     */
    public static function save(array $questions, \stdClass $category, bool $draft = false): array {
        global $USER;

        $created = 0;
        $failed = 0;
        $ids = [];
        $status = $draft ? question_version_status::QUESTION_STATUS_DRAFT : question_version_status::QUESTION_STATUS_READY;

        foreach ($questions as $q) {
            try {
                $form = self::build_form($q, $category, $status);
                $question = new \stdClass();
                $question->category = $category->id;
                $question->qtype = $q['type'];
                $question->createdby = $USER->id;
                $question->modifiedby = $USER->id;

                $saved = \question_bank::get_qtype($q['type'])->save_question($question, $form);
                $ids[] = $saved->id;
                $created++;
            } catch (\Throwable $e) {
                debugging('mod_aiqbank: could not save a question: ' . $e->getMessage(), DEBUG_DEVELOPER);
                $failed++;
            }
        }

        return ['created' => $created, 'failed' => $failed, 'ids' => $ids];
    }

    /**
     * Build the form-like object that question_type::save_question() expects.
     *
     * @param array $q Normalised question.
     * @param \stdClass $category Category row.
     * @param string $status Question status.
     * @return \stdClass
     */
    protected static function build_form(array $q, \stdClass $category, string $status): \stdClass {
        $form = new \stdClass();
        $form->category = $category->id . ',' . $category->contextid;
        $form->name = \core_text::substr($q['text'], 0, 80);
        $form->questiontext = self::field(self::html($q['text']));
        $form->generalfeedback = self::field($q['feedback'] !== '' ? self::html($q['feedback']) : '');
        $form->defaultmark = 1;
        $form->status = $status;
        $form->idnumber = null;
        $form->qtype = $q['type'];

        if ($q['type'] === 'truefalse') {
            $form->penalty = 1;
            $form->correctanswer = $q['answer'] ? 1 : 0;
            $form->feedbacktrue = self::field('');
            $form->feedbackfalse = self::field('');
            return $form;
        }

        $form->penalty = 0.3333333;
        $form->single = 1;
        $form->shuffleanswers = 1;
        $form->answernumbering = 'abc';
        $form->showstandardinstruction = 0;
        $form->shownumcorrect = 1;
        $form->correctfeedback = self::field('');
        $form->partiallycorrectfeedback = self::field('');
        $form->incorrectfeedback = self::field('');
        $form->answer = [];
        $form->fraction = [];
        $form->feedback = [];
        foreach ($q['options'] as $option) {
            $form->answer[] = self::field(self::html($option['text']));
            $form->fraction[] = $option['correct'] ? 1.0 : 0.0;
            $form->feedback[] = self::field('');
        }
        return $form;
    }

    /**
     * Rich text field structure with no attached files.
     *
     * @param string $html
     * @return array
     */
    protected static function field(string $html): array {
        return ['text' => $html, 'format' => FORMAT_HTML, 'files' => []];
    }

    /**
     * Escape plain text and wrap it as HTML.
     *
     * @param string $text
     * @return string
     */
    protected static function html(string $text): string {
        return '<p>' . nl2br(s($text), false) . '</p>';
    }
}
