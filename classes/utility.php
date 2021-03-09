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

use moodle_database;

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
     * @param array $data
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
     * @param stdClass $user
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

    /**
     * Unsuspends the and enrols the $USER with the $enrolkey
     *
     * @param string $enrolkey
     * @param bool $checkuserenrolment
     * @return array
     */
    public static function unsuspend_and_enrol_user(string $enrolkey, bool $checkuserenrolment = true) : array {
        global $DB;

        /** @var enrol_self_plugin $enrol */
        $enrol = enrol_get_plugin('self');
        $enrolplugins = self::get_enrol_plugins($DB, $enrolkey);
        $availableenrolids = [];
        $errors = [];
        foreach ($enrolplugins as $enrolplugin) {
            if (self::can_self_enrol($enrolplugin, $checkuserenrolment) === true) {
                $data = new \stdClass();
                $data->enrolpassword = $enrolplugin->enrolmentkey ?? $enrolplugin->password;
                $enrol->enrol_self($enrolplugin, $data);
                $availableenrolids[] = $enrolplugin->id;
            } else {
                // Store error to output.
                $errors[$enrolplugin->courseid] = $enrol->can_self_enrol($enrolplugin);
            }
        }
        return [$availableenrolids, $errors];
    }

    /**
     * Returns the list of enrolkey plugins which use the $enrolkey
     *
     * @param moodle_database $db
     * @param string $enrolkey
     * @return array
     */
    public static function get_enrol_plugins(moodle_database $db, string $enrolkey) : array {
        // Password is the Enrolment key that is specified in the Self enrolment instance.
        $enrolplugins = $db->get_records('enrol', ['enrol' => 'self', 'password' => $enrolkey]);

        return array_merge($enrolplugins, $db->get_records_sql("
                SELECT e.*, g.enrolmentkey
                  FROM {groups} g
                  JOIN {enrol} e ON e.courseid = g.courseid
                                AND e.enrol = 'self'
                                AND e.customint1 = 1
                 WHERE g.enrolmentkey = ?
            ", [$enrolkey]));
    }

    /**
     * Checks if user can self enrol. Copied from enrol/self/lib.php. Modified to remove the check if user is already enroled
     *
     * @param \stdClass $instance enrolment instance
     * @param bool $checkuserenrolment if true will check if user enrolment is inactive.
     *             used by navigation to improve performance.
     * @return bool|string true if successful, else error message or false.
     */
    public static function can_self_enrol(\stdClass $instance, $checkuserenrolment = true) {
        global $CFG, $DB, $USER;

        if ($checkuserenrolment) {
            if (isguestuser() || !isloggedin()) {
                // Can not enrol guests or unauthenticated users.
                return get_string('noguestaccess', 'enrol') . ' ' . html_writer::link(get_login_url(), get_string('login', 'core'), array('class' => 'btn btn-default'));
            }
            // Check if user is already enroled.
            if ($DB->get_record('user_enrolments', array('userid' => $USER->id, 'enrolid' => $instance->id))) {
                return get_string('canntenrol', 'enrol_self');
            }
        }

        if ($instance->status != ENROL_INSTANCE_ENABLED) {
            return get_string('canntenrol', 'enrol_self');
        }

        if ($instance->enrolstartdate != 0 and $instance->enrolstartdate > time()) {
            return get_string('canntenrolearly', 'enrol_self', userdate($instance->enrolstartdate));
        }

        if ($instance->enrolenddate != 0 and $instance->enrolenddate < time()) {
            return get_string('canntenrollate', 'enrol_self', userdate($instance->enrolenddate));
        }

        if (!$instance->customint6) {
            // New enrols not allowed.
            return get_string('canntenrol', 'enrol_self');
        }

        if ($checkuserenrolment) {
            if ($DB->record_exists('user_enrolments', array('userid' => $USER->id, 'enrolid' => $instance->id))) {
                return get_string('canntenrol', 'enrol_self');
            }
        }

        if ($instance->customint3 > 0) {
            // Max enrol limit specified.
            $count = $DB->count_records('user_enrolments', array('enrolid' => $instance->id));
            if ($count >= $instance->customint3) {
                // Bad luck, no more self enrolments here.
                return get_string('maxenrolledreached', 'enrol_self');
            }
        }

        if ($instance->customint5) {
            require_once("$CFG->dirroot/cohort/lib.php");
            if (!cohort_is_member($instance->customint5, $USER->id)) {
                $cohort = $DB->get_record('cohort', array('id' => $instance->customint5));
                if (!$cohort) {
                    return null;
                }
                $a = format_string($cohort->name, true, array('context' => context::instance_by_id($cohort->contextid)));
                return markdown_to_html(get_string('cohortnonmemberinfo', 'enrol_self', $a));
            }
        }

        return true;
    }
}
