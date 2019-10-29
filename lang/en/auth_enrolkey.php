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
$string['settings_heading'] = 'General settings';
$string['settings_content'] = '<p>Enrolment key based self-registration enables a user to create their own account via a \'Create new account\' button on the login page. The user then receives an email containing a secure link to a page where they can confirm their account. Future logins just check the username and password against the stored values in the Moodle database.</p><p>During self-registration if an enrolment key is entered in the enrolment key field then it will proceed to automatically enrol the new user into any course that it matches. The keys are enabled in (Course administration > Users > Enrolment methods > Add method > Self enrolment).</p><p>Note: In addition to enabling the plugin, Enrolment key based self-registration must also be selected from the self registration drop-down menu on the \'Manage authentication\' page.</p>';
$string['settings_visible_description'] = 'Adds a new form element to the sign-up page for self-registration users. This will be checked against available enrolment keys and enrol the user to the matching courses';
$string['settings_required_description'] = 'The enrolment key will be a required field for validation';
$string['settings_visible_title'] = 'Enable enrolment key element';
$string['settings_required_title'] = 'Require enrolment key for validation';
$string['settings_email_title'] = 'Require email confirmation';
$string['settings_email_description'] = 'Require users confirm their account with an email before accessing enrolled courses.';
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
$string['signup_auth_instructions'] = 'Hi! For full access to courses you\'ll need to take
a minute to create a new account for yourself on this web site.
Each of the individual courses may also have a one-time
"enrolment key", which you can use during this sign up:
<ol>
<li>Fill out the <a href="{$a}">New Account</a> form with your details.</li>
<li>You will be prompted for an "enrolment key" - use the one
that your teacher has given you. This will "enrol" you in the
course.</li>
<li>Your account will be created and you will be logged in.</li>
<li>You can now access the full course for this session.</li>
<li>An email has also been immediately sent to your email address.</li>
<li>Read your email, and click on the web link it contains.</li>
<li>From now on you will only need to enter your personal
username and password (in the form on this page) to log in
and access any course you have enrolled in.</li>
</ol>';
$string['recaptcha'] = 'Adds a visual/audio confirmation form element to the sign-up page for self-registering users. This protects your site against spammers and contributes to a worthwhile cause. See <a href="http://www.google.com/recaptcha">http://www.google.com/recaptcha</a> for more details.';
$string['recaptcha_key'] = 'Enable reCAPTCHA element';
$string['pluginname'] = 'Enrolment key based self-registration';
$string['privacy:metadata'] = 'The auth enrolkey plugin does not store any personal data.';
