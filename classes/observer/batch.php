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

use core\event\enrol_instance_deleted;
use core\event\group_deleted;
use local_providerapi\event\batch_created;
use local_providerapi\event\batch_deleted;
use local_providerapi\event\batch_updated;
use local_providerapi\event\btcourse_deleted;
use local_providerapi\local\batch\batch;
use local_providerapi\local\batch\btcourse;
use local_providerapi\local\cohortHelper;
use local_providerapi\local\enrolHelper;
use local_providerapi\local\groupHelper;

defined('MOODLE_INTERNAL') || die();

/**
 * @param batch_created $event
 * @throws coding_exception
 * @throws dml_exception
 */
function batchcreated(batch_created $event) {
    global $DB;
    $batchid = $event->objectid;
    $batch = batch::get($batchid);
    // Create cohort.
    $cohortid = $batch->createcohort();
    $DB->set_field(batch::$dbname, 'cohortid', $cohortid, array('id' => $batchid));
}

/**
 * @param batch_updated $event
 * @throws coding_exception
 * @throws dml_exception
 */
function batchupdated(batch_updated $event) {
    $batchid = $event->objectid;
    $batch = batch::get($batchid);
    // Create cohort.
    $batch->updatecohort();
    // Group name Updated.
    groupHelper::update_group_instance_names($batchid);

}

/**
 * @param batch_deleted $event
 * @throws coding_exception
 * @throws dml_exception
 */
function batchdeleted(batch_deleted $event) {
    $cohortid = $event->other['cohortid'];
    if (!empty($cohortid)) {
        cohortHelper::delete($cohortid);
    }

    // Delete allbtcourse.
    btcourse::delete_from_batchid($event->objectid);

}

/**
 * @param btcourse_deleted $event
 */
function btcoursedeleted(btcourse_deleted $event) {
    global $DB;
    $groupid = $event->other['groupid'];
    if ($groupid) {
        groupHelper::delete_group($groupid);
    }
    $enrolinstanceid = $event->other['enrolinstanceid'];
    if ($enrolinstanceid) {
        $enrol = enrolHelper::instance('cohort')->get_enrol();
        $enrolinstance = $DB->get_record('enrol', array('id' => $enrolinstanceid));
        if ($enrolinstance && $enrol->can_delete_instance($enrolinstance)) {
            $enrol->delete_instance($enrolinstance);
        }
    }
}

/**
 * @param group_deleted $event
 * @throws dml_exception
 */
function groupdeleted(group_deleted $event) {
    global $DB;
    $groupid = $event->objectid;
    $btcourserecord = $DB->get_record(btcourse::$dbname, array('groupid' => $groupid), 'id');
    if ($btcourserecord) {
        $DB->set_field(btcourse::$dbname, 'groupid', null, array('id' => $btcourserecord->id));
    }
}

/**
 * @param enrol_instance_deleted $event
 */
function enrolinstancedeleted(enrol_instance_deleted $event) {
    global $DB;
    $enrolinstanceid = $event->objectid;
    $btcourserecord = $DB->get_record(btcourse::$dbname, array('enrolinstanceid' => $enrolinstanceid), 'id');
    if ($btcourserecord) {
        $DB->set_field(btcourse::$dbname, 'enrolinstanceid', null, array('id' => $btcourserecord->id));
    }
}




