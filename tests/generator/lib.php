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

use local_providerapi\local\institution\institution;

defined('MOODLE_INTERNAL') || die();

/**
 * Class local_providerapi
 */
class local_providerapi_generator extends component_generator_base {

    /**
     * @param array $record
     * @return institution
     * @throws dml_exception
     */
    public function create_institution($record = array()) {

        if (empty($record)) {
            $record = array(
                    'name' => 'test',
                    'shortname' => 'ABC',
                    'secretkey' => '123456',
                    'description' => 'test description',
                    'descriptionformat' => FORMAT_HTML
            );
        }
        $data = (object) $record;
        $id = institution::get($data)->create();
        return institution::get($id);
    }

    /**
     * @param array $record
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function create_sharedcourse(array $record) {
        if (empty($record)) {
            return;
        }
        $data = (object) $record;
        \local_providerapi\local\course\course::create($data);
    }

}