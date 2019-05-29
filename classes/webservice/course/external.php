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
 * course webservice
 *
 * long_description
 *
 * @package    local_providerapi
 * @copyright  2019 çağlar MERSİNLİ <ceremy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_providerapi\webservice\course;

use context_system;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use local_providerapi\event\btcourse_deleted;
use local_providerapi\local\batch\batch;
use local_providerapi\local\batch\btcourse;
use local_providerapi\local\course\course;
use local_providerapi\local\institution\institution;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . "/externallib.php");

/**
 * Class external
 *
 * @package local_providerapi\webservice\course
 */
class external extends external_api {

    /**
     * @return external_function_parameters
     */
    public static function get_courses_parameters() {
        return new external_function_parameters(
                array(
                        'institutionkey' => new external_value(PARAM_ALPHANUM, 'Institution Key')
                )
        );
    }

    /**
     * @param [type] $institutionkey
     * @return array
     */
    public static function get_courses($institutionkey) {
        $params = self::validate_parameters(self::get_courses_parameters(), array(
                'institutionkey' => $institutionkey
        ));
        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
        require_capability('local/providerapi:viewassigncourse', $systemcontext);
        $courseids = array();
        if ($institution = institution::get_by_secretkey($params['institutionkey'])) {
            global $DB;
            list($select, $from, $wheres, $params) = course::get_sql($institution->id);
            $courseids = $DB->get_records_sql("SELECT sc.id AS sharedcourseid,c.* FROM {$from} WHERE {$wheres}", $params);
        }
        return $courseids;
    }

    /**
     * @return external_multiple_structure
     */
    public static function get_courses_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                                'sharedcourseid' => new external_value(PARAM_INT, 'Shared Course id'),
                                'id' => new external_value(PARAM_INT, 'course id'),
                                'shortname' => new external_value(PARAM_TEXT, 'course short name'),
                                'fullname' => new external_value(PARAM_TEXT, 'full name'),
                                'idnumber' => new external_value(PARAM_RAW, 'id number', VALUE_OPTIONAL),
                                'format' => new external_value(PARAM_PLUGIN,
                                        'course format: weeks, topics, social, site,..'),
                                'showgrades' => new external_value(PARAM_INT,
                                        '1 if grades are shown, otherwise 0', VALUE_OPTIONAL),
                                'newsitems' => new external_value(PARAM_INT,
                                        'number of recent items appearing on the course page', VALUE_OPTIONAL),
                                'startdate' => new external_value(PARAM_INT,
                                        'timestamp when the course start'),
                                'enddate' => new external_value(PARAM_INT,
                                        'timestamp when the course end'),
                                'numsections' => new external_value(PARAM_INT,
                                        '(deprecated, use courseformatoptions) number of weeks/topics',
                                        VALUE_OPTIONAL),
                                'maxbytes' => new external_value(PARAM_INT,
                                        'largest size of file that can be uploaded into the course',
                                        VALUE_OPTIONAL),
                                'showreports' => new external_value(PARAM_INT,
                                        'are activity report shown (yes = 1, no =0)', VALUE_OPTIONAL),
                                'visible' => new external_value(PARAM_INT,
                                        '1: available to student, 0:not available', VALUE_OPTIONAL),
                                'hiddensections' => new external_value(PARAM_INT,
                                        '(deprecated, use courseformatoptions)
                                         How the hidden sections in the course are displayed to students',
                                        VALUE_OPTIONAL),
                                'groupmode' => new external_value(PARAM_INT, 'no group, separate, visible',
                                        VALUE_OPTIONAL),
                                'groupmodeforce' => new external_value(PARAM_INT, '1: yes, 0: no',
                                        VALUE_OPTIONAL),
                                'defaultgroupingid' => new external_value(PARAM_INT, 'default grouping id',
                                        VALUE_OPTIONAL),
                                'timecreated' => new external_value(PARAM_INT,
                                        'timestamp when the course have been created', VALUE_OPTIONAL),
                                'timemodified' => new external_value(PARAM_INT,
                                        'timestamp when the course have been modified', VALUE_OPTIONAL),
                                'enablecompletion' => new external_value(PARAM_INT,
                                        'Enabled, control via completion and activity settings. Disbaled,
                                        not shown in activity settings.',
                                        VALUE_OPTIONAL),
                                'completionnotify' => new external_value(PARAM_INT,
                                        '1: yes 0: no', VALUE_OPTIONAL),
                                'lang' => new external_value(PARAM_SAFEDIR,
                                        'forced course language', VALUE_OPTIONAL),
                                'forcetheme' => new external_value(PARAM_PLUGIN,
                                        'name of the force theme', VALUE_OPTIONAL),
                                'courseformatoptions' => new external_multiple_structure(
                                        new external_single_structure(
                                                array('name' => new external_value(PARAM_ALPHANUMEXT, 'course format option name'),
                                                        'value' => new external_value(PARAM_RAW, 'course format option value')
                                                )),
                                        'additional options for particular course format', VALUE_OPTIONAL
                                ),
                        ), 'course', VALUE_OPTIONAL));
    }

    /**
     * @return external_function_parameters
     */
    public static function assign_course_to_batch_parameters() {
        $batchfields = [
                'sharedcourseid' => new external_value(PARAM_TEXT, 'Batch\'s name must be unique for each institution')
        ];
        return new external_function_parameters(
                ['institutionkey' => new external_value(PARAM_ALPHANUM, 'Institution SecretKey'),
                        'batchid' => new external_value(PARAM_INT, 'Batch\'s id'),
                        'courses' => new external_multiple_structure(
                                new external_single_structure($batchfields)
                        )
                ]
        );
    }

    /**
     * @param $institutionkey
     * @param $batchid
     * @param array $courses
     * @return array
     * @throws \dml_exception
     * @throws \dml_transaction_exception
     * @throws \invalid_parameter_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws moodle_exception
     */
    public static function assign_course_to_batch($institutionkey, $batchid, $courses = array()) {
        global $DB;
        $params = self::validate_parameters(self::assign_course_to_batch_parameters(),
                array('institutionkey' => $institutionkey, 'batchid' => $batchid, 'courses' => $courses));
        $context = context_system::instance();
        require_capability('local/providerapi:assignbtcourse', $context);
        self::validate_context($context);
        // Get institution.
        $institution = institution::get_by_secretkey($params['institutionkey']);

        if (!$DB->record_exists(batch::$dbname, array('id' => $params['batchid'], 'institutionid' => $institution->id))) {
            throw new moodle_exception('notexistbatch', 'local_providerapi');
        }
        $courses = array();
        $transaction = $DB->start_delegated_transaction();
        foreach ($params['courses'] as $sharecourse) {
            $data = new \stdClass();
            $data->batchid = $params['batchid'];
            $data->sharedcourseids = array_values($sharecourse);
            $data->source = PROVIDERAPI_SOURCEWS;
            btcourse::get($data)->create();
            $courses[] = array('sharedcourseid' => $sharecourse['sharedcourseid'], 'status' => true);
        }
        $transaction->allow_commit();
        return $courses;
    }

    /**
     * @return external_multiple_structure
     */
    public static function assign_course_to_batch_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                                'sharedcourseid' => new external_value(PARAM_INT, 'sharedcourseid'),
                                'status' => new external_value(PARAM_BOOL, 'status')
                        )
                )
        );
    }

    /**
     * @return external_function_parameters
     */
    public static function unassign_course_to_batch_parameters() {
        $batchfields = [
                'sharedcourseid' => new external_value(PARAM_TEXT, 'Batch\'s name must be unique for each institution')
        ];
        return new external_function_parameters(
                ['institutionkey' => new external_value(PARAM_ALPHANUM, 'Institution SecretKey'),
                        'batchid' => new external_value(PARAM_INT, 'Batch\'s id'),
                        'courses' => new external_multiple_structure(
                                new external_single_structure($batchfields)
                        )
                ]
        );
    }

    /**
     * @param $institutionkey
     * @param $batchid
     * @param array $courses
     * @return array
     * @throws \dml_exception
     * @throws \dml_transaction_exception
     * @throws \invalid_parameter_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws moodle_exception
     */
    public static function unassign_course_to_batch($institutionkey, $batchid, $courses = array()) {
        global $DB;
        $params = self::validate_parameters(self::unassign_course_to_batch_parameters(),
                array('institutionkey' => $institutionkey, 'batchid' => $batchid, 'courses' => $courses));
        $context = context_system::instance();
        require_capability('local/providerapi:assignbtcourse', $context);
        self::validate_context($context);
        // Get institution.
        $institution = institution::get_by_secretkey($params['institutionkey']);

        if (!$DB->record_exists(batch::$dbname, array('id' => $params['batchid'], 'institutionid' => $institution->id))) {
            throw new moodle_exception('notexistbatch', 'local_providerapi');
        }
        $courses = array();
        $transaction = $DB->start_delegated_transaction();
        foreach ($params['courses'] as $sharecourse) {

            if ($record = $DB->get_record(btcourse::$dbname,
                    array('sharedcourseid' => $sharecourse['sharedcourseid'], 'batchid' => $params['batchid']))) {
                if ($DB->delete_records(btcourse::$dbname,
                        array('sharedcourseid' => $sharecourse['sharedcourseid'], 'batchid' => $params['batchid']))) {
                    btcourse_deleted::create_from_objectid($record)->trigger();
                    $courses[] = array('sharedcourseid' => $sharecourse['sharedcourseid'], 'status' => true);
                } else {
                    $courses[] = array('sharedcourseid' => $sharecourse['sharedcourseid'], 'status' => false);
                }

            }

        }
        $transaction->allow_commit();
        return $courses;
    }

    /**
     * @return external_multiple_structure
     */
    public static function unassign_course_to_batch_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                                'sharedcourseid' => new external_value(PARAM_INT, 'sharedcourseid'),
                                'status' => new external_value(PARAM_BOOL, 'status')
                        )
                )
        );
    }

    /**
     * @return external_function_parameters
     */
    public static function get_batch_courses_parameters() {
        return new external_function_parameters(
                ['institutionkey' => new external_value(PARAM_ALPHANUM, 'Institution SecretKey'),
                        'batchid' => new external_value(PARAM_INT, 'Batch\'s id')
                ]
        );
    }

    /**
     * @param $institutionkey
     * @param $batchid
     * @return array
     * @throws \dml_exception
     * @throws \dml_transaction_exception
     * @throws \invalid_parameter_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws moodle_exception
     */
    public static function get_batch_courses($institutionkey, $batchid) {
        global $DB;
        $params = self::validate_parameters(self::get_batch_courses_parameters(),
                array('institutionkey' => $institutionkey, 'batchid' => $batchid));
        $context = context_system::instance();
        require_capability('local/providerapi:viewassignbtcourse', $context);
        self::validate_context($context);
        // Get institution.
        $institution = institution::get_by_secretkey($params['institutionkey']);

        if (!$DB->record_exists(batch::$dbname, array('id' => $params['batchid'], 'institutionid' => $institution->id))) {
            throw new moodle_exception('notexistbatch', 'local_providerapi');
        }

        list($select, $from, $where, $params) = btcourse::get_sql('b.id = :bid', array('bid' => $params['batchid']));

        return $DB->get_records_sql("SELECT {$select} FROM {$from} WHERE {$where} ", $params);
    }

    /**
     * @return external_multiple_structure
     */
    public static function get_batch_courses_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                                'sharedcourseid' => new external_value(PARAM_INT, 'sharedcourseid'),
                                'coursename' => new external_value(PARAM_TEXT, 'course name'),
                                'courseid' => new external_value(PARAM_TEXT, 'moodle course id'),
                        ), 'Get batch\'s courses', VALUE_OPTIONAL
                )
        );
    }

}