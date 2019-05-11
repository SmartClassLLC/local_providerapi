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
 * course object
 *
 * long_description
 *
 * @package    local_providerapi
 * @copyright  2019 çağlar MERSİNLİ <ceremy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_providerapi\local\course;

use core_course_list_element;
use local_providerapi\event\sharedcourse_created;
use local_providerapi\event\sharedcourse_deleted;
use local_providerapi\local\institution\institution;
use moodle_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Class course
 *
 * @property-read int sharedcourseid
 * @property-read int institutionid
 * @property-read int sharedcoursecreaterid
 * @package local_providerapi\local\course
 */
class course extends core_course_list_element {

    /**
     * @var string
     */
    public static $dbname = 'local_providerapi_courses';

    /**
     * @param int $id
     * @throws \dml_exception
     * @return course
     */
    public static function get(int $id) {
        global $DB;
        $sharedcourse = $DB->get_record(self::$dbname, array('id' => $id), '*', MUST_EXIST);
        $courserecord = get_course($sharedcourse->courseid);
        $courserecord->institutionid = $sharedcourse->institutionid;
        $courserecord->sharedcourseid = $sharedcourse->id;
        $courserecord->sharedcoursecreaterid = $sharedcourse->createrid;
        return new self($courserecord);
    }

    /**
     * @param stdClass $data
     * @throws |coding_exception
     * @throws \dml_exception
     * @throws moodle_exception
     */
    public static function create(stdClass $data): void {
        global $DB, $USER;
        if (empty($data->courseids) || !property_exists($data, 'institutionid')) {
            throw new moodle_exception('missingproperty', 'local_providerapi');
        }
        if (!institution::exist($data->institutionid)) {
            throw new moodle_exception('notexistinstitution', 'local_providerapi');
        }
        $now = time();
        foreach ($data->courseids as $courseid) {
            if (!$DB->record_exists(self::$dbname, array('courseid' => $courseid, 'institutionid' => $data->institutionid))) {
                $item = new stdClass();
                $item->institutionid = $data->institutionid;
                $item->courseid = $courseid;
                $item->createrid = $USER->id;
                $item->timecreated = $now;
                if ($newid = $DB->insert_record(self::$dbname, $item)) {
                    sharedcourse_created::create_from_objectid($newid)->trigger();
                }
            }
        }

    }

    /**
     * @param $institutionid
     * @throws \dml_exception
     */
    public static function deletebyinstitutionid(int $institutionid): void {
        global $DB;
        $courses = $DB->get_fieldset_select(self::$dbname, 'id', 'institutionid = ?', array($institutionid));
        if ($courses) {
            foreach ($courses as $id) {
                self::delete($id);
            }
        }
    }

    /**
     * @param $id
     * @throws \dml_exception
     */
    public static function delete($id): void {
        global $DB;
        $record = $DB->get_record(self::$dbname, array('id' => $id));
        if ($DB->delete_records(self::$dbname, array('id' => $id))) {
            sharedcourse_deleted::create_from_objectid($record)->trigger();
        }
    }

}
