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
 * Callback implementations for Mautic sync.
 *
 * @package    local_mauticsync
 * @copyright  2025 Studio LXD <hello@studiolxd.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Inject an optional marketing opt-in checkbox into the signup form.
 *
 * This is a Moodle callback invoked by the signup form rendering process.
 *
 * @param MoodleQuickForm $mform The signup form instance.
 */
function local_mauticsync_extend_signup_form($mform) {
    $mform->addElement('advcheckbox', 'mauticsync_marketing_optin', '', get_string('signup_optin_label', 'local_mauticsync'));
    $mform->setType('mauticsync_marketing_optin', PARAM_BOOL);
    $mform->setDefault('mauticsync_marketing_optin', 0);
}

/**
 * Capture the opt-in value after successful form validation.
 *
 * Called by core_login_post_signup_requests() only after all validation
 * passes, just before user_signup(). This is more reliable than
 * validate_extend_signup_form which fires on every validation attempt.
 *
 * @param stdClass $data The form data object from get_data().
 */
function local_mauticsync_post_signup_requests($data) {
    global $SESSION;

    if (!empty($data->mauticsync_marketing_optin)) {
        $SESSION->mauticsync_signup_optin = [
            'enabled' => true,
            'email'   => strtolower(trim($data->email ?? '')),
            'time'    => time(),
        ];
    }
}
