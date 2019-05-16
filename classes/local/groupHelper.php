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

/**
 * Class groupHelper
 *
 * @package local_providerapi\local
 */
class groupHelper {

    /**
     * @param \stdClass $data
     * @return int
     * @throws \moodle_exception
     */
    public static function create_group(\stdClass $data) {
        $data->description = 'Created by Providerapi Local Plugin.DO NOT DELETE via Web Interface ';
        $data->descriptionformat = FORMAT_HTML;
        return groups_create_group($data);
    }

    /**
     * @param \stdClass $data
     * @return bool
     * @throws \moodle_exception
     */
    public static function update_group(\stdClass $data) {
        return groups_update_group($data);
    }

    /**
     * @param int $groupid
     * @return bool
     */
    public static function delete_group(int $groupid) {
        return groups_delete_group($groupid);
    }

}