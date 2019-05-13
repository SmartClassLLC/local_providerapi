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
 * Plugin event observers are registered here.
 *
 * @package     local_providerapi
 * @category    event
 * @copyright   2019 Çağlar MERSİNLİ <ceremy@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// For more information about the Events API, please visit:
// https://docs.moodle.org/dev/Event_2.

$observers = array(

        array(
                'eventname' => '\core\event\course_deleted',
                'callback' => 'coursedeleted',
                'includefile' => '/local/providerapi/classes/observer/course.php',
        ),
        array(
                'eventname' => '\local_providerapi\event\institution_created',
                'callback' => 'institutioncreated',
                'includefile' => '/local/providerapi/classes/observer/institution.php',
        ),
        array(
                'eventname' => '\local_providerapi\event\institution_updated',
                'callback' => 'institutionupdated',
                'includefile' => '/local/providerapi/classes/observer/institution.php',
        ),
        array(
                'eventname' => '\local_providerapi\event\institution_deleted',
                'callback' => 'institutiondeleted',
                'includefile' => '/local/providerapi/classes/observer/institution.php',
        ),
        array(
                'eventname' => '\local_providerapi\event\batch_created',
                'callback' => 'batchcreated',
                'includefile' => '/local/providerapi/classes/observer/batch.php',
        ),
        array(
                'eventname' => '\local_providerapi\event\batch_updated',
                'callback' => 'batchupdated',
                'includefile' => '/local/providerapi/classes/observer/batch.php',
        ),
        array(
                'eventname' => '\local_providerapi\event\batch_deleted',
                'callback' => 'batchdeleted',
                'includefile' => '/local/providerapi/classes/observer/batch.php',
        )
);
