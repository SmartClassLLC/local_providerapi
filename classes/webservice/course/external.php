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

use coding_exception;
use context_course;
use context_system;
use core_user;
use dml_exception;
use dml_transaction_exception;
use external_api;
use external_format_value;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use external_warnings;
use grade_plugin_return;
use grade_report_user;
use graded_users_iterator;
use invalid_parameter_exception;
use local_providerapi\event\btcourse_deleted;
use local_providerapi\local\batch\batch;
use local_providerapi\local\batch\btcourse;
use local_providerapi\local\cohortHelper;
use local_providerapi\local\course\course;
use local_providerapi\local\groupHelper;
use local_providerapi\local\helper;
use local_providerapi\local\institution\institution;
use moodle_exception;
use required_capability_exception;
use restricted_context_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . '/local/providerapi/locallib.php');

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
            $courseids = $DB->get_records_sql("SELECT c.* FROM {$from} WHERE {$wheres}", $params);

            // Lti info attached.
            @array_walk($courseids, function(&$v, &$k) {
                $tool = helper::get_tool_by_courseid($v->id, IGNORE_MISSING);
                if ($tool) {
                    $v->launchurl = helper::get_launch_url($tool->id)->out(false);
                    $v->proxyurl = helper::get_proxy_url($tool)->out(false);
                    $v->cartridgeurl = helper::get_cartridge_url($tool)->out(false);
                    $v->secret = $tool->secret;
                }

            });
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
                                'launchurl' => new external_value(PARAM_URL, 'lti v1 launchurl', VALUE_OPTIONAL),
                                'proxyurl' => new external_value(PARAM_URL, 'lti v2 proxyurl', VALUE_OPTIONAL),
                                'cartridgeurl' => new external_value(PARAM_URL, 'cartridgeurl', VALUE_OPTIONAL),
                                'secret' => new external_value(PARAM_TEXT, 'cartridgeurl', VALUE_OPTIONAL),
                        ), 'course', VALUE_OPTIONAL));
    }

    /**
     * @return external_function_parameters
     */
    public static function assign_course_to_batch_parameters() {
        $coursefields = [
                'courseid' => new external_value(PARAM_TEXT, 'Moodle course id')
        ];
        return new external_function_parameters(
                ['institutionkey' => new external_value(PARAM_ALPHANUM, 'Institution SecretKey'),
                        'batchid' => new external_value(PARAM_INT, 'Batch\'s id'),
                        'courses' => new external_multiple_structure(
                                new external_single_structure($coursefields)
                        )
                ]
        );
    }

    /**
     * @param $institutionkey
     * @param $batchid
     * @param array $courses
     * @return array
     * @throws dml_exception
     * @throws dml_transaction_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     * @throws restricted_context_exception
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
        foreach ($params['courses'] as $course) {
            if (!$DB->record_exists('course', array('id' => $course['courseid']))) {
                $courses[] = array('courseid' => $course['courseid'], 'status' => false,
                        'message' => 'This course does not exist in moodle');
                continue;
            }
            if ($sharedcourse =
                    $DB->get_record(course::$dbname, array('courseid' => $course['courseid'], 'institutionid' => $institution->id),
                            'id')) {
                if ($DB->record_exists(btcourse::$dbname,
                        array('sharedcourseid' => $sharedcourse->id, 'batchid' => $params['batchid']))) {
                    $courses[] = array('courseid' => $course['courseid'], 'status' => false,
                            'message' => 'This course already exist in this batch');
                    continue;
                }

                $data = new stdClass();
                $data->batchid = $params['batchid'];
                $data->sharedcourseids = array($sharedcourse->id);
                $data->source = PROVIDERAPI_SOURCEWS;
                btcourse::get($data)->create();
                $courses[] = array('courseid' => $course['courseid'], 'status' => true, 'message' => 'assign successfuly');
            } else {
                $courses[] = array('courseid' => $course['courseid'], 'status' => false,
                        'message' => 'the course does not exist in this institution');
            }

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
                                'courseid' => new external_value(PARAM_INT, 'Moodle course id'),
                                'status' => new external_value(PARAM_BOOL, 'status'),
                                'message' => new external_value(PARAM_TEXT, 'information', VALUE_OPTIONAL)
                        )
                )
        );
    }

    /**
     * @return external_function_parameters
     */
    public static function unassign_course_to_batch_parameters() {
        $coursefields = [
                'courseid' => new external_value(PARAM_TEXT, 'Moodle course id')
        ];
        return new external_function_parameters(
                ['institutionkey' => new external_value(PARAM_ALPHANUM, 'Institution SecretKey'),
                        'batchid' => new external_value(PARAM_INT, 'Batch\'s id'),
                        'courses' => new external_multiple_structure(
                                new external_single_structure($coursefields)
                        )
                ]
        );
    }

    /**
     * @param $institutionkey
     * @param $batchid
     * @param array $courses
     * @return array
     * @throws dml_exception
     * @throws dml_transaction_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     * @throws restricted_context_exception
     * @throws moodle_exception
     */
    public static function unassign_course_to_batch($institutionkey, $batchid, $courses = array()) {
        global $DB;
        $params = self::validate_parameters(self::unassign_course_to_batch_parameters(),
                array('institutionkey' => $institutionkey, 'batchid' => $batchid, 'courses' => $courses));
        $context = context_system::instance();
        require_capability('local/providerapi:unassignbtcourse', $context);
        self::validate_context($context);
        // Get institution.
        $institution = institution::get_by_secretkey($params['institutionkey']);

        if (!$DB->record_exists(batch::$dbname, array('id' => $params['batchid'], 'institutionid' => $institution->id))) {
            throw new moodle_exception('notexistbatch', 'local_providerapi');
        }

        $courses = array();
        $transaction = $DB->start_delegated_transaction();
        foreach ($params['courses'] as $course) {
            if (!$DB->record_exists('course', array('id' => $course['courseid']))) {
                $courses[] = array('courseid' => $course['courseid'], 'status' => false,
                        'message' => 'This course does not exist in moodle');
                continue;
            }
            if ($sharedcourse =
                    $DB->get_record(course::$dbname, array('courseid' => $course['courseid'], 'institutionid' => $institution->id),
                            'id')) {
                if (!$record = $DB->get_record(btcourse::$dbname,
                        array('sharedcourseid' => $sharedcourse->id, 'batchid' => $params['batchid']))) {
                    $courses[] = array('courseid' => $course['courseid'], 'status' => false,
                            'message' => 'This course already not exist in this batch');
                    continue;
                }
                if ($DB->delete_records(btcourse::$dbname,
                        array('sharedcourseid' => $sharedcourse->id, 'batchid' => $params['batchid']))) {
                    btcourse_deleted::create_from_objectid($record)->trigger();
                    $courses[] = array('courseid' => $course['courseid'], 'status' => true, 'message' => 'unassign successfuly');
                } else {
                    $courses[] = array('courseid' => $course['courseid'], 'status' => false, 'message' => 'something went wrong');
                }

            } else {
                $courses[] = array('courseid' => $course['courseid'], 'status' => false,
                        'message' => 'the course does not exist in this institution');
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
                                'courseid' => new external_value(PARAM_INT, 'Moodle courseid'),
                                'status' => new external_value(PARAM_BOOL, 'status'),
                                'message' => new external_value(PARAM_TEXT, 'information', VALUE_OPTIONAL)
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
     * @throws dml_exception
     * @throws dml_transaction_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     * @throws restricted_context_exception
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
                                'courseid' => new external_value(PARAM_INT, 'Moodle Course id'),
                                'coursename' => new external_value(PARAM_TEXT, 'Moodle course name')
                        ), 'Get batch\'s courses', VALUE_OPTIONAL
                )
        );
    }

    /**
     * @return external_function_parameters
     */
    public static function get_lti_info_parameters() {
        return new external_function_parameters(
                ['institutionkey' => new external_value(PARAM_ALPHANUM, 'Institution SecretKey'),
                        'courseid' => new external_value(PARAM_INT, 'Moodle Courseid')
                ]
        );
    }

    /**
     * @param $institutionkey
     * @param $courseid
     * @return array
     * @throws dml_exception
     * @throws dml_transaction_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     * @throws restricted_context_exception
     * @throws moodle_exception
     */
    public static function get_lti_info($institutionkey, $courseid) {
        global $DB;
        $params = self::validate_parameters(self::get_lti_info_parameters(),
                array('institutionkey' => $institutionkey, 'courseid' => $courseid));
        $context = context_system::instance();
        require_capability('local/providerapi:get_lti_info', $context);
        self::validate_context($context);
        // Get institution.
        institution::get_by_secretkey($params['institutionkey']);

        if (!$DB->record_exists('course', array('id' => $params['courseid']))) {
            throw new moodle_exception('notexistcourse', 'local_providerapi');
        }
        $tool = helper::get_tool_by_courseid($params['courseid']);
        if (!$tool) {
            throw new moodle_exception('notexistcourselti', 'local_providerapi');
        }
        $toolinfo = array();
        $toolinfo['launchurl'] = helper::get_launch_url($tool->id)->out(false);
        $toolinfo['proxyurl'] = helper::get_proxy_url($tool)->out(false);
        $toolinfo['cartridgeurl'] = helper::get_cartridge_url($tool)->out(false);
        $toolinfo['secret'] = $tool->secret;
        return $toolinfo;
    }

    /**
     * @return external_single_structure
     */
    public static function get_lti_info_returns() {
        return new external_single_structure(
                array(
                        'launchurl' => new external_value(PARAM_URL, 'Lti launch url for ltiv1'),
                        'proxyurl' => new external_value(PARAM_URL, 'Lti proxyurl for ltiv2'),
                        'cartridgeurl' => new external_value(PARAM_URL, 'Lti cartridgeurl'),
                        'secret' => new external_value(PARAM_TEXT, 'Lti secret')
                ), 'Get course\'s Lti info', VALUE_OPTIONAL
        );
    }

    /**
     * @return external_function_parameters
     */
    public static function manual_enrol_parameters() {
        $enrolfields = [
                'userid' => new external_value(PARAM_INT, 'Moodle user id'),
                'courseid' => new external_value(PARAM_INT, 'Moodle Course id'),
                'batchid' => new external_value(PARAM_INT, 'Batch id'),
                'roleshortname' => new external_value(PARAM_TEXT,
                        "Moodle role Shortname\n Exp: manager,teacher,editingteacher,student ")
        ];
        return new external_function_parameters(
                ['institutionkey' => new external_value(PARAM_ALPHANUM, 'Institution SecretKey'),
                        'users' => new external_multiple_structure(
                                new external_single_structure($enrolfields)
                        )
                ]
        );
    }

    /**
     * @param string $institutionkey
     * @param array $users
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws dml_transaction_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     * @throws restricted_context_exception
     * @throws moodle_exception
     */
    public static function manual_enrol($institutionkey, $users = array()) {
        global $DB, $CFG;
        require_once($CFG->libdir . '/enrollib.php');
        require_once($CFG->libdir . '/grouplib.php');
        require_once($CFG->dirroot . '/group/lib.php');
        $params = self::validate_parameters(self::manual_enrol_parameters(),
                array('institutionkey' => $institutionkey, 'users' => $users));

        // Retrieve the manual enrolment plugin.
        $enrol = enrol_get_plugin('manual');
        if (empty($enrol)) {
            throw new moodle_exception('manualpluginnotinstalled', 'enrol_manual');
        }
        $results = array();
        // Get institution.
        $institution = institution::get_by_secretkey($params['institutionkey']);
        $transaction = $DB->start_delegated_transaction();
        foreach ($params['users'] as $user) {

            // Check Capacity.
            $context = context_course::instance($user['courseid']);

            self::validate_context($context);

            require_capability('enrol/manual:enrol', $context);
            // Is there the course in institution.

            if (!$DB->record_exists('local_providerapi_courses',
                    array('institutionid' => $institution->id, 'courseid' => $user['courseid']))) {
                throw new moodle_exception('notexistcourse', 'local_providerapi');
            }
            // Is there the batch in institution.
            if (!$DB->record_exists('local_providerapi_batches',
                    array('institutionid' => $institution->id, 'id' => $user['batchid']))) {
                throw new moodle_exception('notexistbatch', 'local_providerapi');
            }
            // The user member of institution?
            if (!cohortHelper::is_member($institution->cohortid, $user['userid'])) {
                throw new moodle_exception('notexistuser', 'local_providerapi');
            }
            $sql = "SELECT btc.*
                    FROM {local_providerapi_btcourses} btc
                    JOIN {local_providerapi_courses} c ON c.id = btc.sharedcourseid
                    WHERE btc.batchid = :batchid
                    AND c.courseid = :courseid ";
            if (!$btcourse = $DB->get_record_sql($sql, array('batchid' => $user['batchid'], 'courseid' => $user['courseid']))) {
                throw new moodle_exception('notexistcourseinbatch', 'local_providerapi');
            }
            // What if couse has not group yet.very less possibility.
            if (empty($btcourse->groupid)) {
                $formattedname = $btcourse->istitutionname . ' (' . $btcourse->batchname . ')';
                $data = new stdClass();
                $data->name = $formattedname;
                $data->courseid = $btcourse->courseid;
                $groupid = groupHelper::create_group($data);
                if ($groupid) {
                    $DB->set_field(btcourse::$dbname, 'groupid', $groupid, array('id' => $btcourse->id));
                }
            }
            // Get roleid from Ws.
            if (!$roleid = $DB->get_field('role', 'id', array('shortname' => $user['roleshortname']))) {
                throw new moodle_exception('notexistrole', 'local_providerapi');
            }

            // Throw an exception if user is not able to assign the role.
            $roles = get_assignable_roles($context);
            if (!array_key_exists($roleid, $roles)) {
                $errorparams = new stdClass();
                $errorparams->roleid = $roleid;
                $errorparams->courseid = $user['courseid'];
                $errorparams->userid = $user['userid'];
                throw new moodle_exception('wsusercannotassign', 'enrol_manual', '', $errorparams);
            }

            // Check manual enrolment plugin instance is enabled/exist.
            $instance = null;
            $enrolinstances = enrol_get_instances($user['courseid'], true);
            foreach ($enrolinstances as $courseenrolinstance) {
                if ($courseenrolinstance->enrol == "manual") {
                    $instance = $courseenrolinstance;
                    break;
                }
            }
            if (empty($instance)) {
                $errorparams = new stdClass();
                $errorparams->courseid = $user['courseid'];
                throw new moodle_exception('wsnoinstance', 'enrol_manual', $errorparams);
            }

            // Check that the plugin accept enrolment (it should always the case, it's hard coded in the plugin).
            if (!$enrol->allow_enrol($instance)) {
                $errorparams = new stdClass();
                $errorparams->roleid = $roleid;
                $errorparams->courseid = $user['courseid'];
                $errorparams->userid = $user['userid'];
                throw new moodle_exception('wscannotenrol', 'enrol_manual', '', $errorparams);
            }

            $enrol->enrol_user($instance, $user['userid'], $roleid);
            // Add batch group.
            $result = array();
            $result['userid'] = $user['userid'];
            $result['courseid'] = $user['courseid'];
            $result['enrolstatus'] = true;
            if (groups_add_member($btcourse->groupid, $user['userid'], 'enrol_manual', $instance->id)) {
                $result['groupstatus'] = true;
            } else {
                $result['groupstatus'] = false;
            }
            $results[] = $result;

        }
        $transaction->allow_commit();
        return $results;

    }

    /**
     * @return external_multiple_structure
     */
    public static function manual_enrol_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                                'userid' => new external_value(PARAM_INT, 'user id'),
                                'courseid' => new external_value(PARAM_INT, 'course id'),
                                'enrolstatus' => new external_value(PARAM_BOOL, 'Course enrol status'),
                                'groupstatus' => new external_value(PARAM_BOOL, 'Course group status')
                        )
                ), 'manual enrol status info', VALUE_OPTIONAL
        );
    }

    /**
     * @return external_function_parameters
     */
    public static function manual_unenrol_parameters() {
        $enrolfields = [
                'userid' => new external_value(PARAM_INT, 'Moodle user id'),
                'courseid' => new external_value(PARAM_INT, 'Moodle Course id'),
                'batchid' => new external_value(PARAM_INT,
                        'Batch id. The user full unenrol the course when the batchid is null.Otherwise just unenrol batch\'s group',
                        VALUE_OPTIONAL)
        ];
        return new external_function_parameters(
                ['institutionkey' => new external_value(PARAM_ALPHANUM, 'Institution SecretKey'),
                        'users' => new external_multiple_structure(
                                new external_single_structure($enrolfields)
                        )
                ]
        );
    }

    /**
     * @param $institutionkey
     * @param array $users
     * @throws coding_exception
     * @throws dml_exception
     * @throws dml_transaction_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws required_capability_exception
     * @throws restricted_context_exception
     */
    public static function manual_unenrol($institutionkey, $users = array()) {
        global $DB, $CFG;
        require_once($CFG->libdir . '/enrollib.php');
        require_once($CFG->libdir . '/grouplib.php');
        require_once($CFG->dirroot . '/group/lib.php');
        $params = self::validate_parameters(self::manual_unenrol_parameters(),
                array('institutionkey' => $institutionkey, 'users' => $users));

        // Retrieve the manual enrolment plugin.
        $enrol = enrol_get_plugin('manual');
        if (empty($enrol)) {
            throw new moodle_exception('manualpluginnotinstalled', 'enrol_manual');
        }

        // Get institution.
        $institution = institution::get_by_secretkey($params['institutionkey']);
        $transaction = $DB->start_delegated_transaction();
        foreach ($params['users'] as $user) {

            // Check Capacity.
            $context = context_course::instance($user['courseid']);

            self::validate_context($context);

            require_capability('enrol/manual:unenrol', $context);
            // Is there the course in institution.
            if (!$DB->record_exists('local_providerapi_courses',
                    array('institutionid' => $institution->id, 'courseid' => $user['courseid']))) {
                throw new moodle_exception('notexistcourse', 'local_providerapi');
            }
            // The user member of institution?
            if (!cohortHelper::is_member($institution->cohortid, $user['userid'])) {
                throw new moodle_exception('notexistuser', 'local_providerapi');
            }

            $instance = $DB->get_record('enrol', array('courseid' => $user['courseid'], 'enrol' => 'manual'));
            if (!$instance) {
                throw new moodle_exception('wsnoinstance', 'enrol_manual', $user);
            }
            $realuser = $DB->get_record('user', array('id' => $user['userid']));
            if (!$realuser) {
                throw new invalid_parameter_exception('User id not exist: ' . $user['userid']);
            }
            if (!empty($user['batchid'])) {
                // Is there the batch in institution.
                if (!$DB->record_exists('local_providerapi_batches',
                        array('institutionid' => $institution->id, 'id' => $user['batchid']))) {
                    throw new moodle_exception('notexistbatch', 'local_providerapi');
                }
                $sql = "SELECT btc.*
                    FROM {local_providerapi_btcourses} btc
                    JOIN {local_providerapi_courses} c ON c.id = btc.sharedcourseid
                    WHERE btc.batchid = :batchid
                    AND c.courseid = :courseid ";
                if (!$btcourse = $DB->get_record_sql($sql, array('batchid' => $user['batchid'], 'courseid' => $user['courseid']))) {
                    throw new moodle_exception('notexistcourseinbatch', 'local_providerapi');
                }
                groups_remove_member($btcourse->groupid, $user['userid']);
            } else {
                if (!$enrol->allow_unenrol($instance)) {
                    throw new moodle_exception('wscannotunenrol', 'enrol_manual', '', $user);
                }
                $enrol->unenrol_user($instance, $user['userid']);
            }

        }
        $transaction->allow_commit();

    }

    /**
     * @return null
     */
    public static function manual_unenrol_returns() {
        return null;
    }

    /**
     * @return external_function_parameters
     */
    public static function get_grade_items_parameters() {
        return new external_function_parameters(
                array(
                        'institutionkey' => new external_value(PARAM_ALPHANUM, 'Institution Key'),
                        'courseid' => new external_value(PARAM_INT, 'Course Id'),
                        'userid' => new external_value(PARAM_INT, 'Return grades only for this user'),
                        'batchid' => new external_value(PARAM_INT, 'Batch id')
                )
        );
    }

    /**
     * @param $institutionkey
     * @param $courseid
     * @param $userid
     * @param $batchid
     * @return array
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    public static function get_grade_items($institutionkey, $courseid, $userid, $batchid) {
        global $DB;
        $params = self::validate_parameters(self::get_grade_items_parameters(), array(
                'institutionkey' => $institutionkey,
                'courseid' => $courseid,
                'userid' => $userid,
                'batchid' => $batchid
        ));
        $institution = institution::get_by_secretkey($params['institutionkey']);
        if (!$DB->record_exists('local_providerapi_courses',
                array('institutionid' => $institution->id, 'courseid' => $params['courseid']))) {
            throw new moodle_exception('notexistcourse', 'local_providerapi');
        }
        // The user member of institution?
        if (!cohortHelper::is_member($institution->cohortid, $params['userid'])) {
            throw new moodle_exception('notexistuser', 'local_providerapi');
        }
        $sql = "SELECT btc.*
                    FROM {local_providerapi_btcourses} btc
                    JOIN {local_providerapi_courses} c ON c.id = btc.sharedcourseid
                    WHERE btc.batchid = :batchid
                    AND c.courseid = :courseid ";
        if (!$btcourse = $DB->get_record_sql($sql, array('batchid' => $params['batchid'], 'courseid' => $params['courseid']))) {
            throw new moodle_exception('notexistcourseinbatch', 'local_providerapi');
        }
        // What if couse has not group yet.very less possibility.
        if (empty($btcourse->groupid)) {
            $formattedname = $btcourse->istitutionname . ' (' . $btcourse->batchname . ')';
            $data = new stdClass();
            $data->name = $formattedname;
            $data->courseid = $btcourse->courseid;
            $groupid = groupHelper::create_group($data);
            if ($groupid) {
                $DB->set_field(btcourse::$dbname, 'groupid', $groupid, array('id' => $btcourse->id));
            }
        }

        list($userid, $course, $context, $user, $groupid) = self::check_report_access($courseid, $userid, $btcourse->groupid);
        // We pass userid because it can be still 0.
        list($gradeitems, $warnings) = self::get_report_data($course, $context, $user, $userid, $groupid, false);
        foreach ($gradeitems as $gradeitem) {
            if (isset($gradeitem['feedback']) and isset($gradeitem['feedbackformat'])) {
                list($gradeitem['feedback'], $gradeitem['feedbackformat']) =
                        external_format_text($gradeitem['feedback'], $gradeitem['feedbackformat'], $context->id);
            }
        }

        $result = array();
        $result['usergrades'] = $gradeitems;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Describes tget_grade_items return value.
     *
     * @return external_single_structure
     * @since Moodle 3.2
     */
    public static function get_grade_items_returns() {
        return new external_single_structure(
                array(
                        'usergrades' => new external_multiple_structure(
                                new external_single_structure(
                                        array(
                                                'courseid' => new external_value(PARAM_INT, 'course id'),
                                                'userid' => new external_value(PARAM_INT, 'user id'),
                                                'userfullname' => new external_value(PARAM_TEXT, 'user fullname'),
                                                'maxdepth' => new external_value(PARAM_INT,
                                                        'table max depth (needed for printing it)'),
                                                'gradeitems' => new external_multiple_structure(
                                                        new external_single_structure(
                                                                array(
                                                                        'id' => new external_value(PARAM_INT, 'Grade item id'),
                                                                        'itemname' => new external_value(PARAM_TEXT,
                                                                                'Grade item name'),
                                                                        'itemtype' => new external_value(PARAM_ALPHA,
                                                                                'Grade item type'),
                                                                        'itemmodule' => new external_value(PARAM_PLUGIN,
                                                                                'Grade item module'),
                                                                        'iteminstance' => new external_value(PARAM_INT,
                                                                                'Grade item instance'),
                                                                        'itemnumber' => new external_value(PARAM_INT,
                                                                                'Grade item item number'),
                                                                        'categoryid' => new external_value(PARAM_INT,
                                                                                'Grade item category id'),
                                                                        'outcomeid' => new external_value(PARAM_INT, 'Outcome id'),
                                                                        'scaleid' => new external_value(PARAM_INT, 'Scale id'),
                                                                        'locked' => new external_value(PARAM_BOOL,
                                                                                'Grade item for user locked?', VALUE_OPTIONAL),
                                                                        'cmid' => new external_value(PARAM_INT,
                                                                                'Course module id (if type mod)', VALUE_OPTIONAL),
                                                                        'weightraw' => new external_value(PARAM_FLOAT, 'Weight raw',
                                                                                VALUE_OPTIONAL),
                                                                        'weightformatted' => new external_value(PARAM_NOTAGS,
                                                                                'Weight', VALUE_OPTIONAL),
                                                                        'status' => new external_value(PARAM_ALPHA, 'Status',
                                                                                VALUE_OPTIONAL),
                                                                        'graderaw' => new external_value(PARAM_FLOAT, 'Grade raw',
                                                                                VALUE_OPTIONAL),
                                                                        'gradedatesubmitted' => new external_value(PARAM_INT,
                                                                                'Grade submit date', VALUE_OPTIONAL),
                                                                        'gradedategraded' => new external_value(PARAM_INT,
                                                                                'Grade graded date', VALUE_OPTIONAL),
                                                                        'gradehiddenbydate' => new external_value(PARAM_BOOL,
                                                                                'Grade hidden by date?', VALUE_OPTIONAL),
                                                                        'gradeneedsupdate' => new external_value(PARAM_BOOL,
                                                                                'Grade needs update?', VALUE_OPTIONAL),
                                                                        'gradeishidden' => new external_value(PARAM_BOOL,
                                                                                'Grade is hidden?', VALUE_OPTIONAL),
                                                                        'gradeislocked' => new external_value(PARAM_BOOL,
                                                                                'Grade is locked?', VALUE_OPTIONAL),
                                                                        'gradeisoverridden' => new external_value(PARAM_BOOL,
                                                                                'Grade overridden?', VALUE_OPTIONAL),
                                                                        'gradeformatted' => new external_value(PARAM_NOTAGS,
                                                                                'The grade formatted', VALUE_OPTIONAL),
                                                                        'grademin' => new external_value(PARAM_FLOAT, 'Grade min',
                                                                                VALUE_OPTIONAL),
                                                                        'grademax' => new external_value(PARAM_FLOAT, 'Grade max',
                                                                                VALUE_OPTIONAL),
                                                                        'rangeformatted' => new external_value(PARAM_NOTAGS,
                                                                                'Range formatted', VALUE_OPTIONAL),
                                                                        'percentageformatted' => new external_value(PARAM_NOTAGS,
                                                                                'Percentage', VALUE_OPTIONAL),
                                                                        'lettergradeformatted' => new external_value(PARAM_NOTAGS,
                                                                                'Letter grade', VALUE_OPTIONAL),
                                                                        'rank' => new external_value(PARAM_INT,
                                                                                'Rank in the course', VALUE_OPTIONAL),
                                                                        'numusers' => new external_value(PARAM_INT,
                                                                                'Num users in course', VALUE_OPTIONAL),
                                                                        'averageformatted' => new external_value(PARAM_NOTAGS,
                                                                                'Grade average', VALUE_OPTIONAL),
                                                                        'feedback' => new external_value(PARAM_RAW,
                                                                                'Grade feedback', VALUE_OPTIONAL),
                                                                        'feedbackformat' => new external_format_value('feedback'),
                                                                ), 'Grade items'
                                                        )
                                                )
                                        )
                                )
                        ),
                        'warnings' => new external_warnings()
                )
        );
    }

    /**
     * Validate access permissions to the report
     *
     * @param int $courseid the courseid
     * @param int $userid the user id to retrieve data from
     * @param int $groupid the group id
     * @return array with the parameters cleaned and other required information
     * @since  Moodle 3.2
     */
    protected static function check_report_access($courseid, $userid, $groupid = 0) {
        global $USER;

        // Function get_course internally throws an exception if the course doesn't exist.
        $course = get_course($courseid);

        $context = context_course::instance($courseid);
        self::validate_context($context);

        // Specific capabilities.
        require_capability('gradereport/user:view', $context);

        $user = null;

        $user = core_user::get_user($userid, '*', MUST_EXIST);
        core_user::require_active_user($user);
        // Check if we can view the user group (if any).
        // When userid == 0, we are retrieving all the users, we'll check then if a groupid is required.
        if (!groups_user_groups_visible($course, $user->id)) {
            throw new moodle_exception('notingroup');
        }

        $access = false;
        if (has_capability('moodle/grade:viewall', $context)) {
            // Can view all course grades.
            $access = true;
        }

        if (!$access) {
            throw new moodle_exception('nopermissiontoviewgrades', 'error');
        }

        if (!empty($groupid)) {
            // Determine is the group is visible to user.
            if (!groups_group_visible($groupid, $course)) {
                throw new moodle_exception('notingroup');
            }
        } else {
            // Check to see if groups are being used here.
            if ($groupmode = groups_get_course_groupmode($course)) {
                $groupid = groups_get_course_group($course);
                // Determine is the group is visible to user (this is particullary for the group 0).
                if (!groups_group_visible($groupid, $course)) {
                    throw new moodle_exception('notingroup');
                }
            } else {
                $groupid = 0;
            }
        }

        return array($userid, $course, $context, $user, $groupid);
    }

    /**
     * @param $course
     * @param $context
     * @param $user
     * @param $userid
     * @param $groupid
     * @param bool $tabledata
     * @return array
     * @throws coding_exception
     */
    protected static function get_report_data($course, $context, $user, $userid, $groupid, $tabledata = true) {
        global $CFG;

        $warnings = array();
        // Require files here to save some memory in case validation fails.
        require_once($CFG->dirroot . '/group/lib.php');
        require_once($CFG->libdir . '/gradelib.php');
        require_once($CFG->dirroot . '/grade/lib.php');
        require_once($CFG->dirroot . '/grade/report/user/lib.php');

        // Force regrade to update items marked as 'needupdate'.
        grade_regrade_final_grades($course->id);

        $gpr = new grade_plugin_return(
                array(
                        'type' => 'report',
                        'plugin' => 'user',
                        'courseid' => $course->id,
                        'userid' => $userid)
        );

        $reportdata = array();

        // Just one user.
        if ($user) {
            $report = new grade_report_user($course->id, $gpr, $context, $userid, true);
            $report->fill_table();

            $gradeuserdata = array(
                    'courseid' => $course->id,
                    'userid' => $user->id,
                    'userfullname' => fullname($user),
                    'maxdepth' => $report->maxdepth,
            );
            if ($tabledata) {
                $gradeuserdata['tabledata'] = $report->tabledata;
            } else {
                $gradeuserdata['gradeitems'] = $report->gradeitemsdata;
            }
            $reportdata[] = $gradeuserdata;
        } else {
            $defaultgradeshowactiveenrol = !empty($CFG->grade_report_showonlyactiveenrol);
            $showonlyactiveenrol = get_user_preferences('grade_report_showonlyactiveenrol', $defaultgradeshowactiveenrol);
            $showonlyactiveenrol = $showonlyactiveenrol || !has_capability('moodle/course:viewsuspendedusers', $context);

            $gui = new graded_users_iterator($course, null, $groupid);
            $gui->require_active_enrolment($showonlyactiveenrol);
            $gui->init();

            while ($userdata = $gui->next_user()) {
                $currentuser = $userdata->user;
                $report = new grade_report_user($course->id, $gpr, $context, $currentuser->id);
                $report->fill_table();

                $gradeuserdata = array(
                        'courseid' => $course->id,
                        'userid' => $currentuser->id,
                        'userfullname' => fullname($currentuser),
                        'maxdepth' => $report->maxdepth,
                );
                if ($tabledata) {
                    $gradeuserdata['tabledata'] = $report->tabledata;
                } else {
                    $gradeuserdata['gradeitems'] = $report->gradeitemsdata;
                }
                $reportdata[] = $gradeuserdata;
            }
            $gui->close();
        }
        return array($reportdata, $warnings);
    }

}