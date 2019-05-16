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

use local_providerapi\local\batch\btcourse;
use local_providerapi\local\course\course;
use local_providerapi\local\institution\institution;

defined('MOODLE_INTERNAL') || die();

/**
 * The client test class.
 *
 * @package    local_providerapi
 * @copyright  2019 Çağlar MERSİNLİ <ceremy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_providerapi_institution_testcase extends advanced_testcase {

    // Write the tests here as public funcions.
    /**
     * @throws dml_exception
     */
    public function test_institution_created() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $institution = $providergenerator->create_institution();

        $this->assertNotEmpty($institution);

        $new = $DB->get_record('local_providerapi_companies', array('id' => $institution->id));
        $this->assertSame($institution->name, $new->name);
        $this->assertSame($institution->shortname, $new->shortname);
        $this->assertSame($institution->secretkey, $new->secretkey);
        $this->assertSame($institution->description, $new->description);
        $this->assertEquals($institution->descriptionformat, $new->descriptionformat);
        $this->assertNotEmpty($new->createrid);
        $this->assertNotEmpty($new->modifiedby);
        $this->assertNotEmpty($new->timecreated);
        $this->assertNotEmpty($new->timemodified);

        // Validate create cohort.
        $cohort = $DB->record_exists('cohort', array('id' => $institution->cohortid));
        $this->assertTrue($cohort);

    }

    /**
     * @throws coding_exception
     */
    public function test_institution_eventcreated() {
        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        // Catch Events.
        $sink = $this->redirectEvents();
        $institution = $providergenerator->create_institution();
        $events = $sink->get_events();
        $sink->close();
        // Validate the event.
        $this->assertCount(1, $events);
        $event = $events[0];
        $this->assertInstanceOf('\local_providerapi\event\institution_created', $event);
        $this->assertEquals(institution::$dbname, $event->objecttable);
        $this->assertEquals($institution->id, $event->objectid);

    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_institution_edit() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $institution = $providergenerator->create_institution();

        $this->assertNotEmpty($institution);
        $data = $institution->get_db_record();
        $data->name = 'test2';
        $data->shortname = 'CDB';
        $data->secretkey = '789456';
        institution::get($data)->update();

        $new = $DB->get_record('local_providerapi_companies', array('id' => $institution->id));
        $this->assertSame($data->name, $new->name);
        $this->assertSame($data->shortname, $new->shortname);
        $this->assertSame($data->secretkey, $new->secretkey);
        $this->assertSame($data->description, $new->description);
        $this->assertEquals($data->descriptionformat, $new->descriptionformat);
        $this->assertNotEmpty($data->createrid);
        $this->assertNotEmpty($data->modifiedby);
        $this->assertNotEmpty($data->timecreated);
        $this->assertNotEmpty($data->timemodified);

        // Validate create cohort.
        $cohort = $DB->record_exists('cohort', array('id' => $institution->cohortid, 'name' => format_string($data->name)));
        $this->assertTrue($cohort);

    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_institution_eventupdated() {
        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $institution = $providergenerator->create_institution();
        $data = $institution->get_db_record();
        $data->name = 'test2';
        $data->shortname = 'CDB';
        $data->secretkey = '789456';
        // Catch Events.
        $sink = $this->redirectEvents();
        institution::get($data)->update();
        $events = $sink->get_events();
        $sink->close();
        // Validate the event.
        $this->assertCount(1, $events);
        $event = $events[0];
        $this->assertInstanceOf('\local_providerapi\event\institution_updated', $event);
        $this->assertEquals(institution::$dbname, $event->objecttable);
        $this->assertEquals($institution->id, $event->objectid);
    }

    /**
     * @throws coding_exception
     */
    public function test_institution_eventdeleted() {
        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $institution = $providergenerator->create_institution();
        // Catch Events.
        $sink = $this->redirectEvents();
        $institution->delete();
        $events = $sink->get_events();
        $sink->close();
        // Validate the event.
        $this->assertCount(1, $events);
        $event = $events[0];
        $this->assertInstanceOf('\local_providerapi\event\institution_deleted', $event);
        $this->assertEquals(institution::$dbname, $event->objecttable);
        $this->assertEquals($institution->id, $event->objectid);
    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_institution_deleted() {
        global $DB;
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $institution = $providergenerator->create_institution();
        $this->assertTrue(institution::exist($institution->id));
        $result = $institution->delete();
        // Are u sure deleted ?
        $this->assertTrue($result);
        $this->assertFalse(institution::exist($institution->id));
        // Validate delete cohort.
        $cohort = $DB->record_exists('cohort', array('id' => $institution->cohortid));
        $this->assertFalse($cohort);
    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_relationship_deleteinstitution() {
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
        $batch1 = $providergenerator->create_batch(array(
                'institutionid' => $institution->id,
                'testbach2'
        ));
        $data = new stdClass();
        $data->batchid = $batch1->id;
        $data->source = 'web';
        $data->sharedcourseids = array($sharedcourse1->id);
        btcourse::get($data)->create();
        $institution->delete();

        $this->assertFalse($DB->record_exists(institution::$dbname, array('id' => $institution->id)));
        $this->assertFalse($DB->record_exists($batch1::$dbname, array('id' => $batch1->id)));
        $this->assertFalse($DB->record_exists('cohort', array('id' => $institution->cohortid)));
        $this->assertFalse($DB->record_exists('cohort', array('id' => $batch1->cohortid)));
        $this->assertFalse($DB->record_exists(btcourse::$dbname, array('batchid' => $batch1->id)));
        $this->assertFalse($DB->record_exists(btcourse::$dbname, array('sharedcourseid' => $sharedcourse1->id)));
        $this->assertFalse($DB->record_exists(course::$dbname, array('institutionid' => $institution->id)));

    }

}
