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
 * This contains the auth_enrolkey url redirect form.
 *
 * @package    auth_enrolkey
 * @copyright  2021 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_enrolkey\form;

defined('MOODLE_INTERNAL') || die();

use core\form\persistent;

/**
 * Redirect form.
 *
 * @package    auth_enrolkey
 * @copyright  2021 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrolkey_redirect_form extends persistent {

    /** @var string $persistentclass */
    protected static $persistentclass = 'auth_enrolkey\\persistent\\enrolkey_redirect_mapping';

    /**
     * Define the form - called by parent constructor
     */
    public function definition() {
        $mform = $this->_form;
        $persistent = $this->get_persistent();

        // External.
        $mform->addElement('text', 'url', get_string('label_redirection', 'auth_enrolkey'),  ['size' => '100']);
        $mform->setType('url', PARAM_TEXT);
        $mform->addHelpButton('url', 'label_redirection', 'auth_enrolkey');

        $mform->addElement('hidden', 'enrolid', $persistent->get('enrolid'));
        $mform->setType('enrolid', PARAM_INT);

        $this->add_action_buttons(true);
    }
}

