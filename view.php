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
 * Login page hook to notify user of courses enrolled via tokens.
 *
 * @package    auth_token
 * @copyright  2016 Nicholas Hoobin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//defined('MOODLE_INTERNAL') || die;
require_once('../../config.php');

$PAGE->set_url(new moodle_url('/admin/token/view.php'));


$PAGE->set_pagelayout('print');

$PAGE->set_course($SITE);
$PAGE->set_title('title');
$PAGE->set_heading('heading');

echo $OUTPUT->header();
echo $OUTPUT->footer();
