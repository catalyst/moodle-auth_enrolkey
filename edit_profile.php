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
 * Configure Enrolkey profile mapping
 *
 * @package    auth_enrolkey
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


use auth_enrolkey\form\enrolkey_profile_form;
use auth_enrolkey\persistent\enrolkey_profile_mapping;

require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('auth_enrolkey_manage');

$enrolid = required_param('id', PARAM_INT);
$baseurl = new moodle_url('/auth/enrolkey/edit_profile.php', ['id' => $enrolid]);

$PAGE->set_url($baseurl);
$PAGE->set_title(get_string('title_profile', 'auth_enrolkey'));
$output = $PAGE->get_renderer('auth_enrolkey');

$records = enrolkey_profile_mapping::get_records_by_enrolid($enrolid);

$customdata = ['currentdata' => $records];
$form = new enrolkey_profile_form($baseurl, $customdata);

// Get the data. This ensures that the form was validated.
if ($form && $form->is_cancelled()) {
    redirect(new moodle_url('/auth/enrolkey/manage.php'));

} else if (optional_param('resetbutton', 0, PARAM_ALPHA)) {
    foreach ($records as $persistent) {
        $persistent->delete();
        unset($records, $persistent);
    }
    redirect($baseurl);

} else if (($data = $form->get_data())) {
    $ignore = [
        'submitbutton',
        'cancelbutton',
        'resetbutton',
    ];

    foreach ($data as $key => $value) {
        // Ignore the buttons.
        if (in_array($key, $ignore)) {
            continue;
        }

        // Ignore the headers.
        if (preg_match("/^mform_isexpanded_id.*/", $key, $matches)) {
            continue;
        }

        if ($value == "") {
            // Delete the persistent if it exists.
            if (array_key_exists($key, $records)) {
                $persistent = $records[$key];
                $persistent->delete();
            }

        } else if (is_array($value)) {
            // Special case for 'interests' tag list and potentially others.
            // TODO: Revisit this?
            null;

        } else {
            // Update an existing persistent record.
            if (array_key_exists($key, $records)) {
                $persistent = $records[$key];
                $persistent->set('profilefielddata', $value);
                $persistent->update();

            } else {
                // Create the persistent.
                $pdata = (object) [
                    'enrolid' => $enrolid,
                    'profilefieldname' => $key,
                    'profilefielddata' => $value,
                ];
                $persistent = new enrolkey_profile_mapping(0, $pdata);
                $persistent->save();
            }
        }
    }

    redirect(new moodle_url('/auth/enrolkey/manage.php'));
}


echo $output->header();
echo $output->heading(get_string('title_profile', 'auth_enrolkey'));
$form->set_autocomplete_data($records);
$form->display();
echo $output->footer();
