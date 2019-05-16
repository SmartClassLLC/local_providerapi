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

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/local/providerapi/locallib.php');

/**
 * The btcourse test class.
 *
 * @package    local_providerapi
 * @copyright  2019 Çağlar MERSİNLİ <ceremy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_providerapi_btcourse_testcase extends advanced_testcase {

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_btcourse_assign() {
        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $btcourserecord = $providergenerator->generate_btcourse();

        $this->assertNotEmpty($btcourserecord);
        $this->assertSame('web', $btcourserecord->source);
        $this->assertSame('2', $btcourserecord->createrid);
        $this->assertSame('2', $btcourserecord->modifiedby);
        $this->assertNotEmpty($btcourserecord->timecreated);
        $this->assertNotEmpty($btcourserecord->timemodified);

    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_btcourse_deleted() {
        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $btcourserecord = $providergenerator->generate_btcourse();
        /* $batch1->delete_btcourse($btcourserecord->id);
         $this->assertFalse($DB->record_exists($batch1->btcoursedbname, array('id' => $btcourserecord->id)));*/

    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_btcourse_deletedevent() {
        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $btcourserecord = $providergenerator->generate_btcourse();

        // Catch Events.
        /* $sink = $this->redirectEvents();
         $batch1->delete_btcourse($btcourserecord->id);
         $events = $sink->get_events();
         $sink->close();
         // Validate the event.
         $this->assertCount(1, $events);
         $event = $events[0];
         $this->assertInstanceOf('\local_providerapi\event\btcourse_deleted', $event);
         $this->assertEquals($btcourserecord->id, $event->objectid);
         $this->assertEquals($batch1->id, $event->other['batchid']);
         $this->assertEquals($sharedcourse1->id, $event->other['sharedcourseid']);*/

    }

}
