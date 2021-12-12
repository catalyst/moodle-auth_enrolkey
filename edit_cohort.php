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
 * Configure Enrolkey cohort mapping
 *
 * @package    auth_enrolkey
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


use auth_enrolkey\form\enrolkey_cohort_form;
use auth_enrolkey\persistent\enrolkey_cohort_mapping;

require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('auth_enrolkey_manage');

$id = required_param('id', PARAM_INT);
$baseurl = new moodle_url('/auth/enrolkey/edit_cohort.php', ['id' => $id]);

$PAGE->set_url($baseurl);
$PAGE->set_title(get_string('title_cohort', 'auth_enrolkey'));
$output = $PAGE->get_renderer('auth_enrolkey');

$cohortlist = cohort_get_all_cohorts(0, 5000, '');

$records = enrolkey_cohort_mapping::get_records_by_enrolid($id);

$customdata = ['cohorts' => $cohortlist];
$form = new enrolkey_cohort_form($baseurl, $customdata);

// Get the data. This ensures that the form was validated.
if ($form && $form->is_cancelled()) {
    redirect(new moodle_url('/auth/enrolkey/manage.php'));
} else if (($data = $form->get_data())) {

    $savedcohortids = [];

    foreach ($data->cohortids as $cid) {
        $savedcohortids[$cid] = $cid;

        // The cohort is not in out list of records, so we will create it.
        if (!array_key_exists($cid, $records)) {
            $pdata = (object) [
                'enrolid' => $id,
                'cohortid' => $cid
            ];
            $persistent = new enrolkey_cohort_mapping(0, $pdata);
            $persistent->save();

            // Add to the list of records which is used later.
            $records[$cid] = $persistent;
        }
    }

    // Cleanup older persistents.
    foreach ($records as $cid => $persistent) {
        if (!array_key_exists($cid, $savedcohortids)) {
            $persistent->delete();
        }
    }

    redirect(new moodle_url('/auth/enrolkey/manage.php'));
}

echo $output->header();
echo $output->heading(get_string('title_cohort', 'auth_enrolkey'));
$form->set_autocomplete_data($records);
$form->display();
echo $output->footer();
