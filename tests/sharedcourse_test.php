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
 * File containing tests for client.
 *
 * @package     local_providerapi
 * @category    test
 * @copyright   2019 Çağlar MERSİNLİ <ceremy@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_providerapi\local\course\course;

defined('MOODLE_INTERNAL') || die();

/**
 * The client test class.
 *
 * @package    local_providerapi
 * @copyright  2019 Çağlar MERSİNLİ <ceremy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_providerapi_sharedcourse_testcase extends advanced_testcase {

    // Write the tests here as public funcions.
    /**
     * @throws dml_exception
     */
    public function test_sharedcourse_created() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $institution = $providergenerator->create_institution();

        $this->assertNotEmpty($institution);

        $course1 = $generator->create_course();
        $course2 = $generator->create_course();

        $providergenerator->create_sharedcourse(array(
                'institutionid' => $institution->id,
                'courseids' => array($course1->id, $course2->id)
        ));

        $sharedcourse1 = $DB->get_record('local_providerapi_courses',
                array('institutionid' => $institution->id, 'courseid' => $course1->id));
        $sharedcourse2 = $DB->get_record('local_providerapi_courses',
                array('institutionid' => $institution->id, 'courseid' => $course2->id));
        $this->assertNotEmpty($sharedcourse1);
        $this->assertNotEmpty($sharedcourse2);
        $this->assertEquals($sharedcourse1->createrid, 2);
        $this->assertEquals($sharedcourse2->createrid, 2);
        $this->assertNotEmpty($sharedcourse1->timecreated);
        $this->assertNotEmpty($sharedcourse2->timecreated);

    }

    /**
     * @throws coding_exception
     */
    public function test_sharedcourse_eventcreated() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $institution = $providergenerator->create_institution();
        $course1 = $generator->create_course();
        // Catch Events.
        $sink = $this->redirectEvents();

        $providergenerator->create_sharedcourse(array(
                'institutionid' => $institution->id,
                'courseids' => array($course1->id)
        ));
        $events = $sink->get_events();
        $sink->close();
        // Validate the event.
        $this->assertCount(1, $events);
        $event = $events[0];
        $this->assertInstanceOf('\local_providerapi\event\sharedcourse_created', $event);
        $this->assertEquals(course::$dbname, $event->objecttable);
        $sharedcourse1 = $DB->get_record('local_providerapi_courses',
                array('institutionid' => $institution->id, 'courseid' => $course1->id));
        $this->assertEquals($sharedcourse1->id, $event->objectid);

    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_sharedcourse_deleted() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $institution = $providergenerator->create_institution();

        $this->assertNotEmpty($institution);
        $course1 = $generator->create_course();

        $providergenerator->create_sharedcourse(array(
                'institutionid' => $institution->id,
                'courseids' => array($course1->id)
        ));

        $sharedcourse1 = $DB->get_record('local_providerapi_courses',
                array('institutionid' => $institution->id, 'courseid' => $course1->id));
        $result = course::delete($sharedcourse1->id);

        $this->assertTrue($result);
        $this->assertFalse($DB->record_exists(course::$dbname, array('id' => $sharedcourse1->id)));

    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_sharedcourse_eventdeleted() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $institution = $providergenerator->create_institution();
        $course1 = $generator->create_course();
        $providergenerator->create_sharedcourse(array(
                'institutionid' => $institution->id,
                'courseids' => array($course1->id)
        ));
        $sharedcourse1 = $DB->get_record('local_providerapi_courses',
                array('institutionid' => $institution->id, 'courseid' => $course1->id));
        // Catch Events.
        $sink = $this->redirectEvents();

        course::delete($sharedcourse1->id);
        $events = $sink->get_events();
        $sink->close();
        // Validate the event.
        $this->assertCount(1, $events);
        $event = $events[0];
        $this->assertInstanceOf('\local_providerapi\event\sharedcourse_deleted', $event);
        $this->assertEquals(course::$dbname, $event->objecttable);
        $this->assertEquals($sharedcourse1->id, $event->objectid);

    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_sharedcourse_deletecourse() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $institution = $providergenerator->create_institution();
        $course1 = $generator->create_course();
        $providergenerator->create_sharedcourse(array(
                'institutionid' => $institution->id,
                'courseids' => array($course1->id)
        ));

        delete_course($course1->id, false);
        $this->assertFalse($DB->record_exists(course::$dbname, array('courseid' => $course1->id)));

    }

}
