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

use local_providerapi\event\batch_created;
use local_providerapi\event\batch_deleted;
use local_providerapi\event\batch_updated;
use local_providerapi\local\cohortHelper;
use local_providerapi\local\institution\institution;
use local_providerapi\local\modelbase;
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
 * @package local_providerapi\local\batch
 */
class batch extends modelbase {

    /**
     * @var string
     */
    public static $dbname = "local_providerapi_batches";

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
        $string = $institution->name . ' (' . $this->name.')';
        return format_string($string);
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