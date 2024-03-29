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

namespace local_providerapi\local\batch;

use core\notification;
use local_providerapi\event\batch_created;
use local_providerapi\event\batch_deleted;
use local_providerapi\event\batch_updated;
use local_providerapi\local\cohortHelper;
use local_providerapi\local\institution\institution;
use local_providerapi\local\modelbase;
use moodle_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Class batch
 *
 * @property-read int id
 * @property-read int institutionid
 * @property-read int cohortid
 * @property-read int capacity
 * @property-read int createrid
 * @property-read int modifiedby
 * @property-read int timecreated
 * @property-read int timemodified
 * @property-read string name
 * @property-read string source
 * @package local_providerapi\local\batch
 */
class batch extends modelbase {

    /**
     * @var string
     */
    public static $dbname = "local_providerapi_batches";

    /**
     * @var string
     */
    public $btcoursedbname = "local_providerapi_btcourses";

    /**
     * @param int|stdClass $id
     * @return self
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
     * @return bool|int
     * @throws \dml_exception
     */
    public function create() {
        global $DB, $USER;
        $data = fullclone($this->_data);
        if (empty($data->createrid)) {
            $data->createrid = $USER->id;
        }
        if (empty($data->modifiedby)) {
            $data->modifiedby = $USER->id;
        }
        $data->timecreated = time();
        $data->timemodified = $data->timecreated;
        if (!$DB->record_exists(self::$dbname, array('institutionid' => $data->institutionid, 'name' => $data->name))) {
            if ($newid = $DB->insert_record(self::$dbname, $data)) {
                self::create_event($newid);
                return $newid;
            }
        } else {
            throw new moodle_exception('alreadyexists', 'local_providerapi', null, $data->name);
        }
        return false;
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
        if (!$DB->record_exists_select(self::$dbname, 'institutionid = :institutionid AND name = :name AND id <> :id',
                array('institutionid' => $data->institutionid, 'name' => $data->name, 'id' => $data->id))) {
            if ($DB->update_record(self::$dbname, $data)) {
                self::update_event($data->id);
                return true;
            }
        } else {
            throw new moodle_exception('alreadyexists', 'local_providerapi', null, $data->name);
        }
        return false;
    }

    /**
     * @param int $institutionid
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function deletebyinstitutionid(int $institutionid) {
        global $DB;
        $batches = $DB->get_fieldset_select(self::$dbname, 'id', 'institutionid = ?', array($institutionid));
        if ($batches) {
            foreach ($batches as $id) {
                self::get($id)->delete();
            }
        }
    }

    /**
     * @return int
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function createcohort() {
        $data = new stdClass();
        $data->name = $this->formattedcohortname();
        $data->idnumber = uniqid($data->name . '_');
        return cohortHelper::create($data);
    }

    /**
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function updatecohort() {
        $data = new stdClass();
        $data->id = $this->cohortid;
        $data->name = $this->formattedcohortname();
        $data->idnumber = uniqid($data->name . '_');
        return cohortHelper::update($data);
    }

    /**
     * @return string
     * @throws \dml_exception
     */
    public function formattedcohortname() {
        $institution = institution::get($this->institutionid);
        $string = $institution->name . ' (' . $this->name . ')';
        return format_string($string);
    }

    /**
     * @param $userid
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws moodle_exception
     */
    public function add_member($userid) {
        $cohortid = $this->cohortid;
        if (empty($cohortid)) {
            throw new moodle_exception('cohortnotexist', 'local_providerapi');
        }
        if (!$this->is_full_members()) {
            cohortHelper::add_member($this->cohortid, $userid);
            return true;
        } else {
            $user = \core_user::get_user($userid);
            notification::error(get_string('capacityisfull', 'local_providerapi', fullname($user)));
            return false;
        }
    }

    /**
     * @param $userid
     * @throws moodle_exception
     */
    public function remove_member($userid) {
        $cohortid = $this->cohortid;
        if (empty($cohortid)) {
            throw new moodle_exception('cohortnotexist', 'local_providerapi');
        }
        cohortHelper::delete_member($cohortid, $userid);
    }

    /**
     * @param $userid
     * @return bool
     * @throws moodle_exception
     */
    public function is_member($userid) {
        $cohortid = $this->cohortid;
        if (empty($cohortid)) {
            throw new moodle_exception('cohortnotexist', 'local_providerapi');
        }
        return cohortHelper::is_member($cohortid, $userid);
    }

    /**
     * @return int
     * @throws \dml_exception
     */
    public function count_members() {
        global $DB;
        list($select, $from, $where, $params) = $this->get_member_sql();
        return $DB->count_records_sql("SELECT COUNT(1) FROM {$from} WHERE {$where}", $params);
    }

    /**
     * @return bool
     * @throws \dml_exception
     */
    public function is_full_members() {
        $data = fullclone($this->_data);
        if ($data->capacity == 0) {
            return false;
        }
        $memberscount = $this->count_members();
        return ($memberscount >= $data->capacity) ? true : false;
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
        $select = "bt.*";
        $joins = array('{local_providerapi_batches} bt');
        $wheres[] = ' bt.institutionid = :institutionid';
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

    /**
     * @param string $additionalwhere
     * @param array $additionalparams
     * @return array
     */
    public function get_member_sql($additionalwhere = '', $additionalparams = array()) {
        $wheres = array();
        $params = array();
        $select = "u.*,bt.cohortid AS cohortid,bt.source ";
        $joins = array('{local_providerapi_batches} bt');
        $joins[] = "JOIN {cohort_members} cm ON cm.cohortid = bt.cohortid ";
        $joins[] = "JOIN {user} u ON u.id = cm.userid ";
        $wheres[] = 'bt.id = :batchid';
        $params['batchid'] = $this->id;
        $wheres[] = 'u.deleted = 0 ';

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

    /**
     * yeni kayıt için event olayı yazılacak
     *
     * @param $id
     *
     */
    protected function create_event($id) {
        batch_created::create_from_objectid($id)->trigger();
    }

    /**
     * güncelleme için event olayı yazılacak
     *
     * @param $id
     *
     */
    protected function update_event($id) {
        batch_updated::create_from_objectid($id)->trigger();
    }

    /**
     * silme için event olayı yazılacak
     *
     * @param stdClass $data
     *
     */
    protected function delete_event($data) {
        batch_deleted::create_from_objectid($data)->trigger();
    }
}