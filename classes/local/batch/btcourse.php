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
 * btcourse class
 *
 * long_description
 *
 * @package    local_providerapi
 * @copyright  2019 çağlar MERSİNLİ <ceremy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_providerapi\local\batch;

use coding_exception;
use local_providerapi\event\btcourse_deleted;
use moodle_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Class btcourse
 *
 * @property-read int id
 * @property-read int batchid
 * @property-read int sharedcourseid
 * @property-read int createrid
 * @property-read int timecreated
 * @property-read int timemodified
 * @property-read int modifiedby
 * @property-read string source
 *
 *
 *
 * @package local_providerapi\local
 */
class btcourse {

    /**
     * @var string
     */
    public static $dbname = "local_providerapi_btcourses";

    /**
     * @var stdClass
     */
    protected $_data;

    /**
     * btcourse constructor.
     *
     * @param stdClass $data
     */
    public function __construct(stdClass $data) {
        $this->_data = $data;
    }

    /**
     * @param $id
     * @return btcourse
     * @throws \dml_exception
     */
    public static function get($id) {
        global $DB;
        if (!is_object($id)) {
            $data = $DB->get_record(self::$dbname, array('id' => $id), '*', MUST_EXIST);
        } else {
            $data = $id;
        }
        return new self($data);
    }

    /**
     * @param $prop
     * @return mixed
     * @throws coding_exception
     */
    public function __get($prop) {
        if (property_exists($this->_data, $prop)) {
            return $this->_data->$prop;
        }
        throw new coding_exception('Property "' . $prop . '" does not exist');
    }

    /**
     * @return mixed
     * @throws coding_exception
     */
    public function get_db_record() {
        if (empty($this->_data)) {
            throw new coding_exception('object does not exist');
        }
        return fullclone($this->_data);
    }

    /**
     *
     * @throws \dml_exception
     */
    public function create() {
        global $DB, $USER;
        $data = fullclone($this->_data);
        if (empty($data->sharedcourseids) || !is_array($data->sharedcourseids) || !property_exists($data, 'batchid')) {
            throw new moodle_exception('missingproperty', 'local_providerapi');
        }
        if (!batch::validate_exist($data->batchid)) {
            throw new moodle_exception('notexistbatch', 'local_providerapi');
        }
        if (empty($data->createrid)) {
            $data->createrid = $USER->id;
        }
        if (empty($data->modifiedby)) {
            $data->modifiedby = $USER->id;
        }
        $data->timecreated = time();
        $data->timemodified = $data->timecreated;
        $sharedcourseids = $data->sharedcourseids;
        unset($data->sharedcourseids);
        $records = array();
        foreach ($sharedcourseids as $sharedcourseid) {
            if ($DB->record_exists(self::$dbname, array('batchid' => $data->batchid, 'sharedcourseid' => $sharedcourseid))) {
                continue;
            }
            $record = fullclone($data);
            $record->sharedcourseid = $sharedcourseid;
            $records[] = $record;
        }
        $DB->insert_records(static::$dbname, $records);
    }

    /**
     * @return bool
     * @throws \dml_exception
     */
    public function update() {
        global $DB, $USER;
        $data = fullclone($this->_data);
        $data->modifiedby = $USER->id;
        $data->timemodified = time();
        if ($DB->update_record(static::$dbname, $data)) {
            $this->update_event($data->id);
            return true;
        }
        return false;
    }

    /**
     * @return bool
     * @throws \dml_exception
     * @throws coding_exception
     */
    public function delete() {
        global $DB;
        $data = fullclone($this->_data);
        if (!empty($data->id)) {
            if ($DB->delete_records(static::$dbname, array('id' => $data->id))) {
                $this->delete_event($data);
                return true;
            }
        }
        throw new coding_exception('obje idsi bulunamadı');
    }

    /**
     * @param int $batchid
     * @return array
     * @throws \dml_exception
     */
    public static function get_btsharecourseids(int $batchid) {
        global $DB;
        return $DB->get_fieldset_select(self::$dbname, 'sharedcourseid', 'batchid = ?', array($batchid));
    }

    /**
     * @param int $batchid
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function delete_from_batchid(int $batchid) {
        global $DB;
        $allbtcourserecords = $DB->get_records(self::$dbname, array('batchid' => $batchid));
        if ($allbtcourserecords) {
            foreach ($allbtcourserecords as $btcourserecord) {
                self::get($btcourserecord)->delete();
            }
        }
    }

    /**
     * @param $id
     */
    protected function update_event($id) {
    }

    /**
     * @param $record
     * @throws \dml_exception
     * @throws coding_exception
     */
    protected function delete_event($record) {
        btcourse_deleted::create_from_objectid($record)->trigger();
    }

}