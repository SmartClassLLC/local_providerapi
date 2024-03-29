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
 * Plugin internal classes, functions and constants are defined here.
 *
 * @package     local_providerapi
 * @copyright   2019 Çağlar MERSİNLİ <ceremy@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\event\course_deleted;
use local_providerapi\event\btcourse_deleted;
use local_providerapi\event\sharedcourse_created;
use local_providerapi\event\sharedcourse_deleted;
use local_providerapi\local\course\course;
use local_providerapi\local\helper;

defined('MOODLE_INTERNAL') || die();

/**
 * @param course_deleted $event
 * @throws coding_exception
 * @throws dml_exception
 */
function coursedeleted(course_deleted $event) {
    global $DB;
    // Exist Sahared Courses?
    $sharedcourses = $DB->get_records(course::$dbname, array('courseid' => $event->objectid), '', 'id');
    if ($sharedcourses) {
        foreach ($sharedcourses as $sharedcourse) {
            course::delete($sharedcourse->id);
        }
    }
    // Delete lti sharecourse.
    helper::delete_tool($event->objectid);
}

/**
 * @param sharedcourse_deleted $event
 * @throws coding_exception
 * @throws dml_exception
 */
function sharedcoursedeleted(sharedcourse_deleted $event) {
    global $DB;
    $sharedcourseid = $event->objectid;
    $btcourses = $DB->get_records_select('local_providerapi_btcourses', 'sharedcourseid = ?', array($sharedcourseid));
    if ($btcourses) {
        foreach ($btcourses as $btcourse) {
            if ($DB->delete_records('local_providerapi_btcourses', array('id' => $btcourse->id))) {
                btcourse_deleted::create_from_objectid($btcourse)->trigger();
            }
        }
    }
}

/**
 * @param sharedcourse_created $event
 * @throws dml_exception
 * @throws moodle_exception
 */
function sharedcoursecreated(sharedcourse_created $event) {
    $sharedcourseid = $event->objectid;
    $sharedcourse = course::get($sharedcourseid);
    $courserecord = $sharedcourse->get_record();

    // Force Course GROUPMODE.
    $courserecord->groupmode = SEPARATEGROUPS;
    $courserecord->groupmodeforce = 1;
    update_course($courserecord);

    // Start lti sharing.
    helper::create_tool($courserecord->id);

}


