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

    $settings->add(new admin_setting_configselect('auth_enrolkey/emailconfirmation',
            get_string('settings_email_title', 'auth_enrolkey'),
            get_string('settings_email_description', 'auth_enrolkey'), 0, $options));
}
