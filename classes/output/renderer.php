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
 * Auth Enrolkey renderer.
 *
 * @package    auth_enrolkey
 * @copyright  2021 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_enrolkey\output;


defined('MOODLE_INTERNAL') || die;

use auth_enrolkey\table\enrolkey_available_table;
// We should extend the legacy renderer to maintain compatability with old style render function.
require_once($CFG->dirroot . '/auth/enrolkey/renderer.php');

/**
 * Auth Enrolkey renederer.
 *
 * @package    auth_enrolkey
 * @copyright  2021 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends \auth_enrolkey_renderer {
    /**
     * Render the HTML for the student quiz table.
     *
     * @param string $baseurl the base url to render the table on.
     * @return string $output HTML for the table.
     */
    public static function render_available_table($baseurl) {
        $renderable = new enrolkey_available_table($baseurl);
        ob_start();
        $renderable->out(50, false);
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }
}
