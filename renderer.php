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
 * Enrolment key renderer
 *
 * @package    auth_enrolkey
 * @copyright  2018 Darko Miletic (darko.miletic@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Class auth_enrolkey_renderer
 *
 * @package    auth_enrolkey
 * @copyright  2018 Darko Miletic (darko.miletic@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_enrolkey_renderer extends plugin_renderer_base {

    /**
     * @param  login_signup_form $form
     * @return bool|string
     * @throws moodle_exception
     */
    public function render_enrolkey_signup_form($form) {
        $context = $form->export_for_template($this);

        return $this->render_from_template('core/signup_form_layout', $context);
    }

}