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
 * Token Authentication settings page
 *
 * @package    auth_token
 * @copyright  2016 Nicholas Hoobin (nicholashoobin@catalyst-au.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/auth/token/auth.php');

    $options = array(get_string('no'), get_string('yes'));

    $settings->add(new admin_setting_configselect('auth_token/tokenvisible',
            get_string('auth_tokensettings_visible', 'auth_token'),
            get_string('auth_tokensettings_visible_description', 'auth_token'), 1, $options));

    $settings->add(new admin_setting_configselect('auth_token/tokenrequired',
            get_string('auth_tokensettings_required', 'auth_token'),
            get_string('auth_tokensettings_required_description', 'auth_token'), 1, $options));
}
