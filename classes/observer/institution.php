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

use local_providerapi\event\institution_created;
use local_providerapi\event\institution_deleted;
use local_providerapi\event\institution_updated;
use local_providerapi\local\batch\batch;
use local_providerapi\local\cohortHelper;
use local_providerapi\local\course\course;
use local_providerapi\local\institution\institution;

defined('MOODLE_INTERNAL') || die();

/**
 * @param institution_created $event
 * @throws coding_exception
 * @throws dml_exception
 */
function institutioncreated(institution_created $event) {
    global $DB;
    $institutionid = $event->objectid;
    $institution = institution::get($institutionid);
    // Create cohort.
    $cohortid = $institution->createcohort();
    $DB->set_field(institution::$dbname, 'cohortid', $cohortid, array('id' => $institutionid));
}

/**
 * @param institution_updated $event
 * @throws dml_exception
 */
function institutionupdated(institution_updated $event) {
    $institutionid = $event->objectid;
    $institution = institution::get($institutionid);
    // Create cohort.
    $institution->updatecohort();

}

/**
 * @param institution_deleted $event
 * @throws coding_exception
 */
function institutiondeleted(institution_deleted $event) {
    $cohortid = $event->other['cohortid'];
    if (!empty($cohortid)) {
        cohortHelper::delete($cohortid);
    }

    // Shared Course Deleted.
    course::deletebyinstitutionid($event->objectid);

    // Delete batches.
    batch::deletebyinstitutionid($event->objectid);

}




