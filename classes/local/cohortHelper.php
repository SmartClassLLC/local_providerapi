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
 * cohortHelper_description
 *
 * long_description
 *
 * @package    local_providerapi
 * @copyright  2019 çağlar MERSİNLİ <ceremy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_providerapi\local;

use context_system;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/cohort/lib.php');

/**
 * Class cohortHelper
 *
 * @package local_providerapi\local
 */
class cohortHelper {

    /**
     * expect name,idnumber
     *
     * @param \stdClass $data
     * @return int
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function create(\stdClass $data) {
        if (!is_object($data)) {
            throw new \coding_exception('data must be object');
        }
        $data->contextid = context_system::instance()->id;
        $data->component = 'local_providerapi';
        $data->description = get_string('createdbyplugin', 'local_providerapi');

        return cohort_add_cohort($data);
    }

    /**
     * expect id,name,idnumber
     *
     * @param \stdClass $data
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function update(\stdClass $data) {
        if (!is_object($data)) {
            throw new \coding_exception('data must be object');
        }
        $data->contextid = context_system::instance()->id;
        $data->component = 'local_providerapi';
        $data->description = get_string('createdbyplugin', 'local_providerapi');

        return cohort_update_cohort($data);
    }

    /**
     * @param int $cohortid
     * @throws \coding_exception
     */
    public static function delete(int $cohortid) {
        global $DB;
        if (!is_int($cohortid)) {
            throw new \coding_exception('data must be int');
        }
        $cohort = $DB->get_record('cohort', array('id' => $cohortid));
        if ($cohort) {
            cohort_delete_cohort($cohort);
        }
    }

}