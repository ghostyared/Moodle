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
 * List of AI question bank activities in a course.
 *
 * @package    mod_aiqbank
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$course = get_course($id);
require_course_login($course);

$PAGE->set_url('/mod/aiqbank/index.php', ['id' => $id]);
$PAGE->set_pagelayout('incourse');
$PAGE->set_title(get_string('modulenameplural', 'mod_aiqbank'));
$PAGE->set_heading(format_string($course->fullname));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('modulenameplural', 'mod_aiqbank'));

$instances = get_all_instances_in_course('aiqbank', $course);
if (!$instances) {
    echo $OUTPUT->notification(get_string('noinstances', 'mod_aiqbank'), 'info');
} else {
    $table = new html_table();
    $table->head = [get_string('name')];
    foreach ($instances as $instance) {
        $table->data[] = [html_writer::link(
            new moodle_url('/mod/aiqbank/view.php', ['id' => $instance->coursemodule]),
            format_string($instance->name)
        )];
    }
    echo html_writer::table($table);
}

echo $OUTPUT->footer();
