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
 * Model Base class
 *
 * long_description
 *
 * @package    local_providerapi
 * @copyright  2019 çağlar MERSİNLİ <ceremy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_providerapi\local;

use coding_exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Class modelbase
 *
 * @package local_providerapi\local
 */
abstract class modelbase {

    /**
     * @var string|null
     */
    protected static $dbname = null;

    /**
     * @var array
     */
    protected static $pages = array();

    /**
     * @var \stdClass
     */
    protected $_data;

    /**
     * modelbase constructor.
     *
     * @param \stdClass $data
     */
    public function __construct(\stdClass $data) {
        $this->_data = $data;
    }

    /**
     * @param int|\stdClass $id
     * @return self
     * @throws \dml_exception
     */
    abstract public static function get($id);

    /**
     * @param \navigation_node $node
     * @param null $pages
     * @throws \moodle_exception
     * @throws coding_exception
     */

    /**
     * @param $id
     * @return bool
     * @throws \dml_exception
     */
    public static function validate_exist($id) {
        global $DB;
        return $DB->record_exists(static::$dbname, array('id' => $id));

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

        if ($newid = $DB->insert_record(static::$dbname, $data)) {
            static::create_event($newid);
            return $newid;
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
        if ($DB->update_record(static::$dbname, $data)) {
            static::update_event($data->id);
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
                static::delete_event($data);
                return true;
            }
        }
        throw new coding_exception('object\'s id does not exist');
    }

    /**
     * Create Event
     *
     * @param $id
     *
     */
    abstract protected function create_event($id);

    /**
     * Update Event
     *
     * @param $id
     *
     */
    abstract protected function update_event($id);

    /**
     * Delete Event
     *
     * @param \stdClass $data
     *
     */
    abstract protected function delete_event($data);

}
