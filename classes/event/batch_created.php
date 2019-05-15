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

namespace local_providerapi\event;

use core_user;
use local_providerapi\local\batch\batch;
use local_providerapi\local\institution\institution;
use moodle_url;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Class institution_created
 *
 * @package local_providerapi\event
 */
class batch_created extends \core\event\base {

    /**
     * Override in subclass.
     *
     * Set all required data properties:
     *  1/ crud - letter [crud]
     *  2/ edulevel - using a constant self::LEVEL_*.
     *  3/ objecttable - name of database table if objectid specified
     *
     * Optionally it can set:
     * a/ fixed system context
     *
     * @return void
     */
    protected function init() {
        $this->data['objecttable'] = batch::$dbname;
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * @param $id
     * @return \core\event\base
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function create_from_objectid($id) {
        $batch = batch::get($id);
        $data = array(
                'objectid' => $id,
                'context' => \context_system::instance(),
                'userid' => $batch->createrid

        );

        $event = self::create($data);
        return $event;
    }

    /**
     * @return string
     * @throws \coding_exception
     */
    public static function get_name() {
        return get_string('createdbatch', 'local_providerapi');
    }

    /**
     * @return string
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_description() {
        $a = new  stdClass();
        $a->by = fullname(core_user::get_user($this->userid));
        $a->objname = 'Batch';
        $a->objid = $this->objectid;
        return get_string('eventcreated', 'local_providerapi', $a);
    }

    /**
     * @return moodle_url
     * @throws \moodle_exception
     */
    public function get_url() {
        return new moodle_url('/local/providerapi/modules/batch/index.php');

    }
}
