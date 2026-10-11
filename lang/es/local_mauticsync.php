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
 * Spanish language pack for Mautic sync
 *
 * @package    local_mauticsync
 * @category   string
 * @copyright  2025 Studio LXD <hello@studiolxd.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Mautic sincronización';

$string['signup_optin_label'] = 'Quiero recibir comunicaciones de marketing por email (puedo darme de baja cuando quiera).';

$string['mautic_baseurl'] = 'URL base de Mautic';
$string['mautic_baseurl_desc'] = 'Ejemplo: https://marketing.studiolxd.com (sin barra final).';

$string['mautic_user'] = 'Usuario de la API de Mautic';
$string['mautic_user_desc'] = 'Usuario para autenticación Basic Auth.';

$string['mautic_pass'] = 'Contraseña de la API de Mautic';
$string['mautic_pass_desc'] = 'Contraseña para autenticación Basic Auth.';

$string['marketing_segment_id'] = 'Segmento de marketing';
$string['marketing_segment_id_desc'] = 'Segmento de Mautic al que se añadirá el contacto cuando marque el consentimiento (p. ej., "Newsletter").';

$string['choose'] = 'Seleccione';
$string['save_credentials_first'] = 'Primero guarde la URL de Mautic, el usuario y la contraseña de la API, y luego recargue esta página para seleccionar el segmento de marketing.';
$string['api_connection_error'] = 'Error de conexión con la API de Mautic';
$string['api_connection_error_desc'] = 'No se pudo conectar con la API de Mautic: {$a}. Verifique sus credenciales e intente de nuevo.';
$string['mautic_api_error'] = 'Se produjo un error al comunicarse con la API de Mautic.';

$string['privacy:metadata'] = 'El plugin Mautic sync envía datos de usuario a una instancia externa de Mautic CRM con fines de marketing.';
$string['privacy:metadata:email'] = 'La dirección de correo electrónico del usuario se envía a Mautic para crear o identificar un contacto.';
$string['privacy:metadata:ip'] = 'La dirección IP del usuario se envía a Mautic para el registro de consentimiento RGPD.';