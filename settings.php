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
 * Admin settings: AI provider configuration.
 *
 * @package    mod_aiqbank
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading('mod_aiqbank/providerheading',
        get_string('settingsheading', 'mod_aiqbank'),
        get_string('settingsheading_desc', 'mod_aiqbank')));

    $providers = [
        'gemini' => get_string('provider_gemini', 'mod_aiqbank'),
        'openai' => get_string('provider_openai', 'mod_aiqbank'),
        'grok' => get_string('provider_grok', 'mod_aiqbank'),
        'custom' => get_string('provider_custom', 'mod_aiqbank'),
    ];
    $settings->add(new admin_setting_configselect('mod_aiqbank/provider',
        get_string('provider', 'mod_aiqbank'),
        get_string('provider_desc', 'mod_aiqbank'),
        'gemini', $providers));

    $settings->add(new admin_setting_configpasswordunmask('mod_aiqbank/apikey',
        get_string('apikey', 'mod_aiqbank'),
        get_string('apikey_desc', 'mod_aiqbank'),
        ''));

    $settings->add(new admin_setting_configtext('mod_aiqbank/model',
        get_string('model', 'mod_aiqbank'),
        get_string('model_desc', 'mod_aiqbank'),
        '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('mod_aiqbank/baseurl',
        get_string('baseurl', 'mod_aiqbank'),
        get_string('baseurl_desc', 'mod_aiqbank'),
        '', PARAM_URL));

    $settings->add(new admin_setting_configtext('mod_aiqbank/temperature',
        get_string('temperature', 'mod_aiqbank'),
        get_string('temperature_desc', 'mod_aiqbank'),
        '0.7', PARAM_RAW_TRIMMED));

    $settings->add(new admin_setting_configtext('mod_aiqbank/timeout',
        get_string('timeout', 'mod_aiqbank'),
        get_string('timeout_desc', 'mod_aiqbank'),
        120, PARAM_INT));

    $settings->add(new admin_setting_configselect('mod_aiqbank/batchsize',
        get_string('batchsize', 'mod_aiqbank'),
        get_string('batchsize_desc', 'mod_aiqbank'),
        10, [5 => 5, 10 => 10, 15 => 15, 20 => 20]));

    $settings->add(new admin_setting_configcheckbox('mod_aiqbank/jsonmode',
        get_string('jsonmode', 'mod_aiqbank'),
        get_string('jsonmode_desc', 'mod_aiqbank'),
        1));

    $settings->add(new admin_setting_heading('mod_aiqbank/levelsheading',
        get_string('levelsheading', 'mod_aiqbank'),
        get_string('levelsheading_desc', 'mod_aiqbank')));
    foreach (['initial' => 3, 'primary' => 12, 'secondary' => 15, 'university' => 20] as $levelkey => $levelage) {
        $settings->add(new admin_setting_configtext('mod_aiqbank/age_' . $levelkey,
            get_string('levelage', 'mod_aiqbank', get_string('level_' . $levelkey, 'mod_aiqbank')),
            get_string('levelage_desc', 'mod_aiqbank'),
            $levelage, PARAM_INT));
    }

    $testurl = new moodle_url('/mod/aiqbank/testconnection.php', ['sesskey' => sesskey()]);
    $settings->add(new admin_setting_description('mod_aiqbank/testconnection',
        get_string('testconnection', 'mod_aiqbank'),
        get_string('testconnection_desc', 'mod_aiqbank') . '<br>' .
        html_writer::link($testurl, get_string('testconnection_run', 'mod_aiqbank'), ['class' => 'btn btn-secondary mt-2'])));
}
