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
 * Authentication Plugin: Enrolment key based self-registration.
 *
 * @package    auth_enrolkey
 * @copyright  2016 Nicholas Hoobin (nicholashoobin@catalyst-au.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/authlib.php');

// If totara cohort lib exists, import it.
if (file_exists($CFG->dirroot . '/totara/cohort/lib.php')) {
    require_once($CFG->dirroot . '/totara/cohort/lib.php');
}

/**
 * Enrolment key based self-registration.
 * @copyright  2016 Nicholas Hoobin (nicholashoobin@catalyst-au.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_plugin_enrolkey extends auth_plugin_base {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->authtype = 'enrolkey';
        $this->config = get_config('auth_enrolkey');
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
        if ($user = $DB->get_record('user', ['username' => $username, 'mnethostid' => $CFG->mnet_localhost_id])) {
            return validate_internal_user_password($user, $password);
        }

        return false;
    }

    /**
     * Moodle hook 'pre_user_login_hook'.
     *
     * @param object $user
     * @throws coding_exception
     */
    public function pre_user_login_hook(&$user) {
        if (!get_config('auth_enrolkey', 'unsuspendaccounts')) {
            // The option to unsuspend is not enabled. Do not redirect.
            return;
        }

        // Password is passed via the login.php page.
        $password = optional_param('password', '', PARAM_RAW);
        $valid = validate_internal_user_password($user, $password);

        if ($user->auth != $this->authtype) {
            return;
        }

        if (!empty($user->suspended)) {
            $failurereason = AUTH_LOGIN_SUSPENDED;

            // Trigger login failed event.
            $event = \core\event\user_login_failed::create([
                'userid' => $user->id,
                'other' => ['username' => $user->username, 'reason' => $failurereason],
            ]);
            $event->trigger();

            // Oh no. This user is suspended, but the password is all good. Lets take them to a self un-suspend page.
            if ($valid) {
                redirect(new moodle_url('/auth/enrolkey/unsuspend.php'));
            }
        }
    }

    /**
     * Method for changing password in the system
     * @param stdClass $user
     * @param string $newpassword
     * @return bool
     */
    public function user_update_password($user, $newpassword) {
        $user = get_complete_user_data('id', $user->id);
        return update_internal_user_password($user, $newpassword);
    }

    /**
     * Adds this authentication method to the self registration list.
     *
     */
    public function can_signup() {
        return true;
    }

    /**
     * Returns true if this authentication plugin can change the user's
     * password.
     *
     * @return bool
     */
    public function can_change_password() {
        return true;
    }

    /**
     * Returns true if the user can reset their password.
     *
     * @return bool
     */
    public function can_reset_password() {
        return true;
    }

    /**
     * Sign up a new user ready for confirmation.
     * Password is passed in plaintext.
     * @param object $user
     * @param bool $notify
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function user_signup($user, $notify=true) {
        global $CFG, $DB, $SESSION, $USER, $PAGE, $OUTPUT;
        require_once($CFG->dirroot . '/user/profile/lib.php');
        require_once($CFG->dirroot . '/user/lib.php');
        require_once($CFG->dirroot . '/enrol/self/lib.php');

        $user->password = hash_internal_user_password($user->password);

        // These are currently not present in the user object.
        $user->currentlogin = time();
        $user->picture = 0;
        $user->imagealt = 0;
        $user->deleted = 0;
        $emailconfirmation = get_config('auth_enrolkey', 'emailconfirmation');
        // Default setting confirmation not required.
        $user->policyagreed = 1;
        $user->confirmed = 1;
        if ('1' === $emailconfirmation) {
            // No access until account confirmed via email.
            $user->policyagreed = 0;
            $user->confirmed = 0;
        } else if ('2' === $emailconfirmation) {
            // Access to course, but confirmation required before next login attempt.
            $user->confirmed = 0;
        }
        $user->lastip = getremoteaddr();
        $user->id = user_create_user($user, false, false);

        // Save any custom profile field information.
        profile_save_data($user);

        // Trigger event.
        \core\event\user_created::create_from_userid($user->id)->trigger();
        if ($notify) {
            if (!send_confirmation_email($user)) {
                // TODO make this more resilient? Email shouldn't be critical here.
                throw new \moodle_exception('noemail', 'auth_enrolkey');
            }
        }

        if (PHPUNIT_TEST) {
            $USER->username = $user->username;
            $USER->id = $user->id;
            $USER->email = $user->email;
        } else {
            complete_user_login($user);
        }
        $USER->loggedin = true;
        $USER->site = $CFG->wwwroot;
        set_moodle_cookie($USER->username);
        list($availableenrolids, $errors) = $this->enrol_user($user->signup_token, $notify);
        if (!$notify) {
            return;
        }

        // New Enrolkey hook, will force/add user profile fields user based on the enrolkey used.
        \auth_enrolkey\persistent\enrolkey_profile_mapping::add_fields_during_signup($user, $availableenrolids);

        // New Enrolkey hook, will assign and enrol this user to corhots based on the enrolkey used.
        \auth_enrolkey\persistent\enrolkey_cohort_mapping::add_cohorts_during_signup($user, $availableenrolids);

        // At this point signup and enrolment is finished.
        // If enabled, run a cohort sync to force dynamic cohorts to update.
        if (get_config('auth_enrolkey', 'totaracohortsync') &&
            function_exists('totara_cohort_check_and_update_dynamic_cohort_members')) {
            $trace = new \null_progress_trace();
            // This may be a perfomance hog.
            totara_cohort_check_and_update_dynamic_cohort_members(null, $trace);
        }

        if (!empty($availableenrolids) && $user->confirmed === 0 && $user->policyagreed === 0) {
            $this->email_confirmation($user->email);
        }

        // If there were errors detected, output on target page.
        foreach ($errors as $courseid => $errmsg) {
            $course = get_course($courseid);
            \core\notification::error(
                get_string('errorenrolling', 'auth_enrolkey', ['course' => $course->fullname, 'err' => $errmsg])
            );
        }

        // New Enrolkey hook, if configured will redirect the user based on the enrolkey used.
        \auth_enrolkey\persistent\enrolkey_redirect_mapping::redirect_during_signup($availableenrolids);

        // If no courses found (empty key) go to dashboard.
        if (empty($availableenrolids)) {
            redirect(new moodle_url('/my/'));
        } else {
            redirect(new moodle_url("/auth/enrolkey/view.php", ['ids' => implode(',', $availableenrolids)]));
        }
    }

    /**
     * Enrols a $USER with valid a $enrolkey
     * @param string $enrolkey
     * @param bool $notify
     * @return array
     */
    public function enrol_user(string $enrolkey, bool $notify = true) : array {
        global $DB;

        /** @var enrol_self_plugin $enrol */
        $enrol = enrol_get_plugin('self');
        $enrolplugins = $this->get_enrol_plugins($DB, $enrolkey);
        $availableenrolids = [];
        $errors = [];
        foreach ($enrolplugins as $enrolplugin) {
            if ($enrol->can_self_enrol($enrolplugin) === true) {
                $data = new stdClass();
                $data->enrolpassword = $enrolplugin->enrolmentkey ?? $enrolplugin->password;
                $enrol->enrol_self($enrolplugin, $data);
                $availableenrolids[] = $enrolplugin->id;
            } else {
                // Store error to output.
                $errors[$enrolplugin->courseid] = $enrol->can_self_enrol($enrolplugin);
            }
        }
        return [$availableenrolids, $errors];
    }

    /**
     * Prints helpful instructions in login/index.php
     */
    public function loginpage_hook() {
        global $CFG;

        if ($CFG->registerauth == $this->authtype && empty($CFG->auth_instructions)) {
            $url = '/login/signup.php';
            $CFG->auth_instructions = get_string('signup_auth_instructions', 'auth_enrolkey', $url);
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
            } else if ($user->secret === $confirmsecret && $user->confirmed) {
                return AUTH_CONFIRM_ALREADY;
            } else if ($user->secret === $confirmsecret) { // They have provided the secret key to get in.
                $DB->set_field('user', 'confirmed', 1, ['id' => $user->id]);
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
        return new \auth_enrolkey\form\enrolkey_signup_form(null, null, 'post', '', ['autocomplete' => 'on']);
    }

    /**
     * Returns user to a logout page with notice for email confirmation.
     *
     * @param string $email
     * @throws coding_exception
     */
    private function email_confirmation(string $email = '') {
        global $PAGE, $OUTPUT, $CFG, $USER;
        require_logout();
        $emailconfirm = get_string('emailconfirm');
        $PAGE->navbar->add($emailconfirm);
        $PAGE->set_title($emailconfirm);
        $PAGE->set_heading($PAGE->course->fullname);
        echo $OUTPUT->header();
        $email = $USER->email ?? $email;
        notice(get_string('emailconfirmsent', '', $email), "$CFG->wwwroot/index.php");
    }

    /**
     * Obtains a list of enrolment plugins which use the enrolkey.
     *
     * @param moodle_database $db
     * @param string $enrolkey
     * @return array
     */
    private function get_enrol_plugins(moodle_database $db, string $enrolkey) : array {
        // Password is the Enrolment key that is specified in the Self enrolment instance.
        $enrolplugins = $db->get_records('enrol', ['enrol' => 'self', 'password' => $enrolkey]);

        return array_merge($enrolplugins, $db->get_records_sql("
                SELECT e.*, g.enrolmentkey
                  FROM {groups} g
                  JOIN {enrol} e ON e.courseid = g.courseid
                                AND e.enrol = 'self'
                                AND e.customint1 = 1
                 WHERE g.enrolmentkey = ?
            ", [$enrolkey]));
    }
}
