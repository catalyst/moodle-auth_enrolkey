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
 * Form which will unsuspend users with valid enrolkeys.
 *
 * @package    auth_enrolkey
 * @copyright  2021 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Form which will unsuspend users with valid enrolkeys.
 *
 * This provides an additional enrolment key field that will be validated upon signup.
 *
 * @copyright  2021 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace auth_enrolkey\form;

use auth_enrolkey\utility;
use core_user;

defined('MOODLE_INTERNAL') || die;

/**
 * Class for the unsuspend form.
 *
 * @package    auth_enrolkey
 * @copyright  2021 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class unsuspend_form extends \moodleform {

    /**
     * Creates the Moodle singup form, calls parent::definition();
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('text', 'signup_token', get_string('signup_field_title', 'auth_enrolkey'));

        $mform->setType('signup_token', PARAM_TEXT);
        $token = optional_param('signup_token', '', PARAM_TEXT);
        if (!empty($token)) {
            $mform->setDefault('signup_token', $token);
        }

        $mform->addRule('signup_token', get_string('signup_missing', 'auth_enrolkey'), 'required', null, 'client');

        if (empty($CFG->createuserwithemail)) {
            $mform->addElement('text', 'username', get_string('username'), 'maxlength="100" size="12" autocapitalize="none"');
            $mform->setType('username', PARAM_RAW);
            $mform->addRule('username', get_string('missingusername'), 'required', null, 'client');
        } else {
            $mform->addElement('text', 'email', get_string('email'), 'maxlength="100" size="25"');
            $mform->setType('email', core_user::get_property_type('email'));
            $mform->addRule('email', get_string('missingemail'), 'required', null, 'client');
            $mform->setForceLtr('email');
        }

        $mform->addElement('passwordunmask', 'password', get_string('password'), 'size="12"');
        $mform->setType('password', core_user::get_property_type('password'));
        $mform->addRule('password', get_string('missingpassword'), 'required', null, 'client');

        if (empty($CFG->createuserwithemail)) {
            $mform->addElement('text', 'email', get_string('email'), 'maxlength="100" size="25"');
            $mform->setType('email', core_user::get_property_type('email'));
            $mform->addRule('email', get_string('missingemail'), 'required', null, 'client');
            $mform->setForceLtr('email');

            $mform->addElement('text', 'email2', get_string('emailagain'), 'maxlength="100" size="25"');
            $mform->setType('email2', core_user::get_property_type('email'));
            $mform->addRule('email2', get_string('missingemail'), 'required', null, 'client');
            $mform->setForceLtr('email2');
        }

        $this->add_action_buttons(true, get_string('enrolkeyuse', 'auth_enrolkey'));
    }

    /**
     * Returns an array with fields that are invalid during signup.
     * This is used in /auth/enrolkey/unsuspend.php.
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $signuptoken = $data['signup_token'];
        $tokenisvalid = false;

        if ($signuptoken !== '') {
            // For any case where the token is populated, perform a lookup.
            $tokenisvalid = $this->check_database_for_signuptoken($signuptoken);

            if ($tokenisvalid === false) {
                $errors['signup_token'] = get_string('signup_token_invalid', 'auth_enrolkey');
            }

        } else {
            // The form submission is an empty string, double check if the token is required.
            if ($this->signup_token_required()) {
                $errors['signup_token'] = get_string('signup_missing', 'auth_enrolkey');
            }
        }

        // This is the check to un-suspend users. This user must exist, be suspended, and not deleted.
        // With a valid enrolkey token, and a user that exists, we will bypass the username/email form validation errors.
        // The next major function which is called will be auth_enrolkey user_signup().
        if ($tokenisvalid && get_config('auth_enrolkey', 'unsuspendaccounts')) {
            $errors = $this->check_for_suspended_user_with_post_data($data, $errors);
        }

        return $errors;
    }

    /**
     * During the user signup page, the POST data for username and email is compared to the DB.
     *
     * @param array $data
     * @param array $errors
     * @return array
     */
    private function check_for_suspended_user_with_post_data($data, $errors) {
        global $CFG;

        $user = utility::search_for_suspended_user($data);
        // A user exists with the same email and username.
        if (!$user) {
            if (empty($CFG->createuserwithemail)) {
                $errors['username'] = get_string('invalidusername');
            } else {
                $errors['email'] = get_string('invalidemail');
            }
        }

        if (!validate_internal_user_password($user, $data['password'])) {
            // Fail internal mform validation. Do not prompt an issue with the password.
            $errors['non_element00'] = 'invalid';
        }

        // Else can't sign up, whatever $errors is returning will fail. eg, the same username, or email.
        return $errors;
    }

    /**
     * Checks the enrolment records for any matching self enrolment key.
     *
     * @param string $token Returns true on success. False on failure.
     * @return bool
     */
    private function check_database_for_signuptoken($token) {
        global $DB;

        $selfenrolinstance = false;

        $instances = $DB->get_records('enrol', array('password' => $token, 'enrol' => 'self'));

        // There may be more than one enrolment instance configured with various dates to check against.
        foreach ($instances as $instance) {
            // There may be things that prevent self enrol, such as requiring a capability, or full course.
            // This should not be a blocker to account creation. The creation should pass, then report the error.
            if ($instance->status == ENROL_INSTANCE_ENABLED) {
                $selfenrolinstance = true;
            }
        }

        // Lookup group enrol keys.
        $instances = $DB->get_records_sql("
                    SELECT e.*
                      FROM {groups} g
                      JOIN {enrol} e ON e.courseid = g.courseid
                                    AND e.enrol = 'self'
                                    AND e.customint1 = 1
                     WHERE g.enrolmentkey = ?
            ", array($token));
        foreach ($instances as $instance) {
            if ($instance->status == ENROL_INSTANCE_ENABLED) {
                $selfenrolinstance = true;
            }
        }

        return $selfenrolinstance;
    }
}
