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

use local_providerapi\local\batch\batch;
use local_providerapi\local\btcourse;
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

        if (!array_key_exists('name', $record)) {
            $record['name'] = 'testinstitution';
        }
        if (!array_key_exists('shortname', $record)) {
            $record['shortname'] = 'ABC';
        }
        if (!array_key_exists('secretkey', $record)) {
            $record['secretkey'] = '123456';
        }
        if (!array_key_exists('description', $record)) {
            $record['description'] = 'test description';
        }
        if (!array_key_exists('descriptionformat', $record)) {
            $record['descriptionformat'] = FORMAT_HTML;
        }

        $data = (object) $record;
        $id = institution::get($data)->create();
        return institution::get($id);
    }

    /**
     * @param array $record
     * @return batch
     * @throws dml_exception
     */
    public function create_batch($record = array()) {

        if (!array_key_exists('institutionid', $record)) {
            throw new \moodle_exception('requiredproperty', 'local_providerapi', null, 'institutionid');
        }
        if (!array_key_exists('name', $record)) {
            $record['name'] = 'testbatch';
        }
        if (!array_key_exists('capacity', $record)) {
            $record['capacity'] = 23;
        }

        $data = (object) $record;
        $id = batch::get($data)->create();
        return batch::get($id);
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

    /**
     * @param int $batchid
     * @param array $sharedcourseids
     * @param string $source
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function assign_btcourses(int $batchid, array $sharedcourseids, $source = PROVIDERAPI_SOURCEWEB) {
        global $DB, $USER;

        if (empty($sharedcourseids)) {
            throw new moodle_exception('requiredproperty', 'local_providerapi', 'sharedcourseids');
        }
        $now = time();
        $data = new stdClass();
        $data->batchid = $batchid;
        $data->source = $source;
        $data->createrid = $USER->id;
        $data->modifiedby = $USER->id;
        $data->timecreated = $now;
        $data->timemodified = $now;
        foreach ($sharedcourseids as $sharedcourseid) {
            $record = fullclone($data);
            $record->sharedcourseid = $sharedcourseid;
            btcourse::get($record)->create();
        }
    }

    /**
     * @throws dml_exception
     * @throws dml_transaction_exception
     * @throws moodle_exception
     * @return stdClass
     */
    public function generate_btcourse() {
        global $DB;
        $institution = $this->create_institution();
        $generator = $this->datagenerator;
        $course1 = $generator->create_course();
        $this->create_sharedcourse(array(
                'institutionid' => $institution->id,
                'courseids' => array($course1->id)
        ));
        $sharedcourse1 = $DB->get_record('local_providerapi_courses',
                array('institutionid' => $institution->id, 'courseid' => $course1->id));
        $batch1 = $this->create_batch(array(
                'institutionid' => $institution->id,
                'testbach2'
        ));
        $this->assign_btcourses($batch1->id, array($sharedcourse1->id));
        return $DB->get_record($batch1->btcoursedbname, array('batchid' => $batch1->id, 'sharedcourseid' => $sharedcourse1->id));
    }

}