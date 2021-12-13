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
 * Configure Enrolkey mapping
 *
 * @package    auth_enrolkey
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');


admin_externalpage_setup('auth_enrolkey_manage');
$baseurl = new moodle_url('/auth/enrolkey/manage.php');

$PAGE->set_url($baseurl);
$output = $PAGE->get_renderer('auth_enrolkey');

echo $output->header();

$o = $output->render_available_table($baseurl);
echo $o;

echo $output->footer();
