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

/*
* short_description
*
* long_description
*
* @package    local_providerapi
* @copyright  2019 çağlar MERSİNLİ <ceremy@gmail.com>
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

namespace local_providerapi\webservice\batch;

use context;
use context_system;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use invalid_parameter_exception;
use local_providerapi\form\assigncourse_form;
use local_providerapi\local\batch\batch;
use local_providerapi\local\batch\btcourse;
use local_providerapi\local\institution\institution;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . '/local/providerapi/locallib.php');

/**
 * Class external
 *
 * @package local_providerapi\webservice\batch
 */
class external extends external_api {
    /**
     *
     *
     * @return external_function_parameters
     */
    public static function assigncourseweb_parameters() {
        return new external_function_parameters(
                array(
                        'contextid' => new external_value(PARAM_INT),
                        'batchid' => new external_value(PARAM_INT),
                        'jsonformdata' => new external_value(PARAM_RAW)
                )
        );
    }

    /**
     * @param $contextid
     * @param $batchid
     * @param $jsonformdata
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     */
    public static function assigncourseweb($contextid, $batchid, $jsonformdata) {
        $params = self::validate_parameters(self::assigncourseweb_parameters(), array(
                'contextid' => $contextid,
                'batchid' => $batchid,
                'jsonformdata' => $jsonformdata
        ));
        $context = context::instance_by_id($params['contextid'], MUST_EXIST);
        self::validate_context($context);
        require_capability('local/providerapi:assigncourse', $context);
        $serialiseddata = json_decode($params['jsonformdata']);
        $data = array();
        parse_str($serialiseddata, $data);
        $mform = new assigncourse_form(null, array('batchid' => $batchid), 'post', '', null, true, $data);
        $validateddata = $mform->get_data();

        if ($validateddata) {
            unset($validateddata->id);
            btcourse::get($validateddata)->create();
            return true;
        }
        return false;
    }

    /**
     * @return external_value
     */
    public static function assigncourseweb_returns() {
        return new external_value(PARAM_BOOL, 'ok');
    }

    /**
     * @return external_function_parameters
     */
    public static function create_batches_parameters() {
        $batchfields = [
                'name' => new external_value(PARAM_TEXT, 'Batch\'s name must be unique for each institution'),
                'capacity' => new external_value(PARAM_INT, 'Batch\'s capacity must be max 3 digits', VALUE_DEFAULT, 0,
                        NULL_NOT_ALLOWED)
        ];
        return new external_function_parameters(
                ['institutionkey' => new external_value(PARAM_ALPHANUM, 'Institution SecretKey'),
                        'batches' => new external_multiple_structure(
                                new external_single_structure($batchfields)
                        )
                ]
        );
    }

    /**
     * @param string $institutionkey
     * @param array $batches
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \dml_transaction_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    public static function create_batches($institutionkey, $batches = array()) {
        global $DB;
        $params = self::validate_parameters(self::create_batches_parameters(),
                array('institutionkey' => $institutionkey, 'batches' => $batches));
        $context = context_system::instance();
        require_capability('local/providerapi:addbatch', $context);
        self::validate_context($context);

        $batches = array();
        // Get institution.
        $institution = institution::get_by_secretkey($params['institutionkey']);
        $transaction = $DB->start_delegated_transaction();
        foreach ($params['batches'] as $batch) {
            // Check Capacity.
            $capacity = $batch['capacity'];
            if ($capacity != 0 && ($capacity < 0 || strlen($capacity) > 3)) {
                throw new invalid_parameter_exception(get_string('notcorrect', 'local_providerapi') . ' Capacity : ' . $capacity);
            }
            $data = new \stdClass();
            $data->institutionid = $institution->id;
            $data->name = $batch['name'];
            $data->capacity = $batch['capacity'];
            $data->source = PROVIDERAPI_SOURCEWS;
            if (!$newbatchid = batch::get($data)->create()) {
                throw new moodle_exception('somethingwrong', 'local_providerapi');
            }
            $newbatch = batch::get($newbatchid);
            $batches[] = array('id' => $newbatch->id, 'name' => $newbatch->name);
        }
        $transaction->allow_commit();
        return $batches;

    }

    /**
     * @return external_multiple_structure
     * @throws \coding_exception
     */
    public static function create_batches_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                                'id' => new external_value(PARAM_INT, 'batch id'),
                                'name' => new external_value(PARAM_TEXT, 'batch name'),
                        )
                )
        );
    }

    /**
     * @return external_function_parameters
     */
    public static function update_batches_parameters() {
        $batchfields = [
                'id' => new external_value(PARAM_INT, 'batch \'s id'),
                'name' => new external_value(PARAM_TEXT, 'Batch\'s name must be unique for each institution'),
                'capacity' => new external_value(PARAM_INT, 'Batch\'s capacity must be max 3 digits', VALUE_DEFAULT, 0,
                        NULL_NOT_ALLOWED)
        ];
        return new external_function_parameters(
                ['institutionkey' => new external_value(PARAM_ALPHANUM, 'Institution SecretKey'),
                        'batches' => new external_multiple_structure(
                                new external_single_structure($batchfields)
                        )
                ]
        );
    }

    /**
     * @param $institutionkey
     * @param array $batches
     * @return null
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \dml_transaction_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    public static function update_batches($institutionkey, $batches = array()) {
        global $DB;
        $params = self::validate_parameters(self::update_batches_parameters(),
                array('institutionkey' => $institutionkey, 'batches' => $batches));
        $context = context_system::instance();
        require_capability('local/providerapi:editbatch', $context);
        self::validate_context($context);

        // Get institution.
        $institution = institution::get_by_secretkey($params['institutionkey']);
        $transaction = $DB->start_delegated_transaction();
        foreach ($params['batches'] as $batch) {
            // Check Capacity.
            $capacity = $batch['capacity'];
            if ($capacity != 0 && ($capacity < 0 || strlen($capacity) > 3)) {
                throw new invalid_parameter_exception(get_string('notcorrect', 'local_providerapi') . ' Capacity : ' . $capacity);
            }
            $data = new \stdClass();
            $data->id = $batch['id'];
            $data->institutionid = $institution->id;
            $data->name = $batch['name'];
            $data->capacity = $batch['capacity'];
            $data->source = PROVIDERAPI_SOURCEWS;
            batch::get($data)->update();
        }
        $transaction->allow_commit();
        return null;

    }

    /**
     * @return null
     */
    public static function update_batches_returns() {
        return null;
    }

    public static function delete_batches_parameters() {
        $batchfields = [
                'id' => new external_value(PARAM_INT, 'batch \'s id')
        ];
        return new external_function_parameters(
                ['institutionkey' => new external_value(PARAM_ALPHANUM, 'Institution SecretKey'),
                        'batches' => new external_multiple_structure(
                                new external_single_structure($batchfields)
                        )
                ]
        );
    }

    /**
     * @param $institutionkey
     * @param array $batches
     * @return null
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \dml_transaction_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    public static function delete_batches($institutionkey, $batches = array()) {
        global $DB;
        $params = self::validate_parameters(self::delete_batches_parameters(),
                array('institutionkey' => $institutionkey, 'batches' => $batches));
        $context = context_system::instance();
        require_capability('local/providerapi:deletebatch', $context);
        self::validate_context($context);

        // Get institution.
        $institution = institution::get_by_secretkey($params['institutionkey']);
        $transaction = $DB->start_delegated_transaction();
        foreach ($params['batches'] as $batch) {
            if ($DB->record_exists(batch::$dbname, array('id' => $batch['id'], 'institutionid' => $institution->id))) {
                batch::get($batch['id'])->delete();
            }
        }
        $transaction->allow_commit();
        return null;

    }

    /**
     * @return null
     */
    public static function delete_batches_returns() {
        return null;
    }

}
