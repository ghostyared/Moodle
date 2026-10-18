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

namespace mod_aiqbank\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Question generation form shown to teachers.
 *
 * @package    mod_aiqbank
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class generate_form extends \moodleform {

    /** Maximum number of teacher questions accepted per run. */
    public const MAX_STEMS = 50;

    /** @var array Age used for each preset level. */
    public const LEVEL_AGES = ['initial' => 3, 'primary' => 12, 'secondary' => 15, 'university' => 20];

    /** @var array Languages offered for the questions: key => name sent to the AI. */
    public const LANGUAGES = [
        'es' => 'Spanish',
        'en' => 'English',
        'pt' => 'Portuguese',
        'fr' => 'French',
    ];

    /**
     * Form definition.
     */
    protected function definition() {
        $mform = $this->_form;
        $cmid = $this->_customdata['cmid'];
        $categories = $this->_customdata['categories'];
        $cancreatecategory = $this->_customdata['cancreatecategory'];

        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);

        // Content.
        $mform->addElement('header', 'contentheader', get_string('contentheader', 'mod_aiqbank'));

        $mform->addElement('textarea', 'topics', get_string('topics', 'mod_aiqbank'), ['rows' => 4, 'cols' => 60]);
        $mform->setType('topics', PARAM_TEXT);
        $mform->addHelpButton('topics', 'topics', 'mod_aiqbank');

        $mform->addElement('textarea', 'stems', get_string('stems', 'mod_aiqbank'), ['rows' => 6, 'cols' => 60]);
        $mform->setType('stems', PARAM_TEXT);
        $mform->addHelpButton('stems', 'stems', 'mod_aiqbank');

        // Audience.
        $mform->addElement('header', 'audienceheader', get_string('audienceheader', 'mod_aiqbank'));

        $levels = [
            'initial' => get_string('level_initial', 'mod_aiqbank'),
            'primary' => get_string('level_primary', 'mod_aiqbank'),
            'secondary' => get_string('level_secondary', 'mod_aiqbank'),
            'university' => get_string('level_university', 'mod_aiqbank'),
            'custom' => get_string('level_custom', 'mod_aiqbank'),
        ];
        $mform->addElement('select', 'level', get_string('level', 'mod_aiqbank'), $levels);
        $mform->setDefault('level', 'primary');

        $mform->addElement('text', 'age', get_string('age', 'mod_aiqbank'), ['size' => 4]);
        $mform->setType('age', PARAM_INT);
        $mform->setDefault('age', 12);
        $mform->hideIf('age', 'level', 'neq', 'custom');

        $counts = [5 => 5, 10 => 10, 20 => 20, 30 => 30, 40 => 40, 50 => 50];
        $mform->addElement('select', 'count', get_string('count', 'mod_aiqbank'), $counts);
        $mform->setDefault('count', 10);
        $mform->addHelpButton('count', 'count', 'mod_aiqbank');

        $types = [
            'multichoice' => get_string('qtype_multichoice', 'mod_aiqbank'),
            'truefalse' => get_string('qtype_truefalse', 'mod_aiqbank'),
            'mixed' => get_string('qtype_mixed', 'mod_aiqbank'),
        ];
        $mform->addElement('select', 'qtype', get_string('qtype', 'mod_aiqbank'), $types);
        $mform->setDefault('qtype', 'multichoice');

        $languages = [];
        foreach (self::LANGUAGES as $code => $name) {
            $languages[$code] = get_string('lang_' . $code, 'mod_aiqbank');
        }
        $mform->addElement('select', 'language', get_string('language', 'mod_aiqbank'), $languages);
        $current = substr(current_language(), 0, 2);
        $mform->setDefault('language', isset($languages[$current]) ? $current : 'es');

        // Destination.
        $mform->addElement('header', 'destinationheader', get_string('destinationheader', 'mod_aiqbank'));

        $destinations = $categories;
        if ($cancreatecategory) {
            $destinations = [0 => get_string('category_new', 'mod_aiqbank')] + $categories;
        }
        $mform->addElement('select', 'categoryid', get_string('category', 'mod_aiqbank'), $destinations);
        $mform->addHelpButton('categoryid', 'category', 'mod_aiqbank');
        if ($cancreatecategory) {
            $mform->addElement('text', 'newcategory', get_string('newcategory', 'mod_aiqbank'), ['size' => 40]);
            $mform->setType('newcategory', PARAM_TEXT);
            $mform->hideIf('newcategory', 'categoryid', 'neq', 0);
            if (!$categories) {
                $mform->setDefault('categoryid', 0);
            }
        }

        $mform->addElement('advcheckbox', 'draft', get_string('draft', 'mod_aiqbank'),
            get_string('draft_desc', 'mod_aiqbank'));

        $this->add_action_buttons(false, get_string('generate', 'mod_aiqbank'));
    }

    /**
     * Validation.
     *
     * @param array $data Submitted data.
     * @param array $files Uploaded files.
     * @return array Errors.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $topics = self::split_topics($data['topics'] ?? '');
        $stems = self::split_stems($data['stems'] ?? '');
        $count = (int) ($data['count'] ?? 10);

        if (!$topics && !$stems) {
            $errors['topics'] = get_string('err_nocontent', 'mod_aiqbank');
        } else if (!$topics && $count > count($stems)) {
            $errors['topics'] = get_string('err_topicsneeded', 'mod_aiqbank');
        }
        if (count($stems) > self::MAX_STEMS) {
            $errors['stems'] = get_string('err_toomanystems', 'mod_aiqbank', self::MAX_STEMS);
        }
        if (($data['level'] ?? '') === 'custom') {
            $age = (int) ($data['age'] ?? 0);
            if ($age < 2 || $age > 99) {
                $errors['age'] = get_string('err_age', 'mod_aiqbank');
            }
        }
        if (isset($data['categoryid']) && (int) $data['categoryid'] === 0 && trim($data['newcategory'] ?? '') === '') {
            $errors['newcategory'] = get_string('err_newcategory', 'mod_aiqbank');
        }
        return $errors;
    }

    /**
     * Split the topics text (one per line or comma separated).
     *
     * @param string $text
     * @return string[]
     */
    public static function split_topics(string $text): array {
        $parts = preg_split('/[\r\n,;]+/', $text);
        $parts = array_map('trim', $parts);
        return array_values(array_unique(array_filter($parts, 'strlen')));
    }

    /**
     * Split the teacher questions (one per line or separated by ";"), removing list numbering.
     *
     * @param string $text
     * @return string[]
     */
    public static function split_stems(string $text): array {
        $lines = preg_split('/[\r\n;]+/', $text);
        $stems = [];
        foreach ($lines as $line) {
            $line = trim(preg_replace('/^\s*(?:\d+\s*[\.\)\-]|[-*•])\s*/u', '', $line));
            if ($line !== '') {
                $stems[] = $line;
            }
        }
        return $stems;
    }

    /**
     * Age for the selected level.
     *
     * @param string $level initial|primary|secondary|university|custom
     * @param int $customage Age typed by the teacher.
     * @return int
     */
    public static function resolve_age(string $level, int $customage): int {
        if (isset(self::LEVEL_AGES[$level])) {
            // The age of each level is configurable by the administrator.
            $age = (int) get_config('mod_aiqbank', 'age_' . $level);
            return $age >= 2 ? min(99, $age) : self::LEVEL_AGES[$level];
        }
        return max(2, min(99, $customage));
    }
}
