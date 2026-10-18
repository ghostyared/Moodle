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
 * Admin page that tests the configured AI provider.
 *
 * @package    mod_aiqbank
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();
$systemcontext = context_system::instance();
require_capability('moodle/site:config', $systemcontext);
require_sesskey();

$PAGE->set_context($systemcontext);
$PAGE->set_url('/mod/aiqbank/testconnection.php');
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('testconnection', 'mod_aiqbank'));
$PAGE->set_heading(get_string('testconnection', 'mod_aiqbank'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('testconnection', 'mod_aiqbank'));

$client = \mod_aiqbank\local\ai_client::from_config();
if (!$client->is_configured()) {
    echo $OUTPUT->notification(get_string('notconfigured_teacher', 'mod_aiqbank'), 'warning');
} else {
    try {
        $reply = $client->complete(
            'You are a connectivity test. Reply only with a JSON object.',
            'Reply with this JSON object: {"status":"ok"}'
        );
        echo $OUTPUT->notification(get_string('testing_ok', 'mod_aiqbank', (object) [
            'provider' => $client->get_provider(),
            'model' => $client->get_model(),
        ]), 'success');
        echo html_writer::tag('pre', s(core_text::substr(trim($reply), 0, 500)));
    } catch (moodle_exception $e) {
        echo $OUTPUT->notification($e->getMessage(), 'error');
    }
}

$back = new moodle_url('/admin/settings.php', ['section' => 'modsettingaiqbank']);
echo $OUTPUT->single_button($back, get_string('back'), 'get');
echo $OUTPUT->footer();
