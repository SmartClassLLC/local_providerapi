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
                'name' => 'testbatch2'
        ));
        $this->assertTrue(batch::validate_exist($batch1->id));
        $this->assertTrue(batch::validate_exist($batch2->id));
        $this->assertEquals($batch1->createrid, 2);
        $this->assertEquals($batch2->createrid, 2);
        $this->assertNotEmpty($batch1->timecreated);
        $this->assertNotEmpty($batch2->timecreated);

        $cohort1 = $DB->get_record('cohort', array('id' => $batch1->cohortid));
        $cohort2 = $DB->get_record('cohort', array('id' => $batch2->cohortid));
        $this->assertNotEmpty($cohort1);
        $this->assertNotEmpty($cohort2);
        $this->assertSame($batch1->formattedcohortname(), $cohort1->name);
        $this->assertSame($batch2->formattedcohortname(), $cohort2->name);

    }

    /**
     * @throws coding_exception
     */
    public function test_batch_eventcreated() {
        global $DB;
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
