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
 * switch institution
 *
 * long_description
 *
 * @package    local_providerapi
 * @copyright  2019 çağlar MERSİNLİ <ceremy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\notification;

require_once("../../config.php");
require_login();

global $PAGE, $SESSION, $USER;

$returnurl = required_param('returnurl', PARAM_LOCALURL);
$institutionid = optional_param('institutionid', null, PARAM_INT);

$returnurl = new moodle_url($returnurl);

if (!empty($institutionid) && confirm_sesskey()) {
    unset($SESSION->institution);
    $SESSION->institution = $institutionid;
    notification::success(get_string('successswitchinstitution', 'local_providerapi'));
} else {
    notification::error(get_string('somethingwrong', 'local_providerapi'));
    \core\session\manager::kill_user_sessions($USER->id);
}

redirect($returnurl);