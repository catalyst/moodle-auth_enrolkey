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

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/login/signup_form.php');

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

        // Make the course token field visible earlier.
        $mform->insertElementBefore($element, 'email');

        if ($this->signup_token_required()) {
            $mform->addRule('signup_token', get_string('signup_missing', 'auth_enrolkey'), 'required', null, 'server');
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
        global $DB;
        $errors = parent::validation($data, $files);

        $enrolplugin = enrol_get_plugin('self');

        $token = $data['signup_token'];

        if (!empty($token)) {
            $selfenrolinstance = false;

            $instances = $DB->get_records('enrol', array('password' => $token, 'enrol' => 'self'));

            // There may be more than one enrolment instance configured with various dates to check against.
            foreach ($instances as $instance) {
                // DO we ever want to pass feedback to user?
                if ($enrolplugin->can_self_enrol($instance) === true) {
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
                // DO we ever want to pass feedback to user?
                if ($enrolplugin->can_self_enrol($instance) === true) {
                    $selfenrolinstance = true;
                }
            }

            // No token matched, this will produce an error message. There are concerns about bruteforcing.
            if (!$selfenrolinstance) {
                $errors['signup_token'] = get_string('signup_token_invalid', 'auth_enrolkey');
            }
        }

        return $errors;
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
