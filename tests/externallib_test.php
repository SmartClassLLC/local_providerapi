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
 * short_description
 *
 * long_description
 *
 * @package    local_providerapi
 * @copyright  2019 çağlar MERSİNLİ <ceremy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_providerapi\webservice\institution\external;

defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');

class local_providerapi_externallib_testcase extends externallib_advanced_testcase {

    public function test_create_user() {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $institution = $providergenerator->create_institution();

        $contextid = context_system::instance()->id;
        $roleid = $this->assignUserCapability('local/providerapi:create_user', $contextid);

        $user1 = array(
                'institutionkey' => '123456',
                'studentno' => '123456',
                'username' => 'dummyuser1',
                'firstname' => 'testuser1',
                'lastname' => 'testuserlastname1',
                'password' => '34255345345+?Sa1',
                'email' => 'dummy1@example.com'
        );
        $user2 = array(
                'institutionkey' => '123456',
                'studentno' => '123457',
                'username' => 'dummyuser2',
                'firstname' => 'testuser2',
                'lastname' => 'testuserlastname2',
                'password' => '34255345345+?Sa2',
                'email' => 'dummy2@example.com'
        );

        // Call the external function.
        $this->setCurrentTimeStart();
        $createduser = external::create_users(array($user1, $user2));
        $createduser = external_api::clean_returnvalue(external::create_users_returns(),
                $createduser);
        $this->assertEquals(2, count($createduser));

        foreach ($createduser as $newuser) {
            $user = core_user::get_user($newuser['id']);
            if ($user->username === 'dummyuser1') {
                $this->assertEquals($user->firstname, $user1['firstname']);
                $this->assertEquals($user->lastname, $user1['lastname']);
                $this->assertEquals($user->email, $user1['email']);
                $this->assertEquals($user->idnumber, $institution->shortname . $user1['studentno']);
                $this->assertTrue(cohort_is_member($institution->cohortid, $user->id));
            } else if ($user->username === 'dummyuser2') {
                $this->assertEquals($user->firstname, $user2['firstname']);
                $this->assertEquals($user->lastname, $user2['lastname']);
                $this->assertEquals($user->email, $user2['email']);
                $this->assertEquals($user->idnumber, $institution->shortname . $user2['studentno']);
                $this->assertTrue(cohort_is_member($institution->cohortid, $user->id));
            } else {
                $this->fail('Unrecognised user found');
            }
            $this->assertTimeCurrent($user->timecreated);
            $this->assertTimeCurrent($user->timemodified);

        }

        // Call without required capability.
        $this->unassignUserCapability('local/providerapi:create_user', $contextid, $roleid);
        $this->expectException('required_capability_exception');
        external::create_users(array($user1));
    }
}