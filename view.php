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
 * Post signup page to notify user of courses enrolled via enrolment keys.
 *
 * @package    auth_enrolkey
 * @copyright  2016 Nicholas Hoobin (nicholashoobin@catalyst-au.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

if (isset($SESSION->auth_enrolkey) && isset($SESSION->availableenrolids)) {

    $PAGE->set_url(new moodle_url('/admin/enrolkey/view.php'));

    $PAGE->set_course($SITE);
    $PAGE->set_title(get_string('pluginname', 'auth_enrolkey'));
    $PAGE->set_heading(get_string('signup_view', 'auth_enrolkey'));

    echo $OUTPUT->header();

    $authtoken = $SESSION->auth_enrolkey;
    $availableenrolids = $SESSION->availableenrolids;

    foreach ($availableenrolids as $enrolid) {
        $plugin = $DB->get_record('enrol', array('enrol' => 'self', 'password' => $authtoken, 'id' => $enrolid));
        $course = $DB->get_record('course', array('id' => $plugin->courseid));

        $coursecontext = context_course::instance($plugin->courseid);
        $rolenames = role_get_names($coursecontext, ROLENAME_ALIAS, true);

        $data = new stdClass();
        $data->course        = $course->fullname;
        $data->enrolinstance = $plugin->name;
        $data->role          = $rolenames[$plugin->roleid];
        $data->startdate     = date('Y-m-d H:i', $plugin->enrolstartdate);
        $data->enddate       = date('Y-m-d H:i', $plugin->enrolenddate);
        $data->href          = '/course/view.php?id=' . $plugin->courseid;

        if ($plugin->enrolstartdate > 0 && $plugin->enrolenddate > 0) {
            // The course had both a start and end date.
            $successoutput = get_string('signup_view_message_basic_dates', 'auth_enrolkey', $data);

        } else if ($plugin->enrolstartdate > 0 && $plugin->enrolenddate == 0) {
            // The course only has a start date set.
            $successoutput = get_string('signup_view_message_basic_dates_startonly', 'auth_enrolkey', $data);

        } else if ($plugin->enrolstartdate == 0 && $plugin->enrolenddate > 0) {
            // The course only has a start date set.
            $successoutput = get_string('signup_view_message_basic_dates_endonly', 'auth_enrolkey', $data);

        } else {
            // The course has no date restrictions.
            $successoutput = get_string('signup_view_message_basic', 'auth_enrolkey', $data);

        }

        echo $OUTPUT->notification($successoutput, 'notifysuccess');
    }

    echo $OUTPUT->continue_button(new moodle_url('/index.php'));

    echo $OUTPUT->footer();

}

unset($SESSION->auth_enrolkey);
unset($SESSION->availableenrolids);


