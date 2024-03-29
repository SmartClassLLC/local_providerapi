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

use local_providerapi\local\batch\batch;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/local/providerapi/locallib.php');

/**
 * The client test class.
 *
 * @package    local_providerapi
 * @copyright  2019 Çağlar MERSİNLİ <ceremy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_providerapi_batches_testcase extends advanced_testcase {

    // Write the tests here as public funcions.
    /**
     * @throws dml_exception
     */
    public function test_batch_created() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $institution = $providergenerator->create_institution();

        $this->assertNotEmpty($institution);

        $batch1 = $providergenerator->create_batch(array(
                'institutionid' => $institution->id,
                'testbach2'
        ));
        $batch2 = $providergenerator->create_batch(array(
                'institutionid' => $institution->id,
                'name' => 'testbatch2',
                'source' => PROVIDERAPI_SOURCEWS
        ));
        $this->assertTrue(batch::validate_exist($batch1->id));
        $this->assertTrue(batch::validate_exist($batch2->id));
        $this->assertEquals($batch1->createrid, 2);
        $this->assertEquals($batch2->createrid, 2);
        $this->assertNotEmpty($batch1->timecreated);
        $this->assertNotEmpty($batch2->timecreated);
        $this->assertNotEmpty($batch1->cohortid);
        $this->assertNotEmpty($batch2->cohortid);
        $this->assertNotEmpty($batch1->source);
        $this->assertNotEmpty($batch2->source);
        $this->assertSame($batch1->source, PROVIDERAPI_SOURCEWEB);
        $this->assertSame($batch2->source, PROVIDERAPI_SOURCEWS);

        $cohort1 = $DB->get_record('cohort', array('id' => $batch1->cohortid));
        $cohort2 = $DB->get_record('cohort', array('id' => $batch2->cohortid));
        $this->assertNotEmpty($cohort1);
        $this->assertNotEmpty($cohort2);
        $this->assertSame($batch1->formattedcohortname(), $cohort1->name);
        $this->assertSame($batch2->formattedcohortname(), $cohort2->name);

    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_batch_update() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $institution = $providergenerator->create_institution();

        $this->assertNotEmpty($institution);

        $batch1 = $providergenerator->create_batch(array(
                'institutionid' => $institution->id,
                'testbatch1'
        ));
        $data = $batch1->get_db_record();
        $data->name = 'testbatch211';
        $data->capacity = 33;

        $result = batch::get($data)->update();
        $updatedbatch = batch::get($data->id);

        $this->assertTrue($result);
        $this->assertSame($updatedbatch->name, $data->name);
        $this->assertSame((int) $updatedbatch->capacity, $data->capacity);

        $this->assertEquals($updatedbatch->createrid, 2);
        $this->assertEquals($updatedbatch->modifiedby, 2);
        $this->assertNotEmpty($updatedbatch->timecreated);
        $this->assertNotEmpty($updatedbatch->timemodified);

        $updatedcohort = $DB->get_record('cohort', array('id' => $data->cohortid));
        $this->assertSame($updatedbatch->formattedcohortname(), $updatedcohort->name);

    }

    /**
     * @throws coding_exception
     */
    public function test_batch_eventcreated() {
        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $institution = $providergenerator->create_institution();
        // Catch Events.
        $sink = $this->redirectEvents();

        $batch1 = $providergenerator->create_batch(array(
                'institutionid' => $institution->id
        ));
        $events = $sink->get_events();
        $sink->close();
        // Validate the event.
        $this->assertCount(1, $events);
        $event = $events[0];
        $this->assertInstanceOf('\local_providerapi\event\batch_created', $event);
        $this->assertEquals(batch::$dbname, $event->objecttable);
        $this->assertEquals($batch1->id, $event->objectid);

    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_batch_eventupdated() {
        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $institution = $providergenerator->create_institution();
        $batch1 = $providergenerator->create_batch(array(
                'institutionid' => $institution->id
        ));
        $data = $batch1->get_db_record();
        // Catch Events.
        $sink = $this->redirectEvents();
        batch::get($data)->update();
        $events = $sink->get_events();
        $sink->close();
        // Validate the event.
        $this->assertCount(1, $events);
        $event = $events[0];
        $this->assertInstanceOf('\local_providerapi\event\batch_updated', $event);
        $this->assertEquals(batch::$dbname, $event->objecttable);
        $this->assertEquals($batch1->id, $event->objectid);

    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_batch_deleted() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $institution = $providergenerator->create_institution();

        $this->assertNotEmpty($institution);

        $batch1 = $providergenerator->create_batch(array(
                'institutionid' => $institution->id
        ));

        $result = $batch1->delete();
        $this->assertTrue($result);
        $this->assertFalse($DB->record_exists(batch::$dbname, array('id' => $batch1->id)));
        // Cohort ?
        $this->assertFalse($DB->record_exists('cohort', array('id' => $batch1->cohortid)));

    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_batch_eventdeleted() {
        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $institution = $providergenerator->create_institution();
        $batch1 = $providergenerator->create_batch(array(
                'institutionid' => $institution->id
        ));
        // Catch Events.
        $sink = $this->redirectEvents();
        $batch1->delete();
        $events = $sink->get_events();
        $sink->close();
        // Validate the event.
        $this->assertCount(1, $events);
        $event = $events[0];
        $this->assertInstanceOf('\local_providerapi\event\batch_deleted', $event);
        $this->assertEquals(batch::$dbname, $event->objecttable);
        $this->assertEquals($batch1->id, $event->objectid);

    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_institution_delete() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $institution = $providergenerator->create_institution();
        $providergenerator->create_batch(array(
                'institutionid' => $institution->id
        ));

        $institution->delete();
        $this->assertFalse($DB->record_exists(batch::$dbname, array('institutionid' => $institution->id)));

    }

}
