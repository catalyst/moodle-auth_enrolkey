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
 * Enrolkey core hooks
 *
 * @package    auth_enrolkey
 * @copyright  2023 Matthew Hilton <matthewhilton@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

use auth_enrolkey\utility;
require_once($CFG->dirroot . '/login/lib.php');

/**
 * Post forgot password request hook
 *
 * This is to send password reset emails to suspended users based on if a config is enabled.
 *
 * @param object $data password forget request data
 */
function auth_enrolkey_post_forgot_password_requests($data) {
    // This config allows suspended users to unsuspend their accounts if they provide a valid enrolkey.
    // However, if they forget their password, they cannot login to do this.
    // So we hook in here and if the config is enabled and they ARE suspended, we send them a password email anyway.
    if (empty(get_config('auth_enrolkey', 'unsuspendaccounts'))) {
        return;
    }

    $user = utility::search_for_suspended_user((array) $data);

    if (empty($user) || $user->auth != "enrolkey") {
        return;
    }

    // Make the user appear unsuspended so the email is successfully sent.
    $user->suspended = 0;

    $resetrecord = core_login_generate_password_reset($user);
    send_password_change_confirmation_email($user, $resetrecord);
}

/**
 * Post password set hook
 *
 * This is used to logout users who reset their password while being suspended.
 * Otherwise they are logged in, but still suspended.
 * We log them out so they can use the unsuspend.php page in conjunction with their enrolkey.
 * @param object $data
 */
function auth_enrolkey_post_set_password_requests($data) {
    global $USER;

    if ($USER->auth != 'enrolkey' || empty($USER->suspended)) {
        return;
    }

    // Log them out immediately.
    // Required since resetting password logs you in,
    // but the user is still suspended, so they get stuck in a halfway state.
    require_logout();

    // Redirect them to the unsuspend page afterwards.
    // So they can enter their new details and enrolkey and unsuspend themselves.
    if (!PHPUNIT_TEST) {
        redirect(new moodle_url('/auth/enrolkey/unsuspend.php'));
    }
}

