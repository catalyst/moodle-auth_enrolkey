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
 * Signup form that provides additional enrolment key field.
 *
 * @package    auth_enrolkey
 * @copyright  2016 Nicholas Hoobin (nicholashoobin@catalyst-au.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * A new signup form that extends login_signup_form.
 *
 * This provides an additional enrolment key field that will be validated upon signup.
 *
 * @copyright  2016 Nicholas Hoobin (nicholashoobin@catalyst-au.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace auth_enrolkey\form;

use auth_enrolkey\utility;
use moodle_url;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/login/signup_form.php');

/**
 * Class for the enrolkey signup form.
 *
 * @package    auth_enrolkey
 * @copyright  2021 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrolkey_signup_form extends \login_signup_form {

    /**
     * Creates the Moodle singup form, calls parent::definition();
     */
    public function definition() {
        global $CFG;

        // Generates the default signup form.
        parent::definition();

        $mform = $this->_form;

        $element = $mform->createElement('text', 'signup_token', get_string('signup_field_title', 'auth_enrolkey'));

        // View https://docs.moodle.org/dev/lib/formslib.php_Form_Definition#setType for more types.
        $mform->setType('signup_token', PARAM_TEXT);
        $token = optional_param('signup_token', '', PARAM_TEXT);
        if (!empty($token)) {
            $mform->setDefault('signup_token', $token);
        }

        // Make the course token field visible earlier.
        $mform->insertElementBefore($element, 'email');

        if ($this->signup_token_required()) {
            $mform->addRule('signup_token', get_string('signup_missing', 'auth_enrolkey'), 'required', null, 'client');
        }

        if ($this->signup_captcha_enabled()) {
            $mform->addElement('recaptcha', 'recaptcha_element', get_string('security_question', 'auth'));
            $mform->addHelpButton('recaptcha_element', 'recaptcha', 'auth');
            $mform->closeHeaderBefore('recaptcha_element');
        }
    }

    /**
     * Returns an array with fields that are invalid during signup.
     * This is used in /login/signup.php.
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files) {
        global $CFG;

        $errors = parent::validation($data, $files);

        $signuptoken = $data['signup_token'];

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

        if (get_config('auth_enrolkey', 'unsuspendaccounts') && utility::search_for_suspended_user($data)) {

            $stringdata = (object) ['href' => (new moodle_url('/auth/enrolkey/unsuspend.php'))->out()];
            if (empty($CFG->createuserwithemail)) {
                $errors['username'] = $errors['username'] . get_string('suspendeduseratsignup', 'auth_enrolkey', $stringdata);
            } else {
                $errors['email'] = $errors['email' ] . get_string('suspendeduseratsignup', 'auth_enrolkey', $stringdata);
            }
        }

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

    /**
     * Returns if the enrolment key field is required.
     * @return bool
     */
    public function signup_token_required() {
        return get_config('auth_enrolkey', 'tokenrequired');
    }

    /**
     * Returns whether or not the captcha element is enabled, and the admin settings fulfil its requirements.
     * @return bool
     */
    public function signup_captcha_enabled() {
        global $CFG;
        return !empty($CFG->recaptchapublickey) && !empty($CFG->recaptchaprivatekey) && get_config('auth_enrolkey', 'recaptcha');
    }

}
