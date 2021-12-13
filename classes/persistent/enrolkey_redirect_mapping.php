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
 * Class for mapping enrolkey to redirection urls.
 *
 * @package    auth_enrolkey
 * @copyright  2021 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace auth_enrolkey\persistent;

defined('MOODLE_INTERNAL') || die();

use core\persistent;
/**
 * Class for mapping enrolkey to redirection urls.
 *
 * @package    auth_enrolkey
 * @copyright  2021 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrolkey_redirect_mapping extends persistent {

    /** Table name for the persistent. */
    const TABLE = 'auth_enrolkey_redirect';

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
            'url' => array(
                'default' => '',
                'type' => PARAM_URL,
            ),
        );
    }

    /**
     * Get the record for the enrol, or creates a new record if the enrolid does not match a row.
     *
     * @param int $enrolid The enrolid
     * @return enrolkey_redirect_mapping
     */
    public static function get_record_by_enrolid($enrolid) {
        $persistent = self::get_record(['enrolid' => $enrolid]);

        if (!$persistent) {
            // Create the record for this enrolid if it does not exist.
            $persistent = new enrolkey_redirect_mapping(0, (object) ['enrolid' => $enrolid]);
        }
        return $persistent;
    }

    /**
     * During auth.php->user_signup, this redirects the user to a specified url.
     *
     * @param array $availableenrolids
     */
    public static function redirect_during_signup($availableenrolids) {
        // TODO: Redirect weight.
        foreach ($availableenrolids as $enid) {
            $persistent = self::get_record_by_enrolid($enid);
            $url = $persistent->get('url');
            if ($url != "") {
                redirect($persistent->get_moodle_url());
            }
        }
    }

    /**
     * Returns moodle_url for value saved.
     *
     * @return \moodle_url
     */
    public function get_moodle_url() {
        $moodleurl = new \moodle_url($this->get('url'));
        return $moodleurl;
    }
}
