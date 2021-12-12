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
 * Class for mapping enrolkey to cohorts.
 *
 * @package    auth_enrolkey
 * @copyright  2021 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace auth_enrolkey\persistent;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/cohort/lib.php');

use core\persistent;

/**
 * Class for mapping enrolkey to corhots.
 *
 * @package    auth_enrolkey
 * @copyright  2021 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrolkey_cohort_mapping extends persistent {

    /** Table name for the persistent. */
    const TABLE = 'auth_enrolkey_cohort';

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
            'cohortid' => array(
                'type' => PARAM_INT,
            ),
        );
    }

    /**
     * Get the records for the enrolid.
     *
     * @param int $enrolid The enrolid
     * @return enrolkey_cohort_mapping[]
     */
    public static function get_records_by_enrolid($enrolid) {
        $records = self::get_records(['enrolid' => $enrolid]);

        $result = [];
        foreach ($records as $persistent) {
            $result[$persistent->get('cohortid')] = $persistent;
        }

        return $result;
    }

    /**
     * During auth.php->user_signup, this adds the user to the associated cohorts.
     *
     * @param stdClass $user
     * @param array $availableenrolids
     */
    public static function add_cohorts_during_signup($user, $availableenrolids) {
        foreach ($availableenrolids as $enrolid) {
            $records = self::get_records_by_enrolid($enrolid);

            foreach ($records as $cohortid => $persistent) {
                cohort_add_member($cohortid, $user->id);
            }
        }
    }

    /**
     * Returns moodle_url for managing the cohort.
     *
     * @return \moodle_url
     */
    public function get_moodle_url() {
        $params = ['id' => $this->get('cohortid')];
        $moodleurl = new \moodle_url('/cohort/view.php', $params);
        return $moodleurl;
    }
}
