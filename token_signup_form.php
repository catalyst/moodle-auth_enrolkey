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
 * Signup form that provides additional token field.
 *
 * @package    auth_token
 * @copyright  2016 Nicholas Hoobin (nicholashoobin@catalyst-au.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * A new signup form that extends login_signup_form.
 *
 * This provides an additional token that will be validated upon signup.
 *
 * @copyright  2016 Nicholas Hoobin (nicholashoobin@catalyst-au.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class token_signup_form extends login_signup_form {

    /**
     * Creates the Moodle singup form, calls parent::definition();
     */
    public function definition() {
        global $USER, $CFG;

        // Generates the default signup form.
        parent::definition();

        $mform = $this->_form;

        if ($this->signup_token_enabled()) {
            $element = $mform->createElement('text', 'signup_token', get_string('auth_tokensignup_field', 'auth_token'),
                    array('https' => $CFG->loginhttps));

            // View https://docs.moodle.org/dev/lib/formslib.php_Form_Definition#setType for more types.
            $mform->setType('signup_token', PARAM_TEXT);

            // Make the course token field visible earlier.
            $mform->insertElementBefore($element, 'email');

            if ($this->signup_token_required()) {
                $mform->addRule('signup_token', get_string('auth_tokensignup_missing', 'auth_token'), 'required', null, 'server');
            }
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
        global $CFG, $DB;
        $errors = parent::validation($data, $files);

        $authplugin  = get_auth_plugin('token');
        $enrolplugin = enrol_get_plugin('self');

        $token = $data['signup_token'];

        if (!empty($token)) {
            $canenrol = false;

            $instances = $DB->get_records('enrol', array('password' => $token, 'enrol' => 'self'));

            // There may be more than one enrolment instance configured with various dates to check against.
            foreach ($instances as $instance) {
                if ($enrolplugin->can_self_enrol($instance)) {
                    $canenrol = true;
                }
            }

            if (!$canenrol) {
                $errors['signup_token'] = get_string('auth_tokensignup_token_invalid', 'auth_token');
            }
        }

        // Will not print error message with missing the token.
        if (empty($instances) && !empty($token)) {
            $errors['signup_token'] = get_string('auth_tokensignup_token_invalid', 'auth_token');
        }
        return $errors;
    }

    /**
     * Returns if the signup token field is enabled.
     * @return bool
     */
    public function signup_token_enabled() {
        return get_config('auth_token', 'tokenvisible');
    }

    /**
     * Returns if the signup token field is required.
     * @return bool
     */
    public function signup_token_required() {
        return get_config('auth_token', 'tokenrequired');
    }

}