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
 * Tests for the helper class and for behaviour that changed between Moodle versions.
 *
 * @package    auth_enrolkey
 * @copyright  2026 FINDER Akademie
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_enrolkey;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/auth/enrolkey/auth.php');

/**
 * Tests for {@see \auth_enrolkey\utility}.
 *
 * @package    auth_enrolkey
 * @copyright  2026 FINDER Akademie
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class utility_test extends \advanced_testcase {
    /**
     * Returns the self enrolment instance of a course, with the given enrolment key.
     *
     * @param \stdClass $course The course.
     * @param string $key The enrolment key, may be empty.
     * @return \stdClass The enrolment instance.
     */
    private function set_self_enrol_key(\stdClass $course, string $key): \stdClass {
        global $DB;

        $instance = $DB->get_record('enrol', ['courseid' => $course->id, 'enrol' => 'self'], '*', MUST_EXIST);
        $instance->password = $key;
        $instance->status = ENROL_INSTANCE_ENABLED;
        $DB->update_record('enrol', $instance);
        return $instance;
    }

    /**
     * Creating a user must work on every supported Moodle version without a deprecation notice.
     *
     * user_create_user() is deprecated in Moodle 5.3, which makes debugging() print or, in tests, fail.
     *
     * @covers \auth_enrolkey\utility::create_user
     */
    public function test_create_user(): void {
        global $CFG, $DB;

        $this->resetAfterTest();

        $user = (object) [
            'username' => 'enrolkeycreate',
            'password' => hash_internal_user_password('Passw0rd!Passw0rd!'),
            'firstname' => 'Enrol',
            'lastname' => 'Key',
            'email' => 'enrolkeycreate@example.com',
            'auth' => 'enrolkey',
            'confirmed' => 1,
            'mnethostid' => $CFG->mnet_localhost_id,
        ];
        $id = utility::create_user($user, false, false);

        $this->assertDebuggingNotCalled();
        $this->assertGreaterThan(0, $id);
        $this->assertEquals('enrolkeycreate', $DB->get_field('user', 'username', ['id' => $id]));
    }

    /**
     * Unsuspending a user must work on every supported Moodle version without a deprecation notice.
     *
     * @covers \auth_enrolkey\utility::unsuspend_user
     * @covers \auth_enrolkey\utility::update_user_record
     */
    public function test_unsuspend_user(): void {
        global $DB;

        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user(['auth' => 'enrolkey', 'suspended' => 1]);
        $this->assertEquals(1, $DB->get_field('user', 'suspended', ['id' => $user->id]));

        $this->assertTrue(utility::unsuspend_user($user));

        $this->assertDebuggingNotCalled();
        $this->assertEquals(0, $DB->get_field('user', 'suspended', ['id' => $user->id]));
        // A user that is not suspended is left alone.
        $this->assertFalse(utility::unsuspend_user($user));
    }

    /**
     * An empty enrolment key must never match a self enrolment instance.
     *
     * Otherwise every course that has a self enrolment instance without a key would match.
     *
     * @covers \auth_enrolkey\utility::get_enrol_plugins
     */
    public function test_get_enrol_plugins_ignores_empty_key(): void {
        global $DB;

        $this->resetAfterTest();

        $nokey = $this->getDataGenerator()->create_course();
        $withkey = $this->getDataGenerator()->create_course();
        $this->set_self_enrol_key($nokey, '');
        $instance = $this->set_self_enrol_key($withkey, 'key_1');

        $this->assertSame([], utility::get_enrol_plugins($DB, ''));
        $this->assertSame([], utility::get_enrol_plugins($DB, '   '));

        $found = utility::get_enrol_plugins($DB, 'key_1');
        $this->assertCount(1, $found);
        $this->assertEquals($instance->id, reset($found)->id);
    }

    /**
     * Enrolling with an empty key enrols the user nowhere.
     *
     * @covers \auth_plugin_enrolkey::enrol_user
     */
    public function test_enrol_user_with_empty_key(): void {
        global $DB;

        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $nokey = $this->getDataGenerator()->create_course();
        $instance = $this->set_self_enrol_key($nokey, '');

        [$enrolids, $errors] = get_auth_plugin('enrolkey')->enrol_user('', false);

        $this->assertSame([], $enrolids);
        $this->assertSame([], $errors);
        $this->assertFalse($DB->record_exists('user_enrolments', ['userid' => $user->id, 'enrolid' => $instance->id]));
    }

    /**
     * The profile field form must be buildable on every supported Moodle version.
     *
     * Moodle 5.x removed legacy user fields such as url, icq and skype. Asking core for their type throws.
     *
     * @covers \auth_enrolkey\form\enrolkey_profile_form::definition
     */
    public function test_profile_form_definition(): void {
        global $CFG;

        $this->resetAfterTest();
        $this->setAdminUser();
        require_once($CFG->dirroot . '/user/profile/lib.php');

        $form = new \auth_enrolkey\form\enrolkey_profile_form('/auth/enrolkey/edit_profile.php', ['currentdata' => []]);
        $html = $form->render();

        $this->assertNotFalse(strpos($html, 'name="idnumber"'));
        $this->assertNotFalse(strpos($html, 'name="institution"'));

        // A legacy field is offered if, and only if, core still knows it.
        foreach (['url', 'icq', 'skype', 'aim', 'yahoo', 'msn'] as $field) {
            try {
                \core_user::get_property_type($field);
                $known = true;
            } catch (\coding_exception $e) {
                $known = false;
            }
            $shown = strpos($html, 'name="' . $field . '"') !== false;
            $this->assertSame($known, $shown, "Field $field: known to core = " . var_export($known, true));
        }
    }

    /**
     * A signup key that only consists of whitespace must not pass the signup validation.
     *
     * Databases with collations that pad with spaces compare ' ' equal to '', which matches every self
     * enrolment instance that has no key. The key is required by default, so this must be rejected.
     *
     * @covers \auth_enrolkey\form\enrolkey_signup_form::validation
     */
    public function test_signup_form_rejects_whitespace_key(): void {
        $this->resetAfterTest();
        set_config('registerauth', 'enrolkey');
        set_config('tokenrequired', 1, 'auth_enrolkey');
        set_config('recaptcha', 0, 'auth_enrolkey');

        $nokey = $this->getDataGenerator()->create_course();
        $this->set_self_enrol_key($nokey, '');

        $form = new \auth_enrolkey\form\enrolkey_signup_form(null, null, 'post', '', ['autocomplete' => 'on']);
        $data = [
            'username' => 'whitespacekey',
            'password' => 'Passw0rd!Passw0rd!',
            'email' => 'whitespacekey@example.com',
            'email2' => 'whitespacekey@example.com',
            'firstname' => 'White',
            'lastname' => 'Space',
            'city' => 'Berlin',
            'country' => 'DE',
        ];

        foreach (['', ' ', "  \t "] as $token) {
            $errors = $form->validation($data + ['signup_token' => $token], []);
            $this->assertArrayHasKey('signup_token', $errors, 'Key [' . $token . '] must be rejected');
        }

        // If the key is not required, a whitespace only key is simply treated as no key.
        set_config('tokenrequired', 0, 'auth_enrolkey');
        $errors = $form->validation($data + ['signup_token' => ' '], []);
        $this->assertArrayNotHasKey('signup_token', $errors);
    }
}
