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
 * Event Class
 *
 * long_description
 *
 * @package    local_providerapi
 * @copyright  2019 çağlar MERSİNLİ <ceremy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_providerapi\event;

use core_user;

defined('MOODLE_INTERNAL') || die();

/**
 * Class institution_deleted
 *
 * @package local_providerapi\event
 */
class btcourse_deleted extends \core\event\base {

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
        $this->data['objecttable'] = 'local_providerapi_btcourses';
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * @param  \stdClass $record
     * @return \core\event\base
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function create_from_objectid($record) {
        global $USER;
        $data = array(
                'objectid' => $record->id,
                'context' => \context_system::instance(),
                'userid' => $USER->id,
                'other' => [
                        'batchid' => $record->batchid,
                        'sharedcourseid' => $record->sharedcourseid,
                        'groupid' => $record->groupid
                ]
        );

        $event = self::create($data);
        $event->add_record_snapshot('local_providerapi_btcourses', $record);
        return $event;
    }

    /**
     * @return string
     * @throws \\coding_exception
     */
    public static function get_name() {
        return get_string('deletedbtcourse', 'local_providerapi');
    }

    /**
     * @return string
     * @throws \\coding_exception
     * @throws \\dml_exception
     */
    public function get_description() {
        $a = new  \stdClass();
        $a->by = fullname(core_user::get_user($this->userid));
        $a->objname = 'Batch\'s Course';
        $a->objid = $this->objectid;
        return get_string('eventdeleted', 'local_providerapi', $a);
    }

}


