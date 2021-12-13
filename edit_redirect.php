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
 * Configure Enrolkey redirect mapping
 *
 * @package    auth_enrolkey
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use auth_enrolkey\form\enrolkey_redirect_form;
use auth_enrolkey\persistent\enrolkey_redirect_mapping;

require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('auth_enrolkey_manage');

$id = required_param('id', PARAM_INT);
$baseurl = new moodle_url('/auth/enrolkey/edit_redirect.php', ['id' => $id]);

$PAGE->set_url($baseurl);
$PAGE->set_title(get_string('title_redirect', 'auth_enrolkey'));
$PAGE->set_heading(get_string('title_redirect', 'auth_enrolkey'));
$output = $PAGE->get_renderer('auth_enrolkey');

$persistent = enrolkey_redirect_mapping::get_record_by_enrolid($id);

// Create the form instance. We need to use the current URL and the custom data.
$customdata = [
    'persistent' => $persistent,
    'enrolid' => $id
];

$form = new enrolkey_redirect_form($baseurl, $customdata);

// Get the data. This ensures that the form was validated.
if ($form && $form->is_cancelled()) {
    redirect(new moodle_url('/auth/enrolkey/manage.php'));
} else if (($data = $form->get_data())) {

    try {
        if (empty($data->id)) {
            $persistent = enrolkey_redirect_mapping::get_record_by_enrolid($id);
            $persistent->set('url', $data->url);
            $persistent->create();
        } else {
            $persistent->from_record($data);
            if ($data->url == "") {
                $persistent->delete();
            } else {
                $persistent->update();
            }
        }
        \core\notification::success(get_string('changessaved'));
    } catch (Exception $e) {
        \core\notification::error($e->getMessage());
    }

    redirect(new moodle_url('/auth/enrolkey/manage.php'));
}

echo $output->header();
echo $output->heading(get_string('title_redirect', 'auth_enrolkey'));
$form->display();
echo $output->footer();
