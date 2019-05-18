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

use context_system;
use core_plugin_manager;
use local_providerapi\local\batch\btcourse;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/enrollib.php');

/**
 * Class enrolHelper
 *
 * @package local_providerapi\local
 */
class enrolHelper {

    /**
     * @var \enrol_plugin
     */
    protected $enrol;

    /**
     * enrolHelper constructor.
     *
     * @param string $name
     * @throws \dml_exception
     */
    public function __construct($name) {
        $enabled = enrol_get_plugins(true);
        if (!array_key_exists($name, $enabled)) {
            $enabled = array_keys($enabled);
            $enabled[] = $name;
            set_config('enrol_plugins_enabled', implode(',', $enabled));
            core_plugin_manager::reset_caches();
            context_system::instance()->mark_dirty();
        }
        $this->enrol = enrol_get_plugin($name);
    }

    /**
     * @param string $name
     * @return enrolHelper
     * @throws \dml_exception
     */
    public static function instance($name) {
        return new self($name);
    }

    /**
     * @return \enrol_plugin
     */
    public function get_enrol() {
        return $this->enrol;
    }

    /**
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function check_enrol_instances() {
        global $DB;
        $enrol = clone($this->enrol);
        list($select, $from, $where, $params) = btcourse::get_sql('bt.enrolinstanceid IS NULL AND bt.groupid IS NOT NULL');
        $select = "bt.id,bt.groupid AS groupid,b.cohortid AS cohortid,c.id AS courseid";
        $btcourses = $DB->get_records_sql("SELECT {$select} FROM {$from} WHERE {$where}", $params);
        if ($btcourses) {
            $roles = $DB->get_records('role', array('archetype' => 'student'));
            $studentrole = array_shift($roles);
            foreach ($btcourses as $btcourse) {
                $course = get_course($btcourse->courseid);
                if ($enrol->can_add_instance($course->id)) {
                    $instanceid = $enrol->add_instance($course, array(
                            'roleid' => $studentrole->id,
                            'customint1' => $btcourse->cohortid,
                            'customint2' => $btcourse->groupid
                    ));
                    if ($instanceid) {
                        $DB->set_field(btcourse::$dbname, 'enrolinstanceid', $instanceid, array('id' => $btcourse->id));
                    }
                }
            }
        }

    }

}