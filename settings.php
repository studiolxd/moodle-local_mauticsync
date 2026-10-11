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
 * Admin settings for the local_mauticsync plugin.
 *
 * @package    local_mauticsync
 * @copyright  2025 Studio LXD <hello@studiolxd.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_mauticsync', get_string('pluginname', 'local_mauticsync'));

    $settings->add(new admin_setting_configtext(
        'local_mauticsync/mautic_baseurl',
        get_string('mautic_baseurl', 'local_mauticsync'),
        get_string('mautic_baseurl_desc', 'local_mauticsync'),
        '',
        PARAM_URL
    ));

    $settings->add(new admin_setting_configtext(
        'local_mauticsync/mautic_user',
        get_string('mautic_user', 'local_mauticsync'),
        get_string('mautic_user_desc', 'local_mauticsync'),
        '',
        PARAM_RAW_TRIMMED
    ));

    $settings->add(new admin_setting_configpasswordunmask(
        'local_mauticsync/mautic_pass',
        get_string('mautic_pass', 'local_mauticsync'),
        get_string('mautic_pass_desc', 'local_mauticsync'),
        ''
    ));

    // Dynamic select: marketing segment from Mautic API.
    $baseurl = get_config('local_mauticsync', 'mautic_baseurl');
    $user    = get_config('local_mauticsync', 'mautic_user');
    $pass    = get_config('local_mauticsync', 'mautic_pass');

    if ($baseurl && $user && $pass) {
        try {
            $client = new \local_mauticsync\mautic_client();

            $segments = $client->get_segments();
            $segmentchoices = ['' => get_string('choose', 'local_mauticsync')];
            foreach ($segments as $segment) {
                $id = $segment['id'] ?? '';
                $name = $segment['name'] ?? '';
                if ($id !== '' && $name !== '') {
                    $segmentchoices[$id] = $name;
                }
            }

            $settings->add(new admin_setting_configselect(
                'local_mauticsync/marketing_segment_id',
                get_string('marketing_segment_id', 'local_mauticsync'),
                get_string('marketing_segment_id_desc', 'local_mauticsync'),
                '',
                $segmentchoices
            ));
        } catch (\Throwable $e) {
            $settings->add(new admin_setting_description(
                'local_mauticsync/api_error',
                get_string('api_connection_error', 'local_mauticsync'),
                get_string('api_connection_error_desc', 'local_mauticsync', $e->getMessage())
            ));
        }
    } else {
        $settings->add(new admin_setting_description(
            'local_mauticsync/save_credentials_first',
            '',
            get_string('save_credentials_first', 'local_mauticsync')
        ));
    }

    $ADMIN->add('localplugins', $settings);
}
