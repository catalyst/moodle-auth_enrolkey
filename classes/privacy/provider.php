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
 * Privacy provider.
 *
 * @package   auth_enrolkey
 * @author    Ilya Tregubov (ilyatregubov@catalyst-au.net)
 * @copyright 2018 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace auth_enrolkey\privacy;
defined('MOODLE_INTERNAL') || die;
use core_privacy\local\metadata\null_provider;
use core_privacy\local\legacy_polyfill;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\writer;
use core_privacy\local\request\userlist;

/**
 * Class provider
 * @package auth_enrolkey\privacy
 */
class provider implements \core_privacy\local\metadata\provider,
                          \core_privacy\local\request\core_userlist_provider,
                          \core_privacy\local\request\plugin\provider
{
    /**
     * Returns metadata about this plugin's privacy policy.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return  collection     A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
                'auth_enrolkey_redirect',
                [
                        'usermodified' => 'privacy:metadata:auth_enrolkey_redirect:usermodified',
                ],
                'privacy:metadata:auth_enrolkey_redirect'
        );
        $collection->add_database_table(
                'auth_enrolkey_profile',
                [
                        'usermodified' => 'privacy:metadata:auth_enrolkey_profile:usermodified',
                ],
                'privacy:metadata:auth_enrolkey_profile'
        );
        $collection->add_database_table(
                'auth_enrolkey_cohort',
                [
                        'usermodified' => 'privacy:metadata:auth_enrolkey_cohort:usermodified',
                ],
                'privacy:metadata:auth_enrolkey_cohort'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the given user.
     *
     * @param int $userid the userid to search.
     * @return contextlist the contexts in which data is contained.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $sql = "SELECT ctx.id
                  FROM {user_enrolments} ue
                  JOIN {enrol} e
                    ON e.id = ue.enrolid
                   AND ue.userid = :userid
                  JOIN {context} ctx
                    ON ctx.instanceid = e.courseid
                   AND ctx.contextlevel = :contextlevel
                  JOIN {auth_enrolkey_redirect} aer
                    ON aer.usermodified = ue.userid
                  JOIN {auth_enrolkey_profile} aep
                    ON aep.usermodified = ue.userid
                  JOIN {auth_enrolkey_cohort} aec
                    ON aec.usermodified = ue.userid";
        $params = [
                'contextlevel' => CONTEXT_USER,
                'userid'       => $userid
        ];
        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Gets the list of users who have data with a context.
     *
     * @param userlist $userlist the userlist containing users who have data in this context.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof \context_user) {
            return;
        }

        // If current context is course, all users are contained within, get all users.
        if ($context->contextlevel == CONTEXT_USER) {
            $params = [
                    'contextlevel' => CONTEXT_USER,
                    'contextid' => $context->id,
            ];

            $sql = "SELECT usermodified AS userid FROM {auth_enrolkey_redirect} aer
                        JOIN {enrol} e
                            ON e.id = aer.enrolid
                        JOIN {context} ctx
                            ON ctx.instanceid = e.courseid
                        WHERE ctx.contextlevel = :contextlevel
                            AND ctx.instanceid = :contextid";
            $userlist->add_from_sql('usermodified', $sql, $params);

            $sql = "SELECT usermodified AS userid FROM {auth_enrolkey_profile} aep
                        JOIN {enrol} e
                            ON e.id = aep.enrolid
                        JOIN {context} ctx
                            ON ctx.instanceid = e.courseid
                        WHERE ctx.contextlevel = :contextlevel
                            AND ctx.instanceid = :contextid";
            $userlist->add_from_sql('usermodified', $sql, $params);

            $sql = "SELECT usermodified AS userid FROM {auth_enrolkey_cohort} aec
                        JOIN {enrol} e
                            ON e.id = aec.enrolid
                        JOIN {context} ctx
                            ON ctx.instanceid = e.courseid
                        WHERE ctx.contextlevel = :contextlevel
                            AND ctx.instanceid = :contextid";
            $userlist->add_from_sql('usermodified', $sql, $params);
        }
    }

    /**
     * Exports all data stored in provided contexts for user.
     *
     * @param approved_contextlist $contextlist the list of contexts to export for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        // Export auth enrolkey linked accounts.
        $userid = $contextlist->get_user()->id;
        $context = \context_user::instance($userid);
        $params = [
                'contextlevel' => CONTEXT_USER,
                'contextid' => $context->id,
                'userid' => $userid
        ];
        $sql = "SELECT * FROM {user} u
                  JOIN {user_enrolments} ue
                    ON ue.userid = u.id
                  JOIN {enrol} e
                    ON e.id = ue.enrolid
                   AND ue.userid = :userid
                  JOIN {context} ctx
                    ON ctx.instanceid = e.courseid
                   AND ctx.contextlevel = :contextlevel
                  JOIN {auth_enrolkey_redirect} aer
                    ON aer.usermodified = ue.userid
                  JOIN {auth_enrolkey_profile} aep
                    ON aep.usermodified = ue.userid
                  JOIN {auth_enrolkey_cohort} aec
                    ON aec.usermodified = ue.userid
                  GROUP BY u.id";
        if ($users = $DB->get_records_sql($sql, $params)) {
            foreach ($users as $user) {
                $data = (object) [
                        'timecreated' => transform::datetime($user->timecreated),
                        'timemodified' => transform::datetime($user->timemodified),
                        'username' => $user->username,
                        'email' => $user->email
                ];
                writer::with_context($context)->export_data(
                        [
                                get_string('privacy:metadata:auth_enrolkey', 'auth_enrolkey'),
                                $user->id
                        ],
                        $data
                );
            }
        }
    }

    /**
     * Deletes data for all users in context.
     *
     * @param context $context The context to delete for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        if ($context->contextlevel != CONTEXT_USER) {
            return;
        }
        static::delete_user_data($context->instanceid);
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        $context = $userlist->get_context();

        if ($context instanceof \context_user) {
            static::delete_user_data($context->instanceid);
        }
    }

    /**
     * Delete all user data for this user only.
     *
     * @param  approved_contextlist $contextlist The list of approved contexts for a user.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        if (empty($contextlist->count())) {
            return;
        }
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_USER) {
                continue;
            }
            if ($context->instanceid == $userid) {
                // Because we only use user contexts the instance ID is the user ID.
                static::delete_user_data($context->instanceid);
            }
        }
    }

    /**
     * This does the deletion of user data for the auth_oauth2.
     *
     * @param  int $userid The user ID
     */
    protected static function delete_user_data(int $userid) {
        global $DB;

        // Because we only use user contexts the instance ID is the user ID.
        $DB->delete_records('auth_enrolkey_redirect', ['usermodified' => $userid]);
        $DB->delete_records('auth_enrolkey_profile', ['usermodified' => $userid]);
        $DB->delete_records('auth_enrolkey_cohort', ['usermodified' => $userid]);
    }
}
