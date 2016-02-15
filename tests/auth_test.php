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
 * Token Authentication tests.
 *
 * @package    auth_token
 * @copyright  2016 Nicholas Hoobin (nicholashoobin@catalyst-au.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/auth/token/auth.php');

/**
 * Token Authentication tests.
 * @copyright  2016 Nicholas Hoobin (nicholashoobin@catalyst-au.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_token_auth_testcase extends advanced_testcase {

    /**
     * Test test_auth_token()
     */
    public function test_auth_token() {
        global $DB;

        $this->resetAfterTest(true);

        $tokenauth = get_auth_plugin('token');
        $selfenrol = enrol_get_plugin('self');

        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $course3 = $this->getDataGenerator()->create_course();
        $course4 = $this->getDataGenerator()->create_course();

        $this->assertEquals(5, $DB->count_records('course'));

        $context1 = context_course::instance($course1->id);
        $context2 = context_course::instance($course2->id);
        $context3 = context_course::instance($course3->id);
        $context4 = context_course::instance($course4->id);

        $this->assertEquals(4, $DB->count_records('enrol', array('enrol' => 'self')));

        $instance1 = $DB->get_record('enrol', array('courseid' => $course1->id, 'enrol' => 'self'), '*', MUST_EXIST);
        $instance2 = $DB->get_record('enrol', array('courseid' => $course2->id, 'enrol' => 'self'), '*', MUST_EXIST);
        $instance3 = $DB->get_record('enrol', array('courseid' => $course3->id, 'enrol' => 'self'), '*', MUST_EXIST);
        $instance4 = $DB->get_record('enrol', array('courseid' => $course4->id, 'enrol' => 'self'), '*', MUST_EXIST);

        $instance1->password = '';
        $instance2->password = 'key_1';
        $instance3->password = 'key_1';
        $instance4->password = 'key_2';

        $DB->update_record('enrol', $instance1);
        $DB->update_record('enrol', $instance2);
        $DB->update_record('enrol', $instance3);
        $DB->update_record('enrol', $instance4);

        $selfenrol->update_status($instance1, ENROL_INSTANCE_ENABLED);
        $selfenrol->update_status($instance2, ENROL_INSTANCE_ENABLED);
        $selfenrol->update_status($instance3, ENROL_INSTANCE_ENABLED);
        $selfenrol->update_status($instance4, ENROL_INSTANCE_ENABLED);

        $this->assertTrue($DB->record_exists('enrol', array('courseid' => $course1->id, 'enrol' => 'self', 'password' => '')));
        $this->assertTrue($DB->record_exists('enrol', array('courseid' => $course2->id, 'enrol' => 'self', 'password' => 'key_1')));
        $this->assertTrue($DB->record_exists('enrol', array('courseid' => $course3->id, 'enrol' => 'self', 'password' => 'key_1')));
        $this->assertTrue($DB->record_exists('enrol', array('courseid' => $course4->id, 'enrol' => 'self', 'password' => 'key_2')));

        // During create_user() it will insert the user into the database, we just want to generate the user object.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $user1->signup_token = 'key_1';
        $user2->signup_token = 'key_2';

        // So we will remove the user record from the database. As we want to test $auth->user_signup().
        $user1record = array('username' => $user1->username, 'mnethostid' => $user1->mnethostid);
        $user2record = array('username' => $user2->username, 'mnethostid' => $user2->mnethostid);

        $DB->delete_records('user', $user1record);
        $DB->delete_records('user', $user2record);

        // Testing that the user no longer exists, this would give a unique key restraint error if you tried it add it.
        $this->assertEquals(0, $DB->record_exists('user', $user1record));
        $this->assertEquals(0, $DB->record_exists('user', $user2record));

        // Now signing up correctly. No email notification (false).
        $tokenauth->user_signup($user1, false);
        $tokenauth->user_signup($user2, false);

        // User 1 should be enrolled into course 2 and 3.
        $this->assertFalse(is_enrolled($context1, $user1, '', false));
        $this->assertTrue(is_enrolled($context2, $user1, '', false));
        $this->assertTrue(is_enrolled($context3, $user1, '', false));
        $this->assertFalse(is_enrolled($context4, $user1, '', false));

        // User 2 should be enrolled into course 4.
        $this->assertFalse(is_enrolled($context1, $user2, '', false));
        $this->assertFalse(is_enrolled($context2, $user2, '', false));
        $this->assertFalse(is_enrolled($context3, $user2, '', false));
        $this->assertTrue(is_enrolled($context4, $user2, '', false));
    }
}