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
 * Authentication Plugin: Token Authentication
 *
 * @package    auth_token
 * @copyright  2016 Nicholas Hoobin (nicholashoobin@catalyst-au.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/authlib.php');

/**
 * Email authentication plugin.
 * @copyright  2016 Nicholas Hoobin (nicholashoobin@catalyst-au.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_plugin_token extends auth_plugin_base {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->authtype = 'token';
        $this->config = get_config('auth/token');
    }

    /**
     * Returns true if the username and password work and false if they are
     * wrong or don't exist.
     *
     * @param string $username The username
     * @param string $password The password
     * @return bool Authentication success or failure.
     */
    public function user_login($username, $password) {
        global $CFG, $DB;
        if ($user = $DB->get_record('user', array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id))) {
            return validate_internal_user_password($user, $password);
        }

        return false;
    }

    /**
     * Adds this authentication method to the self registration list.
     *
     */
    public function can_signup() {
        return true;
    }

    /**
     * Sign up a new user ready for confirmation.
     * Password is passed in plaintext.
     *
     * @param object $user new user object
     * @param boolean $notify print notice with link and terminate
     */
    public function user_signup($user, $notify=true) {
        global $CFG, $DB, $SESSION, $USER;
        require_once($CFG->dirroot . '/user/profile/lib.php');
        require_once($CFG->dirroot . '/user/lib.php');
        require_once($CFG->dirroot . '/enrol/self/lib.php');

        $plainpassword = $user->password;
        $user->password = hash_internal_user_password($user->password);

        // These are currently not present in the user object.
        $user->currentlogin = time();
        $user->picture = 0;
        $user->imagealt = 0;
        $user->deleted = 0;

        $user->id = user_create_user($user, false, false);

        // Trigger event.
        \core\event\user_created::create_from_userid($user->id)->trigger();

        if (!PHPUNIT_TEST) {
            if (!send_confirmation_email($user)) {
                print_error('auth_emailnoemail', 'auth_email');
            }
        }

        // Password is the Enrolment key that is specified in the Self enrolment instance.
        $courses = $DB->get_records('enrol', array('password' => $user->signup_token));

        $enrol = enrol_get_plugin('self');
        foreach ($courses as $course) {
            $enrol->enrol_user($course, $user->id);
        }

        if (!PHPUNIT_TEST) {
            complete_user_login($user);
            $USER->loggedin = true;
            $USER->site = $CFG->wwwroot;
            set_moodle_cookie($USER->username);
        }

        // Will be passed to view.php to show which courses they have been enrolled it.
        $SESSION->auth_token = $user->signup_token;

        if (!PHPUNIT_TEST) {
            redirect(new moodle_url("/auth/token/view.php"));
        }
    }

    /**
     * Returns true if plugin allows confirming of new users.
     *
     * @return bool
     */
    public function can_confirm() {
        return true;
    }

    /**
     * Confirm the new user as registered.
     *
     * @param string $username
     * @param string $confirmsecret
     */
    public function user_confirm($username, $confirmsecret) {
        global $DB;
        $user = get_complete_user_data('username', $username);

        if (!empty($user)) {
            if ($user->auth != $this->authtype) {
                return AUTH_CONFIRM_ERROR;

            } else if ($user->secret == $confirmsecret && $user->confirmed) {
                return AUTH_CONFIRM_ALREADY;

            } else if ($user->secret == $confirmsecret) {   // They have provided the secret key to get in.
                $DB->set_field("user", "confirmed", 1, array("id" => $user->id));
                return AUTH_CONFIRM_OK;
            }
        } else {
            return AUTH_CONFIRM_ERROR;
        }
    }

    /**
     * Returns true if plugin can be manually set.
     *
     * @return bool
     */
    public function can_be_manually_set() {
        return true;
    }

    /**
     * Return a form to capture user details for account creation.
     * This is used in /login/signup.php.
     * @return moodle_form A form which edits a record from the user table.
     */
    public function signup_form() {
        global $CFG;

        require_once($CFG->dirroot . '/login/signup_form.php');
        require_once('token_signup_form.php');
        return new token_signup_form(null, null, 'post', '', array('autocomplete' => 'on'));
    }
}


