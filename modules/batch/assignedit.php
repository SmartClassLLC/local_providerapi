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

use local_providerapi\local\batch\btcourse;

require('../../../../config.php');
require_once($CFG->dirroot . '/local/providerapi/locallib.php');
require_login();

// System context.
$context = context_system::instance();

$delid = optional_param('delid', null, PARAM_INT);
// Baseurl.
$baseurl = new moodle_url('/local/providerapi/modules/batch/assignedit.php');
$btcourseurl = new moodle_url('/local/providerapi/modules/batch/assigncourse.php');
$returnurl = optional_param('returnurl', null, PARAM_LOCALURL);

if ($returnurl) {
    $returnurl = new moodle_url($returnurl);
} else {
    $returnurl = $btcourseurl;
}

if ($delid and has_capability('local/providerapi:deleteassigncourse', $context) and confirm_sesskey()) {
    if ($batch->source === PROVIDERAPI_SOURCEWS) {
        throw new moodle_exception('hackattempt', 'local_providerapi');
    }
    btcourse::get($delid)->delete();
    redirect($returnurl);
}