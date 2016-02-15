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

$string['auth_tokendescription'] = 'This provides Token based authentication';
$string['auth_tokensettings_heading'] = 'General settings';
$string['auth_tokensettings_content'] = '<p>Token-based self-registration enables a user to create their own account via a \'Create new account\' button on the login page. The user then receives an email containing a secure link to a page where they can confirm their account. Future logins just check the username and password against the stored values in the Moodle database.</p><p>During self-registration if a token is entered in the Course Token field then it will proceed to automatically enrol the new user into any course key that it matches. The keys are enabled in (Course administration > Users > Enrolment methods > Add method > Self enrolment) and will use the Enrolment Key field.</p><p>Note: In addition to enabling the plugin, token-based self-registration must also be selected from the self registration drop-down menu on the \'Manage authentication\' page.</p>';
$string['auth_tokensettings_visible_description'] = 'Adds a token form element to the sign-up page for self-registration users. This will be checked against available tokens and enrol the user to a specific course';
$string['auth_tokensettings_required_description'] = 'The token element will be a required field for validation';
$string['auth_tokensettings_visible'] = 'Enable Token element';
$string['auth_tokensettings_required'] = 'Require Token for validation';
$string['auth_tokensignup_field'] = 'Course token';
$string['auth_tokensignup_token_invalid'] = 'The token you have entered is invalid';
$string['auth_tokensignup_missing'] = 'Missing token';
$string['auth_tokensignup_view'] = 'Course enrolment';
$string['auth_tokensignup_view_message_basic'] = 'You have enrolled into {$a->course} as a {$a->role}.';
$string['auth_tokensignup_view_message_basic_dates'] = 'You have enrolled into {$a->course} as a {$a->role}.<br />Course starts: {$a->startdate}<br />Course ends: {$a->enddate}';
$string['auth_tokensignup_view_message_basic_dates_startonly'] = 'You have enrolled into {$a->course} as a {$a->role}.<br />Course starts: {$a->startdate}';
$string['auth_tokensignup_view_message_basic_dates_endonly'] = 'You have enrolled into {$a->course} as a {$a->role}.<br />Course ends: {$a->enddate}';
$string['auth_tokensignup_auth_instructions'] = 'Hi! For full access to courses you\'ll need to take
a minute to create a new account for yourself on this web site.
Each of the individual courses may also have a one-time
"enrolment key", which you can use during this sign up:
<ol>
<li>Fill out the <a href="{$a}">New Account</a> form with your details.</li>
<li>If you are prompted for an "enrolment key" - use the one
that your teacher has given you. This will "enrol" you in the
course.</li>
<li>An email will be immediately sent to your email address.</li>
<li>Read your email, and click on the web link it contains.</li>
<li>Your account will be confirmed and you will be logged in.</li>
<li>Now, select the course you want to participate in.</li>
<li>You can now access the full course. From now on you will only need
to enter your personal username and password (in the form on this page)
to log in and access any course you have enrolled in.</li>
</ol>';
$string['pluginname'] = 'Token authentication';

