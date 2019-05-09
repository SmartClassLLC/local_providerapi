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

// For installation and usage of PHPUnit within Moodle please read:
// https://docs.moodle.org/dev/PHPUnit
//
// Documentation for writing PHPUnit tests for Moodle can be found here:
// https://docs.moodle.org/dev/PHPUnit_integration
// https://docs.moodle.org/dev/Writing_PHPUnit_tests
//
// The official PHPUnit homepage is at:
// https://phpunit.de.

/**
 * The client test class.
 *
 * @package    local_providerapi
 * @copyright  2019 Çağlar MERSİNLİ <ceremy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_providerapi_institution_testcase extends advanced_testcase {

    // Write the tests here as public funcions.
    public function test_institution_created() {
        global $DB;

        $this->resetAfterTest();

        $institution = new stdClass();
        $institution->name = 'test institution';
        $institution->shortname = 'TES';
        $institution->secretkey = '123456';
        $institution->description = 'test description';
        $institution->descriptionformat = FORMAT_HTML;

        $id = \local_providerapi\local\institution\institution::get($institution)->create();
        $this->assertNotEmpty($id);

        $new = $DB->get_record('local_providerapi_companies', array('id' => $id));
        $this->assertSame($institution->name, $new->name);
        $this->assertSame($institution->shortname, $new->shortname);
        $this->assertSame($institution->secretkey, $new->secretkey);
        $this->assertSame($institution->description, $new->description);
        $this->assertEquals($institution->descriptionformat, $new->descriptionformat);
        $this->assertNotEmpty($new->createrid);
        $this->assertNotEmpty($new->modifiedby);
        $this->assertNotEmpty($new->timecreated);
        $this->assertNotEmpty($new->timemodified);

    }

}
