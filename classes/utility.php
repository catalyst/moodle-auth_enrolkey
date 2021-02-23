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
 * Helper class for auth_enrolkey
 *
 * @package    auth_enrolkey
 * @copyright  2021 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_enrolkey;

defined('MOODLE_INTERNAL') || die;

/**
 * Helper class for auth_enrolkey
 *
 * @package    auth_enrolkey
 * @copyright  2021 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utility {

    /**
     * Find the username / email combination of a user that is, not deleted, but suspended.
     *
     * @param $data
     * @return \stdClass|bool The user record, or false.
     */
    public static function search_for_suspended_user($data) {
        global $DB, $CFG;

        $params = [
            'suspended' => 1,
            'deleted' => 0,
            'mnethostid' => $CFG->mnet_localhost_id,
            'auth' => 'enrolkey'
        ];

        if (array_key_exists('email', $data)) {
            $params['email'] = $data['email'];
        }

        if (array_key_exists('username', $data)) {
            $params['username'] = $data['username'];
        }

        // This setting means the email address is the username.
        if ($CFG->createuserwithemail) {
            $params['username'] = $data['email'];
        }

        return $DB->get_record('user', $params, '*');
    }

    /**
     * Given a $user object, this will unsuspend them.
     *
     * @param $user
     * @return bool Returns true if the user is unsuspended.
     */
    public static function unsuspend_user($user) {
        if ($user->suspended == 1) {
            $user->suspended = 0;
            user_update_user($user, false, true);
            return true;
        }

        return false;
    }
}
