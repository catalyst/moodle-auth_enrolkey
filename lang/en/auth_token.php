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
 * Strings for component 'auth_token', language 'en'.
 *
 * @package   auth_token
 * @copyright  2016 Nicholas Hoobin (nicholashoobin@catalyst-au.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['auth_tokensettings_visible_description'] = 'Adds a token form element to the sign-up page for self-registration users. This will be checked against available tokens and enrol the user to a specific course';
$string['auth_tokensettings_required_description'] = 'The token element will be a required field for validation';
$string['auth_tokensettings_visible'] = 'Enable Token element';
$string['auth_tokensettings_required'] = 'Require Token for validation';
$string['auth_tokendescription'] = 'This provides Token based authentication';
$string['auth_tokensignup_field'] = 'Course token';
$string['auth_tokensignup_token_invalid'] = 'The token you have entered is invalid';
$string['auth_tokensignup_missing'] = 'Missing token';
$string['auth_tokensignup_view'] = 'Course enrolment';
$string['auth_tokensignup_view_message_basic'] = 'You have enrolled into {$a->course} as a {$a->role}.';
$string['auth_tokensignup_view_message_basic_dates'] = 'You have enrolled into {$a->course} as a {$a->role}.<br />Course starts: {$a->startdate}<br />Course ends: {$a->enddate}';
$string['auth_tokensignup_view_message_basic_dates_startonly'] = 'You have enrolled into {$a->course} as a {$a->role}.<br />Course starts: {$a->startdate}';
$string['auth_tokensignup_view_message_basic_dates_endonly'] = 'You have enrolled into {$a->course} as a {$a->role}.<br />Course ends: {$a->enddate}';
$string['auth_tokensignup_view_message_instancename'] = 'You have enrolled into {$a->course}, {$a->enrolinstance} as a {$a->role}.';
$string['pluginname'] = 'Token authentication';
