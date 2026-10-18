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
 * Library of interface functions for mod_aiqbank.
 *
 * @package    mod_aiqbank
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Features supported by the module.
 *
 * @param string $feature FEATURE_xx constant.
 * @return mixed True if supported, null if unknown.
 */
function aiqbank_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_ADMINISTRATION;
        default:
            return null;
    }
}

/**
 * Create a new instance.
 *
 * @param stdClass $data Form data.
 * @param mod_aiqbank_mod_form|null $mform The form.
 * @return int New instance id.
 */
function aiqbank_add_instance($data, $mform = null) {
    global $DB;

    $data->timecreated = time();
    $data->timemodified = $data->timecreated;
    $data->id = $DB->insert_record('aiqbank', $data);

    return $data->id;
}

/**
 * Update an instance.
 *
 * @param stdClass $data Form data.
 * @param mod_aiqbank_mod_form|null $mform The form.
 * @return bool
 */
function aiqbank_update_instance($data, $mform = null) {
    global $DB;

    $data->timemodified = time();
    $data->id = $data->instance;

    return $DB->update_record('aiqbank', $data);
}

/**
 * Delete an instance.
 *
 * @param int $id Instance id.
 * @return bool
 */
function aiqbank_delete_instance($id) {
    global $DB;

    if (!$DB->record_exists('aiqbank', ['id' => $id])) {
        return false;
    }
    $DB->delete_records('aiqbank', ['id' => $id]);

    return true;
}

/**
 * Hide the activity from users who cannot generate questions (students).
 *
 * @param cm_info $cm Course module info.
 */
function aiqbank_cm_info_dynamic(cm_info $cm) {
    if (!has_capability('mod/aiqbank:generate', $cm->context)) {
        $cm->set_user_visible(false);
    }
}
