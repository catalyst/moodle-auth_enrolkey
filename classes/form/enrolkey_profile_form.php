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
 * This contains the auth_enrolkey profile form.
 *
 * @package    auth_enrolkey
 * @copyright  2021 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_enrolkey\form;

use core\persistent;
use core_user;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/user/editlib.php');


/**
 * Profile form.
 *
 * @package    auth_enrolkey
 * @copyright  2021 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrolkey_profile_form extends \moodleform {

    /**
     * Define the form - called by parent constructor
     */
    public function definition() {
        global $USER;

        $mform = $this->_form;

        // Customisable profile fields.
        profile_definition($mform, 0);

        // Moodle optional fields.
        $mform->addElement('header', 'moodle_optional', get_string('optional', 'form'));
        $mform->setExpanded('moodle_optional', false);

        $mform->addElement('text', 'url', get_string('webpage'), 'maxlength="255" size="50"');
        $mform->setType('url', core_user::get_property_type('url'));

        $mform->addElement('text', 'icq', get_string('icqnumber'), 'maxlength="15" size="25"');
        $mform->setType('icq', core_user::get_property_type('icq'));
        $mform->setForceLtr('icq');

        $mform->addElement('text', 'skype', get_string('skypeid'), 'maxlength="50" size="25"');
        $mform->setType('skype', core_user::get_property_type('skype'));
        $mform->setForceLtr('skype');

        $mform->addElement('text', 'aim', get_string('aimid'), 'maxlength="50" size="25"');
        $mform->setType('aim', core_user::get_property_type('aim'));
        $mform->setForceLtr('aim');

        $mform->addElement('text', 'yahoo', get_string('yahooid'), 'maxlength="50" size="25"');
        $mform->setType('yahoo', core_user::get_property_type('yahoo'));
        $mform->setForceLtr('yahoo');

        $mform->addElement('text', 'msn', get_string('msnid'), 'maxlength="50" size="25"');
        $mform->setType('msn', core_user::get_property_type('msn'));
        $mform->setForceLtr('msn');

        $mform->addElement('text', 'idnumber', get_string('idnumber'), 'maxlength="255" size="25"');
        $mform->setType('idnumber', core_user::get_property_type('idnumber'));

        $mform->addElement('text', 'institution', get_string('institution'), 'maxlength="255" size="25"');
        $mform->setType('institution', core_user::get_property_type('institution'));

        $mform->addElement('text', 'department', get_string('department'), 'maxlength="255" size="25"');
        $mform->setType('department', core_user::get_property_type('department'));

        $mform->addElement('text', 'phone1', get_string('phone1'), 'maxlength="20" size="25"');
        $mform->setType('phone1', core_user::get_property_type('phone1'));
        $mform->setForceLtr('phone1');

        $mform->addElement('text', 'phone2', get_string('phone2'), 'maxlength="20" size="25"');
        $mform->setType('phone2', core_user::get_property_type('phone2'));
        $mform->setForceLtr('phone2');

        $mform->addElement('text', 'address', get_string('address'), 'maxlength="255" size="25"');
        $mform->setType('address', core_user::get_property_type('address'));

        $group = array();
        $group[] = $mform->createElement('cancel');
        $group[] = $mform->createElement('submit', 'resetbutton', get_string('reset'));
        $group[] = $mform->createElement('submit', 'submitbutton', get_string('submit'));
        $mform->addGroup($group, 'buttons', '', ' ', false);
        $mform->closeHeaderBefore('buttons');

        // Some of the headers are dynamically named, so lets iterate through them to set their visibility.
        foreach ($mform->_elements as $ele) {
            if ($ele instanceof \MoodleQuickForm_header) {
                $mform->setExpanded($ele->getName(), true);
            }
        }
    }

    /**
     * Helper function to set the form fields.
     *
     * @param persistent[] $currentdata
     */
    public function set_autocomplete_data($currentdata) {
        $toset = [];

        foreach ($currentdata as $id => $persistent) {
            $key = $persistent->get('profilefieldname');
            $data = $persistent->get('profilefielddata');

            $toset[$key] = $data;
        }

        $this->set_data($toset);
    }

}

