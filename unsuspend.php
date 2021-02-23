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
 * Unsuspend user page with a valid enrolkey.
 *
 * @package    auth_enrolkey
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


use auth_enrolkey\form\unsuspend_form;
use auth_enrolkey\utility;

require_once(__DIR__.'/../../config.php');

$context = context_system::instance();

$baseurl = new moodle_url('/auth/enrolkey/unsuspend.php');

if (!get_config('auth_enrolkey', 'unsuspendaccounts') || isloggedin()) {
    redirect(new moodle_url('/'));;
}

$PAGE->set_url($baseurl);
$PAGE->set_pagelayout('login');
$PAGE->set_title(get_string('title_unsuspend', 'auth_enrolkey'));
$PAGE->set_heading(get_string('heading_unsuspend', 'auth_enrolkey'));
$output = $PAGE->get_renderer('auth_enrolkey');

$form = new unsuspend_form($baseurl);

if ($form->is_cancelled()) {
    require_logout();
    redirect(new moodle_url('/'));
} else if ($form->is_submitted() && $form->is_validated()) {
    $valid = false;
    $data = $form->get_data();

    // At this stage we can say, due to mform validation the user exists, is suspended and the password checks out.
    // Lets just double check these things anyway.

    $user = utility::search_for_suspended_user((array)$data);
    if ($user) {
        $valid = validate_internal_user_password($user, $data->password);
    }

    if ($valid) {
        // We must log the user in to the $USER object for enrolling them, or call the can_self_enrol() function.
        complete_user_login($user);

        $auth = get_auth_plugin('enrolkey');

        try {
            list($availableenrolids, $errors) = $auth->enrol_user($data->signup_token);

            // Only enrol a user to enrolkeys and courses which they are not already enrolled in.
            if ($availableenrolids) {
                utility::unsuspend_user($user);

                // They are now unsuspended. We can actually called the real auth login function.
                if (authenticate_user_login($user->username, $data->password)) {
                    \auth_enrolkey\persistent\enrolkey_profile_mapping::add_fields_during_signup($user, $availableenrolids);
                    \auth_enrolkey\persistent\enrolkey_cohort_mapping::add_cohorts_during_signup($user, $availableenrolids);
                    \auth_enrolkey\persistent\enrolkey_redirect_mapping::redirect_during_signup($availableenrolids);

                    // Default redirect.
                    redirect(new moodle_url("/auth/enrolkey/view.php", array('ids' => implode(',', $availableenrolids))));
                }
            }

        } catch (Exception $e) {
            error_log('auth_enrolkey expection:' . $e->getMessage());
        }

        // Well, something messed up. Lets not let the user in.
        require_logout();
    }
}

echo $output->header();
echo $output->heading($PAGE->heading);
$form->display();
echo $output->footer();