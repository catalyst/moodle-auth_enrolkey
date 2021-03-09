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
 * This contains the auth_enrolkey cohort form.
 *
 * @package    auth_enrolkey
 * @copyright  2021 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_enrolkey\form;

use core\persistent;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * Cohort form.
 *
 * @package    auth_enrolkey
 * @copyright  2021 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrolkey_cohort_form extends \moodleform {

    /**
     * Define the form - called by parent constructor
     */
    public function definition() {
        $mform = $this->_form;

        $cohortlist = $this->_customdata['cohorts'];

        $cohortnames = [];
        foreach ($cohortlist['cohorts'] as $cohort) {
            $cohortnames[$cohort->id] = $cohort->name . ' (' . $cohort->idnumber . ')';
        }
        $options = array(
            'multiple' => true,
            'noselectionstring' => get_string('label_cohortselect_empty', 'auth_enrolkey'),
        );
        $mform->addElement('autocomplete', 'cohortids', get_string('label_cohortselect', 'auth_enrolkey'), $cohortnames, $options);
        $mform->addHelpButton('cohortids', 'label_cohortselect', 'auth_enrolkey');

        $this->add_action_buttons(true);
    }

    /**
     * Helper function to set the form fields.
     *
     * @param persistent[] $currentdata
     */
    public function set_autocomplete_data($currentdata) {
        $list = [];

        foreach ($currentdata as $id => $persistent) {
            $list[] = $id;
        }

        $this->set_data([
            'cohortids' => $list
        ]);
    }
}

