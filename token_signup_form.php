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
 * @copyright  2016 Nicholas Hoobin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * A new signup form that extends login_signup_form.
 *
 * This provides an additional token that will be validated upon signup.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package auth_token
 */
class token_signup_form extends login_signup_form {
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

            // The Submit button elements.
            // formslib.php line 1202: 'buttonar' specified.
            $mform->insertElementBefore($element, 'buttonar');
        }

    }

    public function validation($data, $files) {
        global $CFG, $DB;
        $errors = parent::validation($data, $files);

        $authplugin = get_auth_plugin($CFG->registerauth);

        $token = $data['signup_token'];

        // Will not print error message with missing the token.
        if (!empty($token)) {
            $errors['signup_token'] = get_string('auth_tokensignup_token_invalid', 'auth_token');
        }
        return $errors;
    }

    public function signup_token_enabled() {
        return get_config('auth/token', 'authtoken');
    }

}