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
 * Enrolment key based self-registration settings page
 *
 * @package    auth_enrolkey
 * @copyright  2016 Nicholas Hoobin (nicholashoobin@catalyst-au.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/auth/enrolkey/auth.php');

    $options = array(get_string('no'), get_string('yes'));

    $settings->add(new admin_setting_heading('auth_enrolkey_heading', get_string('settings_heading', 'auth_enrolkey'),
            get_string('settings_content', 'auth_enrolkey')));

    $settings->add(new admin_setting_configselect('auth_enrolkey/tokenrequired',
            get_string('settings_required_title', 'auth_enrolkey'),
            get_string('settings_required_description', 'auth_enrolkey'), 1, $options));

    $settings->add(new admin_setting_configselect('auth_enrolkey/recaptcha',
            get_string('recaptcha_key', 'auth_enrolkey'),
            get_string('recaptcha', 'auth_enrolkey'), 0, $options));

    $options[] = get_string('settings_partial', 'auth_enrolkey');
    $settings->add(new admin_setting_configselect('auth_enrolkey/emailconfirmation',
            get_string('settings_email_title', 'auth_enrolkey'),
            get_string('settings_email_description', 'auth_enrolkey'), 0, $options));

    if (function_exists('totara_cohort_check_and_update_dynamic_cohort_members')) {
        $settings->add(new admin_setting_configcheckbox('auth_enrolkey/totaracohortsync',
            get_string('cohortsync', 'auth_enrolkey'),
            get_string('cohortsync_description', 'auth_enrolkey'), 0));
    }

    if (moodle_major_version() >= '3.3') {
            $authplugin = get_auth_plugin('enrolkey');
            display_auth_lock_options($settings, $authplugin->authtype, $authplugin->userfields,
                get_string('auth_fieldlocks_help', 'auth'), false, false, $authplugin->get_custom_user_profile_fields());
    }

    $authplugin = get_auth_plugin('enrolkey');
    display_auth_lock_options($settings, $authplugin->authtype, $authplugin->userfields,
            '', true, true, $authplugin->get_custom_user_profile_fields());
}
