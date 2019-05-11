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
use local_providerapi\local\course\course;

defined('MOODLE_INTERNAL') || die();

function coursedeleted(course_deleted $event) {
    global $DB;
    // Exist Sahared Courses?
    $sharedcourses = $DB->get_records(course::$dbname, array('courseid' => $event->objectid));
    if ($sharedcourses) {
        foreach ($sharedcourses as $sharedcourse) {
            course::delete($sharedcourse->id);
        }
    }
}


