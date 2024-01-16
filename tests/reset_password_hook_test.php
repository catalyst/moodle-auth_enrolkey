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


namespace auth_enrolkey;

use advanced_testcase;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/auth/enrolkey/lib.php');

/**
 * Enrolkey password reset hook tests.
 *
 * @package    auth_enrolkey
 * @copyright  2023 Catalyst IT
 * @author     Matthew Hilton <matthewhilton@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class reset_password_hook_test extends advanced_testcase {
    /** @var object test user **/
    private $user;

    /**
     * Sets up tests
     */
    public function setUp(): void {
        global $DB;
        $this->resetAfterTest(true);

        // Create a user who signs into the site with enrolkey.
        $enrolkey = get_auth_plugin('enrolkey');

        // Generate a user, but then delete them so we can use the enrolkey to sign them up.
        $user = $this->getDataGenerator()->create_user();
        $user->signup_token = 'token1';

        $DB->delete_records('user', ['id' => $user->id]);
        $enrolkey->user_signup($user, false);
        $user = $DB->get_record('user', ['username' => $user->username]);
        $DB->update_record('user', ['id' => $user->id, 'auth' => 'enrolkey']);

        // Suspend them and re-fetch the record.
        $DB->update_record('user', ['id' => $user->id, 'suspended' => 1]);
        $user = \core_user::get_user($user->id);

        $this->user = $user;
    }

    /**
     * Tears down tests
     */
    public function tearDown(): void {
        delete_user($this->user);
        $this->user = null;
        parent::tearDown();
    }

    /**
     * Tests auth_enrolkey_post_forgot_password_requests function
     */
    public function test_auth_enrolkey_post_forgot_password_requests() {
        global $DB;

        $sink = $this->redirectEmails();
        $formdata = ['username' => $this->user->username];

        // Initially the config to allow suspended enrolkey
        // users to get password reset emails is disabled.
        // So if we call this, we expect it to fail / do nothing.
        set_config('unsuspendaccounts', 0, 'auth_enrolkey');
        auth_enrolkey_post_forgot_password_requests($formdata);
        $this->assertCount(0, $sink->get_messages());

        // Confirm the user is still suspended.
        $this->assertEquals(1, $DB->get_field('user', 'suspended', ['id' => $this->user->id]));

        // But if the config is enabled, they should successfully receive an email.
        set_config('unsuspendaccounts', 1, 'auth_enrolkey');
        auth_enrolkey_post_forgot_password_requests($formdata);
        $this->assertCount(1, $sink->get_messages());

        // Confirm the user is still suspended.
        // The user is still required to use unsuspend.php to unsuspend themselves.
        // This only gives them their password back.
        $this->assertEquals(1, $DB->get_field('user', 'suspended', ['id' => $this->user->id]));
    }

    /**
     * Tests auth_enrolkey_post_set_password_requests function
     */
    public function test_auth_enrolkey_post_set_password_requests() {
        global $USER;

        $randomuser = $this->getDataGenerator()->create_user();

        // For non-enrolkey users, this does nothing.
        $this->setUser($randomuser);

        // Confirm user is logged in.
        $this->assertTrue(!empty($USER->id));

        auth_enrolkey_post_set_password_requests([]);

        // Confirm user is still logged in.
        $this->assertTrue(!empty($USER->id));

        // Now set as enrolkey user, this will log them out.
        $this->setUser($this->user);

        // Confirm user is logged in.
        $this->assertTrue(!empty($USER->id));

        auth_enrolkey_post_set_password_requests([]);

        // Confirm user is now logged out.
        $this->assertTrue(empty($USER->id));
    }
}
