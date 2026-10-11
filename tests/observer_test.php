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

namespace local_mauticsync;

/**
 * Unit tests for the event observer.
 *
 * @package    local_mauticsync
 * @copyright  2025 Studio LXD <hello@studiolxd.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_mauticsync\observer
 */
final class observer_test extends \advanced_testcase {

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Test that observer does nothing when no session opt-in data exists.
     */
    public function test_user_created_without_session_data(): void {
        global $SESSION;

        unset($SESSION->mauticsync_signup_optin);

        $user = $this->getDataGenerator()->create_user();

        // If we get here without error, the observer correctly returned early.
        $this->assertFalse(isset($SESSION->mauticsync_signup_optin));
    }

    /**
     * Test that observer does nothing when opt-in is disabled.
     */
    public function test_user_created_with_optin_disabled(): void {
        global $SESSION;

        $SESSION->mauticsync_signup_optin = [
            'enabled' => false,
            'email'   => 'test@example.com',
            'time'    => time(),
        ];

        $user = $this->getDataGenerator()->create_user(['email' => 'test@example.com']);

        // Session data should still be there since observer returned early.
        $this->assertFalse($SESSION->mauticsync_signup_optin['enabled']);
    }

    /**
     * Test that observer does nothing when email does not match.
     */
    public function test_user_created_with_email_mismatch(): void {
        global $SESSION;

        $SESSION->mauticsync_signup_optin = [
            'enabled' => true,
            'email'   => 'other@example.com',
            'time'    => time(),
        ];

        $user = $this->getDataGenerator()->create_user(['email' => 'test@example.com']);

        // Session data should still be there since emails didn't match.
        $this->assertTrue(isset($SESSION->mauticsync_signup_optin));
    }

    /**
     * Test that observer rejects stale session data.
     */
    public function test_user_created_with_stale_session_data(): void {
        global $SESSION;

        $SESSION->mauticsync_signup_optin = [
            'enabled' => true,
            'email'   => 'test@example.com',
            'time'    => time() - 600, // 10 minutes ago, exceeds 5-minute limit.
        ];

        $user = $this->getDataGenerator()->create_user(['email' => 'test@example.com']);

        // Session data should be cleaned up due to staleness.
        $this->assertFalse(isset($SESSION->mauticsync_signup_optin));
    }

    /**
     * Test that observer consumes session data and handles missing Mautic config gracefully.
     *
     * Without Mautic configuration, the mautic_client constructor will throw
     * a coding_exception. The observer catches all Throwables and logs them,
     * so user creation should not be blocked.
     */
    public function test_user_created_with_valid_optin_clears_session(): void {
        global $SESSION;

        $email = 'marketing@example.com';

        $SESSION->mauticsync_signup_optin = [
            'enabled' => true,
            'email'   => $email,
            'time'    => time(),
        ];

        // No Mautic config set, so mautic_client will throw.
        // The observer should catch it and still clear the session.
        $user = $this->getDataGenerator()->create_user(['email' => $email]);

        // Session data should be consumed regardless of API failure.
        $this->assertFalse(isset($SESSION->mauticsync_signup_optin));
    }
}
