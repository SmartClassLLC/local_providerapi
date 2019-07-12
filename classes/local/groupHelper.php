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

namespace local_providerapi\local;

use dml_exception;
use local_providerapi\local\batch\btcourse;
use moodle_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/grouplib.php');
require_once($CFG->dirroot . '/group/lib.php');

/**
 * Class groupHelper
 *
 * @package local_providerapi\local
 */
class groupHelper {

    /**
     * @param stdClass $data
     * @return int
     * @throws moodle_exception
     */
    public static function create_group(stdClass $data) {
        $data->description = 'Created by Providerapi Local Plugin.DO NOT DELETE via Web Interface ';
        $data->descriptionformat = FORMAT_HTML;
        return groups_create_group($data);
    }

    /**
     * @param stdClass $data
     * @return bool
     * @throws moodle_exception
     */
    public static function update_group(stdClass $data) {
        return groups_update_group($data);
    }

    /**
     * @param int $groupid
     * @return bool
     */
    public static function delete_group(int $groupid) {
        return groups_delete_group($groupid);
    }

    /**
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function check_group_instances() {
        global $DB;
        list($select, $from, $where, $params) = btcourse::get_sql('bt.groupid IS NULL');
        $select = "bt.id,b.name AS batchname,com.name AS istitutionname,c.id AS courseid";

        $btcourses = $DB->get_records_sql("SELECT {$select} FROM {$from} WHERE {$where}", $params);
        if ($btcourses) {
            foreach ($btcourses as $btcourse) {
                $formattedname = $btcourse->istitutionname . ' (' . $btcourse->batchname . ')';
                $data = new stdClass();
                $data->name = $formattedname;
                $data->courseid = $btcourse->courseid;
                $groupid = self::create_group($data);
                if ($groupid) {
                    $DB->set_field(btcourse::$dbname, 'groupid', $groupid, array('id' => $btcourse->id));
                }

            }
        }

    }

    /**
     * @param $batchid
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function update_group_instance_names($batchid) {
        global $DB;
        list($select, $from, $where, $params) =
                btcourse::get_sql('bt.groupid IS NOT NULL AND b.id = :batchid', array('batchid' => $batchid));
        $select = "bt.id,bt.groupid AS groupid,b.name AS batchname,com.name AS istitutionname,c.id AS courseid";

        $btcourses = $DB->get_records_sql("SELECT {$select} FROM {$from} WHERE {$where}", $params);
        if ($btcourses) {
            foreach ($btcourses as $btcourse) {
                $group = $DB->get_record('groups', array('id' => $btcourse->groupid));
                if ($group) {
                    $formattedname = $btcourse->istitutionname . ' (' . $btcourse->batchname . ')';
                    $group->name = $formattedname;
                    self::update_group($group);
                }

            }
        }
    }

}