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
 * Event observer for user creation.
 *
 * Listens for user_created events and syncs the new user to Mautic
 * if they opted in to marketing during signup.
 *
 * @package    local_mauticsync
 * @copyright  2025 Studio LXD <hello@studiolxd.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {

    /** @var int Maximum age in seconds for session opt-in data to be considered valid. */
    private const OPTIN_MAX_AGE = 300;

    /**
     * Handle the user_created event.
     *
     * Checks for a marketing opt-in flag stored in the session by
     * local_mauticsync_post_signup_requests(), verifies the email matches,
     * and subscribes the contact in Mautic.
     *
     * @param \core\event\user_created $event The user creation event.
     */
    public static function user_created(\core\event\user_created $event): void {
        global $DB, $SESSION;

        // Only proceed if we came from signup with our session flag set.
        $opt = $SESSION->mauticsync_signup_optin ?? null;
        if (empty($opt['enabled']) || empty($opt['email'])) {
            return;
        }

        // Reject stale session data (e.g. leftover from a previous failed attempt).
        if (isset($opt['time']) && (time() - $opt['time']) > self::OPTIN_MAX_AGE) {
            unset($SESSION->mauticsync_signup_optin);
            return;
        }

        // Verify the email matches the newly created user.
        $user = $DB->get_record('user', ['id' => $event->objectid], 'id,email', MUST_EXIST);
        $useremail = strtolower(trim((string)$user->email));

        if ($useremail !== $opt['email']) {
            return;
        }

        // Consume the session data to avoid affecting other user creations.
        unset($SESSION->mauticsync_signup_optin);

        // Call Mautic API. Never block user registration on failure.
        try {
            $client = new mautic_client();
            $client->subscribe_marketing($useremail, [
                'ip'     => self::get_ip(),
                'source' => 'moodle: signup',
                'at'     => gmdate('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            debugging('Mautic sync failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }

    /**
     * Get the remote IP address of the current user.
     *
     * Uses Moodle's getremoteaddr() which respects proxy configuration.
     *
     * @return string The IP address, or 'unknown' if unavailable.
     */
    private static function get_ip(): string {
        return function_exists('getremoteaddr') ? (string)getremoteaddr() : 'unknown';
    }
}
