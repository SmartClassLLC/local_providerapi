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

use context_helper;
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
     * @param int $courseid
     * @return course
     * @throws \dml_exception
     */
    public static function get_by_courseid(int $courseid) {
        $record = get_course($courseid);
        return new self($record);
    }

    /**
     * @return stdClass
     */
    public function get_record() {
        return $this->record;
    }

    /**
     * @param stdClass $data
     * @throws |coding_exception
     * @throws \dml_exception
     * @throws moodle_exception
     */
    public static function create(stdClass $data) {
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
    public static function deletebyinstitutionid(int $institutionid) {
        global $DB;
        $courses = $DB->get_fieldset_select(self::$dbname, 'id', 'institutionid = ?', array($institutionid));
        if ($courses) {
            foreach ($courses as $id) {
                self::delete($id);
            }
        }
    }

    /**
     * @param int $id
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function delete(int $id): bool {
        global $DB;
        $record = $DB->get_record(self::$dbname, array('id' => $id));
        if ($DB->delete_records(self::$dbname, array('id' => $id))) {
            sharedcourse_deleted::create_from_objectid($record)->trigger();
            return true;
        }
        return false;
    }

    /**
     * @param int $institutionid
     * @return array
     * @throws \dml_exception
     */
    public static function get_course_menu(int $institutionid) {
        global $DB;
        list($select, $from, $wheres, $params) = self::get_sql($institutionid);
        return $DB->get_records_sql_menu("SELECT sc.id,c.fullname FROM {$from} WHERE {$wheres}", $params);
    }

    /**
     * @param int $institutionid
     * @param string $additionalwhere
     * @param array $additionalparams
     * @return array
     */
    public static function get_sql(int $institutionid, $additionalwhere = '', $additionalparams = array()): array {

        $wheres = array();
        $params = array();
        $select = "DISTINCT sc.id,sc.createrid, c.fullname AS coursefullname,c.shortname AS courseshortname,c.id AS courseid," .
                context_helper::get_preload_record_columns_sql('ctx');
        $joins = array('{local_providerapi_courses} sc');
        $joins[] = 'JOIN {course} c ON c.id = sc.courseid';
        $joins[] = 'LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)';
        $params['contextlevel'] = CONTEXT_COURSE;

        $wheres[] = ' sc.institutionid = :institutionid';
        $params['institutionid'] = $institutionid;

        if (!empty($additionalwhere)) {
            $wheres[] = $additionalwhere;
            $params = array_merge($params, $additionalparams);
        }

        $from = implode("\n", $joins);
        if ($wheres) {
            $wheres = implode(' AND ', $wheres);
        } else {
            $wheres = '';
        }

        return array($select, $from, $wheres, $params);
    }

}
