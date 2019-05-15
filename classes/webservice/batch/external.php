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
use external_api;
use external_function_parameters;
use external_value;
use local_providerapi\form\assigncourse_form;
use local_providerapi\local\batch\batch;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . "/externallib.php");

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
     * @throws \dml_transaction_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
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
            $batch = batch::get($params['batchid']);
            return $batch->assigncourse($validateddata->sharedcourseids);

        }
        return false;
    }

    /**
     * @return external_value
     */
    public static function assigncourseweb_returns() {
        return new external_value(PARAM_BOOL, 'ok');
    }

}
