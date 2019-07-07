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
 * short_description
 *
 * long_description
 *
 * @package    local_providerapi
 * @copyright  2019 çağlar MERSİNLİ <ceremy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_providerapi\local\helper;
use local_providerapi\local\tool_provider;

require_once(__DIR__ . '/../../config.php');

$toolid = null;
$token = null;
$filearguments = get_file_argument();
$arguments = explode('/', trim($filearguments, '/'));
if (count($arguments) == 2) {
    list($toolid, $token) = $arguments;
}

$toolid = optional_param('id', $toolid, PARAM_INT);
$token = optional_param('token', $token, PARAM_ALPHANUM);

$PAGE->set_context(context_system::instance());
$url = new moodle_url('/local/providerapi/proxy.php');
$PAGE->set_url($url);
$PAGE->set_pagelayout('popup');
$PAGE->set_title(get_string('registration', 'local_providerapi'));

// Only show the proxy if the token parameter is correct.
// If we do not compare with a shared secret, someone could very easily
// guess an id for the enrolment.
if (!helper::verify_proxy_token($toolid, $token)) {
    throw new \moodle_exception('incorrecttoken', 'local_providerapi');
}
$tool = helper::get_lti_tool($toolid);

$messagetype = required_param('lti_message_type', PARAM_TEXT);

// Only accept proxy registration requests from this endpoint.
if ($messagetype != "ToolProxyRegistrationRequest") {
    print_error('invalidrequest', 'local_providerapi');
    exit();
}

$toolprovider = new tool_provider($toolid);
$toolprovider->handleRequest();
echo $OUTPUT->header();
echo $OUTPUT->footer();
