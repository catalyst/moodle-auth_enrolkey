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
 * Class for mapping enrolkey to profile fields.
 *
 * @package    auth_enrolkey
 * @copyright  2021 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace auth_enrolkey\persistent;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/user/profile/lib.php');

use core\persistent;
/**
 * Class for mapping enrolkey to corhots.
 *
 * @package    auth_enrolkey
 * @copyright  2021 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrolkey_profile_mapping extends persistent {

    /** Table name for the persistent. */
    const TABLE = 'auth_enrolkey_profile';

    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties() {
        return array(
            'enrolid' => array(
                'type' => PARAM_INT,
            ),
            'profilefieldname' => array(
                'type' => PARAM_TEXT,
            ),
            'profilefielddata' => array(
                'type' => PARAM_TEXT,
            )
        );
    }

    /**
     * Get the records for the enrolid.
     *
     * @param int $enrolid The enrolid
     * @return enrolkey_profile_mapping[]
     */
    public static function get_records_by_enrolid($enrolid) {
        $records = self::get_records(['enrolid' => $enrolid]);

        $result = [];
        foreach ($records as $persistent) {
            $result[$persistent->get('profilefieldname')] = $persistent;
        }

        return $result;
    }

    /**
     * During auth.php->user_signup, this adds the forced user profile fields.
     *
     * @param stdClass $user
     * @param array $availableenrolids
     */
    public static function add_fields_during_signup($user, $availableenrolids) {
        // Obtain the existing user profile fields.
        $userfields = (array) profile_user_record($user->id);

        foreach ($userfields as $field => $value) {
            $key = 'profile_field_' . $field;
            $user->$key = $value;
        }

        foreach ($availableenrolids as $enid) {
            $records = self::get_records_by_enrolid($enid);
            foreach ($records as $persistent) {
                $field = $persistent->get('profilefieldname');
                $data = $persistent->get_readable_value();

                // Prevent saving null data to the profile fields.
                if (!is_null($data)) {
                    $user->$field = $data;
                }
            }

        }

        // Do it.
        profile_save_data($user, true);
    }

    /**
     * Returns the field name without the prefix 'profile_field_'.
     *
     * @return string
     */
    public function get_readable_name() {
        return str_replace('profile_field_', '', $this->get('profilefieldname'));
    }

    /**
     * Returns the data assocaited with a profile field. This may also return the value selected in the multipart
     * dropdown.
     *
     * @return mixed
     */
    public function get_readable_value() {
        global $DB;

        $key = $this->get('profilefieldname');
        $value = $this->get('profilefielddata');

        $shortname = str_replace('profile_field_', '', $key);

        $select = $DB->sql_compare_text('shortname') . ' = ' . $DB->sql_compare_text(':shortname');
        $select .= ' AND ' . $DB->sql_compare_text('datatype') . ' = ' . $DB->sql_compare_text(':datatype');
        $params = [
            'shortname' => $shortname,
            'datatype' => 'menu'
        ];

        $record = $DB->get_record_select('user_info_field', $select, $params);

        if ($record) {
            // The param1 is a \n delimited list of dropdown choices.
            $values = explode("\n", $record->param1);
            if (array_key_exists((int)$value, $values)) {
                return $values[$value];
            }
        }

        // Else.
        return $value;
    }
}
