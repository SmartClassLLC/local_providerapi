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
 * Department
 *
 * long_description
 *
 * @package    local_providerapi
 * @copyright  2019 çağlar MERSİNLİ <ceremy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_providerapi\local\institution;

use local_providerapi\event\institution_created;
use local_providerapi\event\institution_deleted;
use local_providerapi\event\institution_updated;
use local_providerapi\local\cohortHelper;
use local_providerapi\local\modelbase;

defined('MOODLE_INTERNAL') || die();

/**
 * department class
 *
 * long_description
 *
 * @property-read int id
 * @property-read int createrid
 * @property-read int modifiedby
 * @property-read int cohortid
 * @property-read int descriptionformat
 * @property-read  string name
 * @property-read  string description
 * @property-read  string shortname
 * @property-read  string secretkey
 *
 * @package    local_providerapi
 * @copyright  2019 çağlar MERSİNLİ <ceremy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class institution extends modelbase {

    /**
     * @var string
     */
    public static $dbname = "local_providerapi_companies";

    /**
     * @param int|\stdClass $id
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
     * @param int $id
     * @return bool
     * @throws \dml_exception
     */
    public static function exist(int $id): bool {
        global $DB;
        return $DB->record_exists(self::$dbname, array('id' => $id));
    }

    /**
     * @param string $additionalwhere
     * @param array $additionalparams
     * @return array
     */
    public static function get_sql($additionalwhere = '', $additionalparams = array()) {

        $wheres = array();
        $params = array();
        $select = "cmp.* ";
        $joins = array('{local_providerapi_companies} cmp');

        $wheres[] = ' cmp.secretkey IS NOT null';

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
     * @return int
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function createcohort() {
        $data = new \stdClass();
        $data->name = format_string($this->name);
        $data->idnumber = uniqid($this->shortname . '_');
        return cohortHelper::create($data);
    }

    /**
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function updatecohort() {
        $data = new \stdClass();
        $data->id = $this->cohortid;
        $data->name = format_string($this->name);
        $data->idnumber = uniqid($this->shortname . '_');
        return cohortHelper::update($data);
    }

    /**
     * @return array
     * @throws \dml_exception
     */
    public static function get_menu() {
        global $DB;
        $menu = array();
        $institutions = $DB->get_records('local_providerapi_companies');
        if ($institutions) {
            foreach ($institutions as $institution) {
                $menu[$institution->id] = $institution->name . ' (' . $institution->shortname . ')';
            }
        }
        return $menu;
    }

    /**
     * yeni kayıt için event olayı yazılacak
     *
     * @param $id
     *
     */
    protected function create_event($id) {
        institution_created::create_from_objectid($id)->trigger();
    }

    /**
     * güncelleme için event olayı yazılacak
     *
     * @param $id
     *
     */
    protected function update_event($id) {
        institution_updated::create_from_objectid($id)->trigger();
    }

    /**
     * silme için event olayı yazılacak
     *
     * @param \stdClass $data
     *
     */
    protected function delete_event($data) {
        institution_deleted::create_from_objectid($data)->trigger();
    }
}

