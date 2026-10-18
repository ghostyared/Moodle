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

/**
 * Question generator page (what the teacher sees when opening the activity).
 *
 * @package    mod_aiqbank
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/questionlib.php');

use mod_aiqbank\form\generate_form;
use mod_aiqbank\local\ai_client;
use mod_aiqbank\local\category_helper;
use mod_aiqbank\local\question_generator;
use mod_aiqbank\local\question_saver;

$id = required_param('id', PARAM_INT);
[$course, $cm] = get_course_and_cm_from_cmid($id, 'aiqbank');
$instance = $DB->get_record('aiqbank', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
$coursecontext = context_course::instance($course->id);
require_capability('mod/aiqbank:generate', $context);
require_capability('moodle/question:add', $coursecontext);

$PAGE->set_url('/mod/aiqbank/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($instance->name));
$PAGE->set_heading(format_string($course->fullname));

$event = \mod_aiqbank\event\course_module_viewed::create([
    'objectid' => $instance->id,
    'context' => $context,
]);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('aiqbank', $instance);
$event->trigger();

$client = ai_client::from_config();

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($instance->name));
if (trim(strip_tags((string) $instance->intro)) !== '') {
    echo $OUTPUT->box(format_module_intro('aiqbank', $instance, $cm->id), 'generalbox mod_introbox');
}

if (!$client->is_configured()) {
    if (has_capability('moodle/site:config', context_system::instance())) {
        $url = new moodle_url('/admin/settings.php', ['section' => 'modsettingaiqbank']);
        echo $OUTPUT->notification(get_string('notconfigured_admin', 'mod_aiqbank', $url->out()), 'warning');
    } else {
        echo $OUTPUT->notification(get_string('notconfigured_teacher', 'mod_aiqbank'), 'warning');
    }
    echo $OUTPUT->footer();
    exit;
}

$cancreatecategory = has_capability('moodle/question:managecategory', $coursecontext);
$categories = category_helper::get_options($coursecontext);

$mform = new generate_form($PAGE->url->out(false), [
    'cmid' => $cm->id,
    'categories' => $categories,
    'cancreatecategory' => $cancreatecategory,
]);

if ($data = $mform->get_data()) {
    try {
        core_php_time_limit::raise(900);

        $topics = generate_form::split_topics($data->topics);
        $stems = generate_form::split_stems($data->stems);
        $age = generate_form::resolve_age($data->level, (int) $data->age);
        $language = generate_form::LANGUAGES[$data->language] ?? 'Spanish';
        $total = max((int) $data->count, count($stems));

        $generator = new question_generator($client, $client->get_batchsize());
        $questions = $generator->generate($topics, $stems, $total, $age, $data->qtype, $language);
        if (empty($questions)) {
            throw new moodle_exception('error_noquestions', 'mod_aiqbank');
        }

        // Resolve (or create) the destination category only after the AI answered.
        if ((int) $data->categoryid === 0) {
            require_capability('moodle/question:managecategory', $coursecontext);
            $category = category_helper::create($coursecontext, trim($data->newcategory));
        } else {
            $category = $DB->get_record('question_categories',
                ['id' => $data->categoryid, 'contextid' => $coursecontext->id], '*', MUST_EXIST);
        }

        $result = question_saver::save($questions, $category, !empty($data->draft));

        // Results.
        if ($result['created'] > 0) {
            echo $OUTPUT->notification(get_string('created', 'mod_aiqbank', (object) [
                'count' => $result['created'],
                'category' => format_string($category->name, true, ['context' => $coursecontext]),
            ]), 'success');
            if (!empty($data->draft)) {
                echo $OUTPUT->notification(get_string('saveddraft', 'mod_aiqbank'), 'info');
            }
        }
        if ($result['failed'] > 0) {
            echo $OUTPUT->notification(get_string('failed', 'mod_aiqbank', $result['failed']), 'warning');
        }
        if ($result['created'] < $total) {
            echo $OUTPUT->notification(get_string('fewer', 'mod_aiqbank', (object) [
                'created' => $result['created'],
                'requested' => $total,
            ]), 'warning');
        }
        foreach ($generator->get_warnings() as $warning) {
            echo $OUTPUT->notification($warning, 'warning');
        }

        // Preview table.
        $table = new html_table();
        $table->head = [
            '#',
            get_string('col_question', 'mod_aiqbank'),
            get_string('col_type', 'mod_aiqbank'),
            get_string('col_answer', 'mod_aiqbank'),
        ];
        $table->attributes['class'] = 'table table-sm generaltable';
        foreach ($questions as $i => $q) {
            if ($q['type'] === 'truefalse') {
                $typelabel = get_string('qtype_truefalse', 'mod_aiqbank');
                $answer = $q['answer'] ? get_string('true', 'qtype_truefalse') : get_string('false', 'qtype_truefalse');
            } else {
                $typelabel = get_string('qtype_multichoice', 'mod_aiqbank');
                $answer = '';
                foreach ($q['options'] as $option) {
                    if ($option['correct']) {
                        $answer = $option['text'];
                    }
                }
            }
            $table->data[] = [$i + 1, s($q['text']), $typelabel, s($answer)];
        }
        echo $OUTPUT->heading(get_string('preview', 'mod_aiqbank'), 3);
        echo html_writer::table($table);

        $bankurl = new moodle_url('/question/edit.php', [
            'courseid' => $course->id,
            'cat' => $category->id . ',' . $category->contextid,
        ]);
        echo html_writer::div(
            $OUTPUT->single_button($bankurl, get_string('gotobank', 'mod_aiqbank'), 'get', ['type' => 'primary']) .
            $OUTPUT->single_button($PAGE->url, get_string('generatemore', 'mod_aiqbank'), 'get'),
            'd-flex gap-2 mb-3'
        );
        echo $OUTPUT->footer();
        exit;
    } catch (moodle_exception $e) {
        echo $OUTPUT->notification($e->getMessage(), 'error');
    }
}

$mform->display();
echo $OUTPUT->footer();
