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

use local_providerapi\local\modelbase;
use local_providerapi\local\navigation;

defined('MOODLE_INTERNAL') || die();

/**
 * department class
 *
 * long_description
 *
 * @package    local_providerapi
 * @copyright  2019 çağlar MERSİNLİ <ceremy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class institution extends modelbase {
    use navigation;

    protected static $dbname = "local_providerapi_companies";

    protected static $pages = array(/* 'main' => array(
                    'url' => '/local/providerapi/modules/institution/index.php',
                    'text' => 'institutions',
                    'icon' => '',
            ),*/

    );

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
     * yeni kayıt için event olayı yazılacak
     *
     * @param $id
     *
     */
    protected function create_event($id) {
        // TODO: Implement create_event() method.
    }

    /**
     * güncelleme için event olayı yazılacak
     *
     * @param $id
     *
     */
    protected function update_event($id) {
        // TODO: Implement update_event() method.
    }

    /**
     * silme için event olayı yazılacak
     *
     * @param $id
     *
     */
    protected function delete_event($id) {
        // TODO: Implement delete_event() method.
    }
}

