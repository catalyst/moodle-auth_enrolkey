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
 * Strings for component 'auth_enrolkey', language 'en'.
 *
 * @package   auth_enrolkey
 * @copyright  2016 Nicholas Hoobin (nicholashoobin@catalyst-au.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['description'] = 'This provides Enrolment key based self-registration';
$string['settings_heading'] = 'Security';
$string['settings_security_desc'] = 'In addition to enabling the plugin, to allow the self-registration by email, this plugin must also be selected in the page "Site administration > Plugins > Authentication > Manage authentication", section "Common Settings", under "self-registration".';
$string['settings_tokenkey'] = 'Enrolment Token key';
$string['settings_tokenkey_desc'] = 'During self-registration if an enrolment key, provided by the teacher, is entered in the enrolment key field then it will proceed to automatically enrol the new user into any course that it matches. The keys are enabled in (Course administration > Users > Enrolment methods > Add method > Self enrolment).';
$string['settings_visible_description'] = 'Adds a new form element to the sign-up page for self-registration users. This will be checked against available enrolment keys and enrol the user to the matching courses';
$string['settings_required_description'] = 'The enrolment key will be a required field for validation';
$string['settings_visible_title'] = 'Enable enrolment key element';
$string['settings_required_title'] = 'Require enrolment key for validation';
$string['settings_email_title'] = 'Require email confirmation';
$string['settings_email_description'] = 'Require users to confirm their account with an email before accessing enrolled courses.';
$string['signup_failure'] = 'Opps! Something went wrong, and you may not have been enrolled properly. Go to <a href="{$a->href}">Home</a>';
$string['signup_field_title'] = 'Enrolment key';
$string['signup_token_invalid'] = 'The enrolment key you have entered is invalid';
$string['signup_missing'] = 'Missing enrolment key';
$string['noemail'] = 'Tried to send you an email but failed!';
$string['signup_view'] = 'Course enrolment';
$string['signup_view_message_basic'] = 'You have been enrolled as a {$a->role} into the course \'<a href="{$a->href}">{$a->course}</a>\'';
$string['signup_view_message_basic_dates'] = 'You have enrolled into {$a->course} as a {$a->role}. <a href={$a->href}>Click here to view the course.</a><br />Course starts: {$a->startdate}<br />Course ends: {$a->enddate}';
$string['signup_view_message_basic_dates_startonly'] = 'You have enrolled into {$a->course} as a {$a->role}. <a href={$a->href}>Click here to view the course.</a><br />Course starts: {$a->startdate}';
$string['signup_view_message_basic_dates_endonly'] = 'You have enrolled into {$a->course} as a {$a->role}. <a href={$a->href}>Click here to view the course.</a><br />Course ends: {$a->enddate}';
$string['signup_auth_instructions'] = 'For full access to courses you\'ll need to take a minute to create a new account for yourself on this web site.';
$string['recaptcha'] = 'Adds a visual/audio confirmation form element to the sign-up page for self-registering users. This protects your site against spammers and contributes to a worthwhile cause. See <a href="http://www.google.com/recaptcha">http://www.google.com/recaptcha</a> for more details.';
$string['recaptcha_key'] = 'Enable reCAPTCHA element';
$string['pluginname'] = 'Enrolment key based self-registration';
$string['privacy:metadata'] = 'The auth enrolkey plugin does not store any personal data.';
$string['optionalfield'] = 'Additional fields for User';
$string['optionalfield_desc'] = 'Choose the additional fields (in addition to "Username" and "email" which are mandatory) which are shown to new user on the Sign up page. If you want, you can make any of them "required", i.e the User *must* fill something.';
$string['enablefirstname'] = '1 - "first name"';
$string['enablefirstnameDesc'] = 'User can define own\'s first name';
$string['requiredfirstname'] = 'required?';
$string['enablelastname'] = '2 - "last name"';
$string['enablelastnameDesc'] = 'User can define own\'s last name';
$string['requiredlastname'] = 'required?';
$string['enablecity'] = '3 - "city"';
$string['enablecityDesc'] = 'User can define own\'s city';
$string['requiredcity'] = 'required?';
$string['missingcity'] = 'Missing city/town';
$string['enablecountry'] = '4 - "country"';
$string['enablecountryDesc'] = 'User can define own\'s country';
$string['requiredcountry'] = 'required?';
$string['missingcountry'] = 'Missing country';
