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
 * HTTP client for the Mautic CRM API.
 *
 * Handles authentication and API calls to create contacts and
 * add them to segments via Mautic's REST API using Basic Auth.
 *
 * @package    local_mauticsync
 * @copyright  2025 Studio LXD <hello@studiolxd.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mautic_client {

    /** @var string Mautic instance base URL. */
    private string $baseurl;

    /** @var string API username for Basic Auth. */
    private string $user;

    /** @var string API password for Basic Auth. */
    private string $pass;

    /** @var int Mautic segment ID for marketing contacts. */
    private int $marketingsegmentid;

    /**
     * Constructor. Reads plugin configuration and validates it.
     *
     * @throws \coding_exception If any required configuration value is missing.
     */
    public function __construct() {
        $this->baseurl = rtrim((string)get_config('local_mauticsync', 'mautic_baseurl'), '/');
        $this->user    = (string)get_config('local_mauticsync', 'mautic_user');
        $this->pass    = (string)get_config('local_mauticsync', 'mautic_pass');
        $this->marketingsegmentid = (int)get_config('local_mauticsync', 'marketing_segment_id');

        if (!$this->baseurl || !$this->user || !$this->pass) {
            throw new \coding_exception('Mautic configuration is incomplete (local_mauticsync).');
        }
    }

    /**
     * Subscribe an email address to the configured marketing segment.
     *
     * Finds or creates a contact in Mautic, then adds it to the segment.
     *
     * @param string $email The email address to subscribe.
     * @param array  $gdpr  GDPR consent metadata with keys 'ip', 'source', 'at'.
     * @throws \core\exception\invalid_parameter_exception If email is empty.
     * @throws \moodle_exception If the Mautic API call fails.
     */
    public function subscribe_marketing(string $email, array $gdpr): void {
        if ($this->marketingsegmentid <= 0) {
            throw new \coding_exception('Marketing segment ID is not configured (local_mauticsync).');
        }

        $email = strtolower(trim($email));
        if ($email === '') {
            throw new \core\exception\invalid_parameter_exception('Empty email');
        }

        $contactid = $this->find_contact_id_by_email($email);

        if (!$contactid) {
            $contactid = $this->create_contact($email, $gdpr);
        }

        $this->add_to_segment($contactid, $this->marketingsegmentid);
    }

    /**
     * Create a pre-configured curl instance with Basic Auth headers.
     *
     * @return \curl A curl instance with authentication headers set.
     */
    private function curl(): \curl {
        $curl = new \curl();
        $curl->setHeader('Authorization: Basic ' . base64_encode($this->user . ':' . $this->pass));
        $curl->setHeader('Accept: application/json');
        return $curl;
    }

    /**
     * Get all segments from the Mautic account.
     *
     * @return array List of segment objects with 'id' and 'name' keys.
     * @throws \moodle_exception If the API call fails.
     */
    public function get_segments(): array {
        $curl = $this->curl();
        $resp = $curl->get($this->baseurl . '/api/segments?limit=100');
        $code = (int) ($curl->get_info()['http_code'] ?? 0);

        if ($code >= 400) {
            throw new \moodle_exception('mautic_api_error', 'local_mauticsync', '', null,
                'GET segments failed: HTTP ' . $code . ' :: ' . $resp);
        }

        $json = json_decode($resp, true) ?: [];
        return $json['lists'] ?? [];
    }

    /**
     * Search for an existing Mautic contact by email address.
     *
     * @param string $email The email address to search for.
     * @return int|null The Mautic contact ID, or null if not found.
     */
    private function find_contact_id_by_email(string $email): ?int {
        $curl = $this->curl();
        // Use minimal=0 to verify exact email match when Mautic returns multiple results.
        $url = $this->baseurl . '/api/contacts?search=' . rawurlencode('email:' . $email) . '&minimal=0';

        $resp = $curl->get($url);
        if ($curl->get_info()['http_code'] >= 400) {
            debugging('Mautic find contact by email failed: HTTP '
                . $curl->get_info()['http_code'] . ' for email ' . $email, DEBUG_DEVELOPER);
            return null;
        }

        $json = json_decode($resp, true) ?: [];
        $contacts = $json['contacts'] ?? [];

        foreach ($contacts as $key => $c) {
            $candidate = $c['fields']['core']['email']['value'] ?? null;
            if (\is_string($candidate) && strtolower(trim($candidate)) === $email) {
                $idraw = $c['id'] ?? $key;
                $id = is_numeric($idraw) ? (int)$idraw : 0;
                return $id > 0 ? $id : null;
            }
        }

        return null;
    }

    /**
     * Create a new contact in Mautic with GDPR consent metadata.
     *
     * @param string $email The email address for the new contact.
     * @param array  $gdpr  GDPR consent metadata with keys 'ip', 'source', 'at'.
     * @return int The newly created Mautic contact ID.
     * @throws \moodle_exception If the API call fails or the response is invalid.
     */
    private function create_contact(string $email, array $gdpr): int {
        $curl = $this->curl();
        $curl->setHeader('Content-Type: application/json');

        $payload = [
            'email' => $email,
            'overwriteWithBlank' => false,
            // GDPR consent custom fields.
            'gdpr_ip'         => (string)($gdpr['ip'] ?? 'unknown'),
            'gdpr_source'     => (string)($gdpr['source'] ?? 'moodle'),
            'gdpr_consent_at' => (string)($gdpr['at'] ?? gmdate('Y-m-d H:i:s')),
        ];

        $url = $this->baseurl . '/api/contacts/new';
        $resp = $curl->post($url, json_encode($payload));

        $code = (int)($curl->get_info()['http_code'] ?? 0);
        if ($code >= 400) {
            throw new \moodle_exception('mautic_api_error', 'local_mauticsync', '', null,
                'Create contact failed: HTTP ' . $code . ' :: ' . $resp);
        }

        $json = json_decode($resp, true) ?: [];
        $id = $json['contact']['id'] ?? null;
        if (!is_numeric($id)) {
            throw new \moodle_exception('mautic_api_error', 'local_mauticsync', '', null,
                'Could not extract contact.id from Mautic response');
        }

        return (int)$id;
    }

    /**
     * Add a contact to a Mautic segment.
     *
     * @param int $contactid The Mautic contact ID.
     * @param int $segmentid The Mautic segment ID.
     * @throws \moodle_exception If the API call fails.
     */
    private function add_to_segment(int $contactid, int $segmentid): void {
        $curl = $this->curl();

        $url = $this->baseurl . '/api/segments/' . $segmentid . '/contact/' . $contactid . '/add';
        $resp = $curl->post($url, '');

        $code = (int)($curl->get_info()['http_code'] ?? 0);
        if ($code >= 400) {
            throw new \moodle_exception('mautic_api_error', 'local_mauticsync', '', null,
                'Add to segment failed: HTTP ' . $code . ' :: ' . $resp);
        }
    }
}
