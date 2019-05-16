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
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/grouplib.php');
require_once($CFG->dirrot . '/group/lib.php');

class groupHelper {

    public static function create_group(int $btcourseid) {
        global $DB;
        $btcourserc = $DB->get_record('local_providerapi_btcourses', array('id' => $btcourseid), '*', MUST_EXIST);
        $batchrc = $DB->get_record('local_providerapi_batches', array('id' => $btcourserc->batchid), '*', MUST_EXIST);
        $sharedcourserc = $DB->get_record('local_providerapi_courses', array('id' => $btcourserc->sharedcourseid), '*', MUST_EXIST);
        $course = get_course($sharedcourserc->courseid);

    }

}