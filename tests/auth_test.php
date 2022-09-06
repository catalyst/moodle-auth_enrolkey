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
 * Enrolkey authentication tests.
 *
 * @package    auth_enrolkey
 * @copyright  2016 Nicholas Hoobin (nicholashoobin@catalyst-au.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_enrolkey;

use auth_enrolkey\persistent\enrolkey_cohort_mapping;
use auth_enrolkey\persistent\enrolkey_profile_mapping;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/auth/enrolkey/auth.php');
require_once($CFG->dirroot . '/cohort/lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');

/**
 * Token Authentication tests.
 * @copyright  2016 Nicholas Hoobin (nicholashoobin@catalyst-au.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_test extends \advanced_testcase {

    /**
     * Test test_auth_enrolkey()
     */
    public function test_auth_enrolkey() {
        global $DB, $CFG;

        $this->resetAfterTest(true);

        $tokenauth = get_auth_plugin('enrolkey');
        $selfenrol = enrol_get_plugin('self');
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $course3 = $this->getDataGenerator()->create_course();
        $course4 = $this->getDataGenerator()->create_course();
        $course5 = $this->getDataGenerator()->create_course();
        $course6 = $this->getDataGenerator()->create_course();
        $course7 = $this->getDataGenerator()->create_course();
        $course8 = $this->getDataGenerator()->create_course();

        // Additional for the test db that exists.
        $this->assertEquals(9, $DB->count_records('course'));

        $context1 = \context_course::instance($course1->id);
        $context2 = \context_course::instance($course2->id);
        $context3 = \context_course::instance($course3->id);
        $context4 = \context_course::instance($course4->id);
        $context5 = \context_course::instance($course5->id);
        $context6 = \context_course::instance($course6->id);
        $context7 = \context_course::instance($course7->id);
        $context8 = \context_course::instance($course8->id);

        $this->assertEquals(8, $DB->count_records('enrol', ['enrol' => 'self']));

        $instance1 = $DB->get_record('enrol', ['courseid' => $course1->id, 'enrol' => 'self'], '*', MUST_EXIST);
        $instance2 = $DB->get_record('enrol', ['courseid' => $course2->id, 'enrol' => 'self'], '*', MUST_EXIST);
        $instance3 = $DB->get_record('enrol', ['courseid' => $course3->id, 'enrol' => 'self'], '*', MUST_EXIST);
        $instance4 = $DB->get_record('enrol', ['courseid' => $course4->id, 'enrol' => 'self'], '*', MUST_EXIST);
        $instance5 = $DB->get_record('enrol', ['courseid' => $course5->id, 'enrol' => 'self'], '*', MUST_EXIST);
        $instance6 = $DB->get_record('enrol', ['courseid' => $course6->id, 'enrol' => 'self'], '*', MUST_EXIST);
        $instance7 = $DB->get_record('enrol', ['courseid' => $course7->id, 'enrol' => 'self'], '*', MUST_EXIST);
        $instance8 = $DB->get_record('enrol', ['courseid' => $course8->id, 'enrol' => 'self'], '*', MUST_EXIST);

        $instance1->password = '';
        $instance2->password = 'key_1';
        $instance3->password = 'key_1';
        $instance4->password = 'key_2';
        $instance5->password = 'key_2';
        $instance6->password = 'key_1';
        $instance7->password = 'key_1';
        $instance8->password = 'key_1';

        $DB->update_record('enrol', $instance1);
        $DB->update_record('enrol', $instance2);
        $DB->update_record('enrol', $instance3);
        $DB->update_record('enrol', $instance4);
        $DB->update_record('enrol', $instance5);
        $DB->update_record('enrol', $instance6);
        $DB->update_record('enrol', $instance7);
        $DB->update_record('enrol', $instance8);

        $selfenrol->update_status($instance1, ENROL_INSTANCE_ENABLED);
        $selfenrol->update_status($instance2, ENROL_INSTANCE_ENABLED);
        $selfenrol->update_status($instance3, ENROL_INSTANCE_ENABLED);
        $selfenrol->update_status($instance4, ENROL_INSTANCE_ENABLED);
        $selfenrol->update_status($instance5, ENROL_INSTANCE_ENABLED);
        $selfenrol->update_status($instance6, ENROL_INSTANCE_ENABLED);
        $selfenrol->update_status($instance7, ENROL_INSTANCE_ENABLED);
        $selfenrol->update_status($instance8, ENROL_INSTANCE_DISABLED);

        $this->assertTrue($selfenrol->can_self_enrol($instance1));
        $this->assertTrue($selfenrol->can_self_enrol($instance2));
        $this->assertTrue($selfenrol->can_self_enrol($instance3));
        $this->assertTrue($selfenrol->can_self_enrol($instance4));
        $this->assertTrue($selfenrol->can_self_enrol($instance5));
        $this->assertTrue($selfenrol->can_self_enrol($instance6));
        $this->assertTrue($selfenrol->can_self_enrol($instance7));
        $this->assertContains('Enrolment is disabled or inactive', [$selfenrol->can_self_enrol($instance8)]);

        $this->assertTrue($DB->record_exists('enrol', ['courseid' => $course1->id, 'enrol' => 'self', 'password' => '']));
        $this->assertTrue($DB->record_exists('enrol', ['courseid' => $course2->id, 'enrol' => 'self', 'password' => 'key_1']));
        $this->assertTrue($DB->record_exists('enrol', ['courseid' => $course3->id, 'enrol' => 'self', 'password' => 'key_1']));
        $this->assertTrue($DB->record_exists('enrol', ['courseid' => $course4->id, 'enrol' => 'self', 'password' => 'key_2']));
        $this->assertTrue($DB->record_exists('enrol', ['courseid' => $course5->id, 'enrol' => 'self', 'password' => 'key_2']));
        $this->assertTrue($DB->record_exists('enrol', ['courseid' => $course6->id, 'enrol' => 'self', 'password' => 'key_1']));
        $this->assertTrue($DB->record_exists('enrol', ['courseid' => $course7->id, 'enrol' => 'self', 'password' => 'key_1']));
        $this->assertTrue($DB->record_exists('enrol', ['courseid' => $course8->id, 'enrol' => 'self', 'password' => 'key_1']));

        // During create_user() it will insert the user into the database, we just want to generate the user object.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();

        $user1->signup_token = 'key_1';
        $user2->signup_token = 'key_2';
        $user3->signup_token = 'key_1';

        // So we will remove the user record from the database. As we want to test $auth->user_signup().
        $user1record = ['username' => $user1->username, 'mnethostid' => $user1->mnethostid];
        $user2record = ['username' => $user2->username, 'mnethostid' => $user2->mnethostid];
        $user3record = ['username' => $user3->username, 'mnethostid' => $user3->mnethostid];

        $DB->delete_records('user', $user1record);
        $DB->delete_records('user', $user2record);
        $DB->delete_records('user', $user3record);

        // Testing that the user no longer exists, this would give a unique key restraint error if you tried it add it.
        $this->assertEquals(0, $DB->record_exists('user', $user1record));
        $this->assertEquals(0, $DB->record_exists('user', $user2record));
        $this->assertEquals(0, $DB->record_exists('user', $user3record));

        // This hack is for email testin in travis.
        $debug = $CFG->debug;
        unset($CFG->debug);

        // Now signing up correctly. No email notification (false).
        $sink = $this->redirectEvents();
        $tokenauth->user_signup($user1, false);
        $tokenauth->user_signup($user2, false);

        // Even though we don't send emails, the 'self' plugin may.
        $sink->close();
        $CFG->debug = $debug;

        // User 1 should be enrolled into course 2 and 3.
        $this->assertFalse(is_enrolled($context1, $user1, ''));
        $this->assertTrue(is_enrolled($context2, $user1, ''));
        $this->assertTrue(is_enrolled($context3, $user1, ''));
        $this->assertFalse(is_enrolled($context4, $user1, ''));

        $this->assertFalse(is_enrolled($context8, $user1, ''));

        // User 2 should be enrolled into course 4.
        $this->assertFalse(is_enrolled($context1, $user2, ''));
        $this->assertFalse(is_enrolled($context2, $user2, ''));
        $this->assertFalse(is_enrolled($context3, $user2, ''));
        $this->assertTrue(is_enrolled($context4, $user2, ''));

        $this->assertFalse(is_enrolled($context8, $user2, ''));
    }

    public function test_group_enrolkey() {
        $this->resetAfterTest(true);
        global $DB, $CFG;

        // Generate users for test.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();

        // Setup course and enrolment.
        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);
        // Create selfenrolment instance.
        $instance = $DB->get_record('enrol', ['courseid' => $course->id, 'enrol' => 'self'], '*', MUST_EXIST);
        $instance->password = 'key';
        // Set customint that controls groupkeys.
        $instance->customint1 = 1;

        // Register instance with plugin.
        $selfenrol = enrol_get_plugin('self');
        $DB->update_record('enrol', $instance);
        $selfenrol->update_status($instance, ENROL_INSTANCE_ENABLED);
        // Check self-enrolment is setup properly.
        $this->setUser($user1);
        $this->assertTrue($selfenrol->can_self_enrol($instance));

        // Create group enrolment.
        $group = $this->getDataGenerator()->create_group(['courseid' => $course->id, 'enrolmentkey' => 'groupkey']);

        // Remove users from database to re-enrol.
        // So we will remove the user record from the database. As we want to test $auth->user_signup().
        $user1record = ['username' => $user1->username, 'mnethostid' => $user1->mnethostid];
        $user2record = ['username' => $user2->username, 'mnethostid' => $user2->mnethostid];
        $user3record = ['username' => $user3->username, 'mnethostid' => $user3->mnethostid];

        $DB->delete_records('user', $user1record);
        $DB->delete_records('user', $user2record);
        $DB->delete_records('user', $user3record);

        // Self signup to course.
        $user1->signup_token = 'key';
        // Self signup to group.
        $user2->signup_token = 'groupkey';
        // Self signup non-valid key.
        $user3->signup_token = 'fakekey';

        // Setup plugin to enrol.
        $tokenauth = get_auth_plugin('enrolkey');

        // Now signing up correctly. No email notification (false).
        $sink = $this->redirectEvents();
        $tokenauth->user_signup($user1, false);
        $this->setUser($user2);
        $tokenauth->user_signup($user2, false);
        $this->setUser($user3);
        $tokenauth->user_signup($user3, false);

        // Even though we don't send emails, the 'self' plugin may.
        $sink->close();

        // Check that $user1 is enrolled in $course but not $group.
        $this->assertTrue(is_enrolled($context, $user1, ''));
        $this->assertFalse(groups_is_member($group->id, $user1->id));

        // Check that $user2 is enrolled in $course and in $group.
        $this->assertTrue(is_enrolled($context, $user2, ''));
        $this->assertTrue(groups_is_member($group->id, $user2->id));

        // Check that $user3 is a valid user, but not enrolled in $course or $group.
        $this->assertTrue($DB->record_exists('user', ['id' => $user3->id]));
        $this->assertFalse(is_enrolled($context, $user3, ''));
        $this->assertFalse(groups_is_member($group->id, $user3->id));
    }

    public function test_add_cohorts_during_signup() {
        $this->resetAfterTest(true);
        global $DB;

        // Setup the cohort data structure.
        $cohort = new \stdClass();
        $cohort->contextid = \context_system::instance()->id;
        $cohort->name = 'test cohort';
        $cohort->idnumber = 'testid';
        $cohort->description = 'test cohort desc';
        $cohort->descriptionformat = FORMAT_HTML;
        $cohortid = cohort_add_cohort($cohort);
        $this->assertNotEmpty($cohortid);

        // Build the persistent to use with cohort mapping.
        $cdata = [
            'enrolid' => 1,
            'cohortid' => $cohortid,
        ];
        $cohortmapping = new enrolkey_cohort_mapping(0, (object) $cdata);
        $cohortmapping->save();
        $this->assertTrue($DB->record_exists('auth_enrolkey_cohort', $cdata));

        $user1 = $this->getDataGenerator()->create_user();

        $availableenrolids = [1];
        enrolkey_cohort_mapping::add_cohorts_during_signup($user1, $availableenrolids);

        $this->assertTrue($DB->record_exists('cohort_members', ['cohortid' => $cohort->id, 'userid' => $user1->id]));
    }

    public function test_add_fields_during_signup() {
        $this->resetAfterTest(true);
        global $DB;

        // Create user info fields.
        if (!$DB->record_exists('user_info_category', [])) {
            // Copied from user/profile/index.php.
            $defaultcategory = new \stdClass();
            $defaultcategory->name = 'Default category';
            $defaultcategory->sortorder = 1;

            $DB->insert_record('user_info_category', $defaultcategory);
        }

        $field = [
            'shortname' => 'test1',
            'name' => 'test field 1',
            'categoryid' => 1,
            'datatype' => 'text',
        ];
        $DB->insert_record('user_info_field', (object) $field);

        // Build the persistent to use with cohort mapping.
        $pdata = [
            'enrolid' => 1,
            'profilefieldname' => 'profile_field_test1',
            'profilefielddata' => 'this is a string',
        ];
        $fieldmapping = new enrolkey_profile_mapping(0, (object) $pdata);
        $fieldmapping->save();

        $select = $DB->sql_compare_text('profilefieldname') . ' = ' . $DB->sql_compare_text(':profilefieldname');
        $select .= ' AND ' . $DB->sql_compare_text('profilefielddata') . ' = ' . $DB->sql_compare_text(':profilefielddata');
        $this->assertTrue($DB->record_exists_select('auth_enrolkey_profile', $select, $pdata));

        $user1 = $this->getDataGenerator()->create_user();
        $user1->profile_field_test1 = 'this data is force overwritten';
        profile_save_data($user1, true);

        $availableenrolids = [1];
        enrolkey_profile_mapping::add_fields_during_signup($user1, $availableenrolids);

        // Obtain the fieldid reference.
        $field = $DB->get_record('user_info_field', ['shortname' => 'test1']);
        $params = [
            'fieldid' => $field->id,
            'userid' => $user1->id,
            'data' => 'this is a string',
        ];

        // Check to see if the data is saved.
        $select = $DB->sql_compare_text('data') . ' = ' . $DB->sql_compare_text(':data');
        $select .= ' AND fieldid = :fieldid';
        $select .= ' AND userid = :userid';
        $this->assertTrue($DB->record_exists_select('user_info_data', $select, $params));
    }
}
