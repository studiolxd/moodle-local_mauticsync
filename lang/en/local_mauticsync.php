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
 * English language pack for Mautic sync
 *
 * @package    local_mauticsync
 * @category   string
 * @copyright  2025 Studio LXD <hello@studiolxd.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Mautic sync';

$string['signup_optin_label'] = 'I want to receive email marketing communications (I can unsubscribe anytime).';

$string['mautic_baseurl'] = 'Mautic Base URL';
$string['mautic_baseurl_desc'] = 'Example: https://marketing.studiolxd.com (no trailing slash).';

$string['mautic_user'] = 'Mautic API user';
$string['mautic_user_desc'] = 'Username for Basic Auth.';

$string['mautic_pass'] = 'Mautic API password';
$string['mautic_pass_desc'] = 'Password for Basic Auth.';

$string['marketing_segment_id'] = 'Marketing segment';
$string['marketing_segment_id_desc'] = 'Mautic segment to add the contact to when consent is checked (e.g., "Newsletter").';

$string['choose'] = '-- Select --';
$string['save_credentials_first'] = 'Please save the Mautic URL, API user, and API password first, then reload this page to select the marketing segment.';
$string['api_connection_error'] = 'Mautic API connection error';
$string['api_connection_error_desc'] = 'Could not connect to the Mautic API: {$a}. Please check your credentials and try again.';
$string['mautic_api_error'] = 'An error occurred while communicating with the Mautic API.';

$string['privacy:metadata'] = 'The Mautic sync plugin sends user data to an external Mautic CRM instance for marketing purposes.';
$string['privacy:metadata:email'] = 'The user\'s email address is sent to Mautic to create or identify a contact.';
$string['privacy:metadata:ip'] = 'The user\'s IP address is sent to Mautic for GDPR consent record keeping.';