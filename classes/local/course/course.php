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
use local_providerapi\local\institution\institution;
use moodle_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Class course
 *
 * @package local_providerapi\local\course
 */
class course extends core_course_list_element {

    /**
     * @var string
     */
    public static $dbname = 'local_providerapi_courses';

    /**
     * @param stdClass $data
     * @throws |coding_exception
     * @throws \dml_exception
     * @throws moodle_exception
     */
    public static function create(stdClass $data): void {
        global $DB;
        if (empty($data->courseids) || !property_exists($data, 'institutionid')) {
            throw new moodle_exception('missingproperty', 'local_providerapi');
        }

        if (!institution::exist($data->institutionid)) {
            throw new moodle_exception('notexistinstitution', 'local_providerapi');
        }
        $records = array();
        array_walk($data->courseids, function($v, $k) use (&$records, $data) {
            $item = new stdClass();
            $item->institutionid = $data->institutionid;
            $item->courseid = $v;
            $records[] = $item;
        });

        $DB->insert_records(self::$dbname, $records);

    }

    /**
     * @param $id
     * @return bool
     * @throws \dml_exception
     */
    public static function delete($id) {
        global $DB;
        return $DB->delete_records(self::$dbname, array('id' => $id));
        // TODO: delete event.
    }

    /**
     * @param int $institutionid
     * @return stdClass
     * @throws \dml_exception
     */
    public static function get_db_records(int $institutionid): stdClass {
        global $DB;
        $courseids = $DB->get_fieldset_select(self::$dbname, 'courseid', ' institutionid = ?', array($institutionid));
        $data = new stdClass();
        $data->institutionid = $institutionid;
        $data->courseids = $courseids;
        return $data;
    }

}
