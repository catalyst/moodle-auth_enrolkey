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
 * Renderable table for listing available enrolkeys.
 *
 * @package    auth_enrolkey
 * @copyright  2021 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_enrolkey\table;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

use auth_enrolkey\persistent\enrolkey_cohort_mapping;
use auth_enrolkey\persistent\enrolkey_profile_mapping;
use auth_enrolkey\persistent\enrolkey_redirect_mapping;
use moodle_url;
use \table_sql;
use \renderable;

/**
 * Renderable table for quiz dashboard users.
 *
 * @package    auth_enrolkey
 * @copyright  2021 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrolkey_available_table extends table_sql implements renderable {

    /**
     * report_table constructor.
     *
     * @param string $baseurl Base URL of the page that contains the table.
     *
     * @throws \coding_exception
     */
    public function __construct(string $baseurl) {
        parent::__construct('local_enrolkey_available_table');

        $this->set_attribute('id', 'local_enrolkey_available_table');
        $this->set_attribute('class', 'generaltable generalbox');
        $this->downloadable = false;
        $this->define_baseurl($baseurl);
        $this->allcohorts = cohort_get_all_cohorts(0, 5000, '');

        $fields = "en.id,
                   en.password,
                   en.name as enrolkeyname,
                   en.courseid,
                   en.timecreated,
                   en.timemodified,
                   cc.fullname AS coursefullname,
                   cc.shortname,
                   cc.id AS courseid";
        $from = '{enrol} en
                 LEFT JOIN {course} cc ON en.courseid = cc.id';

        $where = '1 = 1';
        $where .= " AND en.password != ''
                    AND en.password IS NOT NULL ";

        $this->set_sql($fields, $from, $where, array());

        // Define the headers and columns.
        $headers = [];
        $columns = [];

        $headers[] = get_string('th_enrolkeyname', 'auth_enrolkey');
        $columns[] = 'enrolkeyname';

        $headers[] = get_string('th_fullname', 'auth_enrolkey');
        $columns[] = 'coursefullname';

        $headers[] = get_string('th_cohorts', 'auth_enrolkey');
        $columns[] = 'cohorts';

        $headers[] = get_string('th_profilefields', 'auth_enrolkey');
        $columns[] = 'profilefields';

        $headers[] = get_string('th_redirecturl', 'auth_enrolkey');
        $columns[] = 'redirecturl';

        $this->define_columns($columns);
        $this->define_headers($headers);

        // Setup pagination.
        $this->pageable(false);
        $this->sortable(false);
        $this->collapsible(false);
        $this->column_nosort = array('actions');
    }

    /**
     * Get content for cohorts column.
     * Displays a summary of the rules.
     *
     * @param \stdClass $row
     * @return string html used to display the field.
     */
    public function col_cohorts($row) {
        global $OUTPUT;

        $content = '';

        $cohortlist = enrolkey_cohort_mapping::get_records_by_enrolid($row->id);

        foreach ($cohortlist as $cohortid => $persistent) {
            if (array_key_exists($cohortid, $this->allcohorts['cohorts'])) {
                $cohort = $this->allcohorts['cohorts'][$cohortid];
                $cohortname = $cohort->name . ' (' . $cohort->idnumber . ')';
                $url = $persistent->get_moodle_url();
                $link = \html_writer::link($url, $cohortname);
                $content .= \html_writer::div($link);
            }
        }

        $icon = $OUTPUT->render(new \pix_icon('i/edit', ''))
            . get_string('edit_cohort', 'auth_enrolkey')
            . \html_writer::empty_tag('br');
        $url = new moodle_url('edit_cohort.php', ['id' => $row->id]);
        $content .= \html_writer::link($url, $icon, array(
            'class' => 'action-icon edit',
            'id' => 'admin-enrolkey-edit-' . $row->id,
            'data-toggle' => 'tooltip',
            'data-placement' => 'top',
            'title' => 'Edit'
        ));

        return $content;
    }

    /**
     * Get content for profilefields column.
     * Displays a summary of the rules.
     *
     * @param \stdClass $row
     * @return string html used to display the field.
     */
    public function col_profilefields($row) {
        global $OUTPUT;

        $content = '';

        $persistents = enrolkey_profile_mapping::get_records_by_enrolid($row->id);
        $fields = [];

        foreach ($persistents as $persistent) {
            $fields[] = [
                'name' => $persistent->get_readable_name(),
                'value' => $persistent->get_readable_value()
            ];
        }

        $context = [
            'data' => [
                'fields' => $fields
            ]
        ];

        $content .= $OUTPUT->render_from_template('auth_enrolkey/enrolkey_profiletable', $context);

        $icon = $OUTPUT->render(new \pix_icon('i/edit', ''))
            . get_string('edit_profile', 'auth_enrolkey')
            . \html_writer::empty_tag('br');
        $url = new moodle_url('edit_profile.php', ['id' => $row->id]);
        $content .= \html_writer::link($url, $icon, array(
            'class' => 'action-icon edit',
            'id' => 'admin-enrolkey-profile-' . $row->id,
            'data-toggle' => 'tooltip',
            'data-placement' => 'top',
            'title' => 'Profile'
        ));

        return $content;
    }

    /**
     * Get content for redirecturl column.
     * Displays a summary of the rules.
     *
     * @param \stdClass $row
     * @return string html used to display the field.
     */
    public function col_redirecturl($row) {
        global $OUTPUT;

        $content = '';

        $persistent = enrolkey_redirect_mapping::get_record_by_enrolid($row->id);
        $url = $persistent->get_moodle_url();
        if ($url != '') {
            $content .= \html_writer::link($url, $url)
                . \html_writer::empty_tag('br');
        }

        $icon = $OUTPUT->render(new \pix_icon('i/edit', ''))
            . get_string('edit_redirect', 'auth_enrolkey')
            . \html_writer::empty_tag('br');
        $editurl = new moodle_url('edit_redirect.php', ['id' => $row->id]);
        $content .= \html_writer::link($editurl, $icon, array(
            'class' => 'action-icon edit',
            'id' => 'admin-enrolkey-redirect-' . $row->id,
            'data-toggle' => 'tooltip',
            'data-placement' => 'top',
            'title' => 'Redirect'
        ));

        return $content;
    }

    /**
     * Get content for enrolkeyname column.
     *
     * @param \stdClass $row
     * @return string html used to display the field.
     */
    public function col_enrolkeyname($row) {
        $param = [
            'courseid' => $row->courseid,
            'id' => $row->id,
            'type' => 'self',
        ];
        $url = new moodle_url('/enrol/editinstance.php', $param);

        return  \html_writer::link($url, $row->enrolkeyname);
    }

    /**
     * Get content for coursefullname column.
     *
     * @param \stdClass $row
     * @return string html used to display the field.
     */
    public function col_coursefullname($row) {
        $param = [
            'id' => $row->courseid,
        ];
        $url = new moodle_url('/course/view.php', $param);

        return  \html_writer::link($url, $row->coursefullname);
    }

}
