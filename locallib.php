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
 * Plugin internal classes, functions and constants are defined here.
 *
 * @package     local_providerapi
 * @copyright   2019 Çağlar MERSİNLİ <ceremy@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_providerapi\local\helper;

defined('MOODLE_INTERNAL') || die();

/**
 *
 */
define('PROVIDERAPI_SOURCEWS', 'ws');
/**
 *
 */
define('PROVIDERAPI_SOURCEWEB', 'web');

/**
 * Check selected institution
 *
 * @return bool|int
 */
function local_providerapi_getinstitution() {
    global $SESSION;
    if (!isset($SESSION->institution) || empty($SESSION->institution)) {
        return false;
    }
    return $SESSION->institution;
}

/**
 * @throws coding_exception
 * @throws dml_exception
 * @deprecated
 */
function local_providerapi_sync_grades() {
    global $DB, $CFG;

    require_once($CFG->dirroot . '/local/providerapi/ims-blti/OAuth.php');
    require_once($CFG->dirroot . '/local/providerapi/ims-blti/OAuthBody.php');
    require_once($CFG->dirroot . '/lib/completionlib.php');
    require_once($CFG->libdir . '/gradelib.php');
    require_once($CFG->dirroot . '/grade/querylib.php');

    // Get all the enabled tools.
    if ($tools = $DB->get_records('local_api_tools', array('gradesync' => 1))) {
        foreach ($tools as $tool) {
            mtrace("Starting - Grade sync for shared tool '$tool->id' for the course '$tool->courseid'.");

            // Variables to keep track of information to display later.
            $usercount = 0;
            $sendcount = 0;

            // We check for all the users - users can access the same tool from different consumers.
            if ($ltiusers = $DB->get_records('local_api_users', array('toolid' => $tool->id), 'lastaccess DESC')) {
                $completion = new \completion_info(get_course($tool->courseid));
                foreach ($ltiusers as $ltiuser) {
                    $mtracecontent = "for the user '$ltiuser->userid' in the tool '$tool->id' for the course " .
                            "'$tool->courseid'";

                    $usercount = $usercount + 1;

                    // Check if we do not have a serviceurl - this can happen if the sync process has an unexpected error.
                    if (empty($ltiuser->serviceurl)) {
                        mtrace("Skipping - Empty serviceurl $mtracecontent.");
                        continue;
                    }

                    // Check if we do not have a sourceid - this can happen if the sync process has an unexpected error.
                    if (empty($ltiuser->sourceid)) {
                        mtrace("Skipping - Empty sourceid $mtracecontent.");
                        continue;
                    }

                    // Need a valid context to continue.
                    if (!$context = \context::instance_by_id($tool->contextid)) {
                        mtrace("Failed - Invalid contextid '$tool->contextid' for the tool '$tool->id'.");
                        continue;
                    }

                    // Ok, let's get the grade.
                    $grade = false;
                    if ($context->contextlevel == CONTEXT_COURSE) {
                        // Check if the user did not completed the course when it was required.
                        if ($tool->gradesynccompletion && !$completion->is_course_complete($ltiuser->userid)) {
                            mtrace("Skipping - Course not completed $mtracecontent.");
                            continue;
                        }

                        // Get the grade.
                        if ($grade = grade_get_course_grade($ltiuser->userid, $tool->courseid)) {
                            $grademax = floatval($grade->item->grademax);
                            $grade = $grade->grade;
                        }
                    } else if ($context->contextlevel == CONTEXT_MODULE) {
                        $cm = get_coursemodule_from_id(false, $context->instanceid, 0, false, MUST_EXIST);

                        if ($tool->gradesynccompletion) {
                            $data = $completion->get_data($cm, false, $ltiuser->userid);
                            if ($data->completionstate != COMPLETION_COMPLETE_PASS &&
                                    $data->completionstate != COMPLETION_COMPLETE) {
                                mtrace("Skipping - Activity not completed $mtracecontent.");
                                continue;
                            }
                        }

                        $grades = grade_get_grades($cm->course, 'mod', $cm->modname, $cm->instance, $ltiuser->userid);
                        if (!empty($grades->items[0]->grades)) {
                            $grade = reset($grades->items[0]->grades);
                            if (!empty($grade->item)) {
                                $grademax = floatval($grade->item->grademax);
                            } else {
                                $grademax = floatval($grades->items[0]->grademax);
                            }
                            $grade = $grade->grade;
                        }
                    }

                    if ($grade === false || $grade === null || strlen($grade) < 1) {
                        mtrace("Skipping - Invalid grade $mtracecontent.");
                        continue;
                    }

                    // No need to be dividing by zero.
                    if (empty($grademax)) {
                        mtrace("Skipping - Invalid grade $mtracecontent.");
                        continue;
                    }

                    // Check to see if the grade has changed.
                    if (!grade_floats_different($grade, $ltiuser->lastgrade)) {
                        mtrace("Not sent - The grade $mtracecontent was not sent as the grades are the same.");
                        continue;
                    }

                    // Sync with the external system.
                    $floatgrade = $grade / $grademax;

                    $body = helper::create_service_body($ltiuser->sourceid, $floatgrade);

                    try {
                        $response = sendOAuthBodyPOST('POST', $ltiuser->serviceurl,
                                $ltiuser->consumerkey, $ltiuser->consumersecret, 'application/xml', $body);
                    } catch (\Exception $e) {
                        mtrace("Failed - The grade '$floatgrade' $mtracecontent failed to send.");
                        mtrace($e->getMessage());
                        continue;
                    }

                    if (strpos(strtolower($response), 'success') !== false) {
                        $DB->set_field('local_api_users', 'lastgrade', grade_floatval($grade), array('id' => $ltiuser->id));
                        mtrace("Success - The grade '$floatgrade' $mtracecontent was sent.");
                        $sendcount = $sendcount + 1;
                    } else {
                        mtrace("Failed - The grade '$floatgrade' $mtracecontent failed to send.");
                    }

                }
            }
            mtrace("Completed - Synced grades for tool '$tool->id' in the course '$tool->courseid'. " .
                    "Processed $usercount users; sent $sendcount grades.");
            mtrace("");
        }
    }
}

