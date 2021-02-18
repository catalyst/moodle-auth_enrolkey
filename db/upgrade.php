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
 * Enrolkey authentication plugin upgrade code
 *
 * @package    auth_enrolkey
 * @copyright  2021 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade function
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_auth_enrolkey_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2021021700.01) {
        // Define table auth_enrolkey_redirect to be created.
        $table = new xmldb_table('auth_enrolkey_redirect');

        // Adding fields to table auth_enrolkey_redirect.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, null);
        $table->add_field('enrolid', XMLDB_TYPE_INTEGER, '18', null, null, null, null);
        $table->add_field('url', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Adding keys to table auth_enrolkey_redirect.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('usermodified_key', XMLDB_KEY_FOREIGN, array('usermodified'), 'user', array('id'));

        // Conditionally launch create table for auth_enrolkey_redirect.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Enrolkey savepoint reached.
        upgrade_plugin_savepoint(true, 2021021700.01, 'auth', 'enrolkey');
    }

    if ($oldversion < 2021021700.02) {
        // Define table auth_enrolkey_profile to be created.
        $table = new xmldb_table('auth_enrolkey_profile');

        // Adding fields to table auth_enrolkey_cohort.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, null);
        $table->add_field('enrolid', XMLDB_TYPE_INTEGER, '18', null, null, null, null);
        $table->add_field('profilefieldname', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('profilefielddata', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Adding keys to table auth_enrolkey_redirect.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('usermodified_key', XMLDB_KEY_FOREIGN, array('usermodified'), 'user', array('id'));

        // Conditionally launch create table for auth_enrolkey_redirect.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Enrolkey savepoint reached.
        upgrade_plugin_savepoint(true, 2021021700.02, 'auth', 'enrolkey');
    }

    if ($oldversion < 2021021700.03) {
        // Define table auth_enrolkey_cohort to be created.
        $table = new xmldb_table('auth_enrolkey_cohort');

        // Adding fields to table auth_enrolkey_cohort.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, null);
        $table->add_field('enrolid', XMLDB_TYPE_INTEGER, '18', null, null, null, null);
        $table->add_field('cohortid', XMLDB_TYPE_INTEGER, '18', null, null, null, null);

        // Adding keys to table auth_enrolkey_redirect.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('usermodified_key', XMLDB_KEY_FOREIGN, array('usermodified'), 'user', array('id'));

        // Conditionally launch create table for auth_enrolkey_redirect.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Enrolkey savepoint reached.
        upgrade_plugin_savepoint(true, 2021021700.03, 'auth', 'enrolkey');
    }

    return true;
}
