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

/**
 * Class local_providerapi_externallib_testcase
 */
class local_providerapi_externallib_testcase extends externallib_advanced_testcase {

    /**
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws invalid_response_exception
     */
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

    /**
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     */
    public function test_create_user_same_idnumber() {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $institution = $providergenerator->create_institution();

        $contextid = context_system::instance()->id;
        $roleid = $this->assignUserCapability('local/providerapi:create_user', $contextid);

        $user1 = array(
                'institutionkey' => '123456',
                'studentno' => '123456',
                'username' => 'user1',
                'firstname' => 'testuser1',
                'lastname' => 'testuserlastname1',
                'password' => '3445345+?Sa1',
                'email' => 'dummy1@example.com'
        );
        $user2 = array(
                'institutionkey' => '123456',
                'studentno' => '123456',
                'username' => 'user2',
                'firstname' => 'testuser2',
                'lastname' => 'testuserlastname2',
                'password' => '34245345+?Sa2',
                'email' => 'dummy2@example.com'
        );
        $this->expectException('invalid_parameter_exception');
        external::create_users(array($user1, $user2));

    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws invalid_response_exception
     * @throws moodle_exception
     */
    public function test_update_user_same_idnumber() {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $institution = $providergenerator->create_institution();

        $contextid = context_system::instance()->id;
        $roleid = $this->assignUserCapability('local/providerapi:create_user', $contextid);
        $this->assignUserCapability('local/providerapi:update_user', $contextid, $roleid);

        $user1 = array(
                'institutionkey' => '123456',
                'studentno' => '123456',
                'username' => 'user1',
                'firstname' => 'testuser1',
                'lastname' => 'testuserlastname1',
                'password' => '3445345+?Sa1',
                'email' => 'dummy1@example.com'
        );
        $user2 = array(
                'institutionkey' => '123456',
                'studentno' => '765432',
                'username' => 'user2',
                'firstname' => 'testuser2',
                'lastname' => 'testuserlastname2',
                'password' => '34245345+?Sa2',
                'email' => 'dummy2@example.com'
        );
        $usercreated = external::create_users(array($user1, $user2));
        $usercreated = external_api::clean_returnvalue(external::create_users_returns(),
                $usercreated);
        $this->assertEquals(2, count($usercreated));
        $usercreated = reset($usercreated);

        $user2 = array(
                'id' => $usercreated['id'],
                'institutionkey' => '123456',
                'studentno' => '765432',
                'username' => 'user1',
                'firstname' => 'testuser1',
                'lastname' => 'testuserlastname1',
                'password' => '3445345+?Sa1',
                'email' => 'dummy1@example.com'
        );
        $this->expectException('invalid_parameter_exception');
        external::update_users(array($user2));

    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     * @throws dml_transaction_exception
     * @throws invalid_parameter_exception
     * @throws invalid_response_exception
     * @throws moodle_exception
     * @throws required_capability_exception
     * @throws restricted_context_exception
     */
    public function test_delete_user() {
        global $DB;
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $institution = $providergenerator->create_institution();

        $contextid = context_system::instance()->id;
        $roleid = $this->assignUserCapability('local/providerapi:create_user', $contextid);
        $this->assignUserCapability('local/providerapi:delete_user', $contextid, $roleid);

        $user1 = array(
                'institutionkey' => '123456',
                'studentno' => '123456',
                'username' => 'dummyuser12',
                'firstname' => 'testuser12',
                'lastname' => 'testuserlastname12',
                'password' => '34255345345+?Sa12',
                'email' => 'dummy12@example.com'
        );
        $user2 = array(
                'institutionkey' => '123456',
                'studentno' => '123457',
                'username' => 'dummyuser22',
                'firstname' => 'testuser22',
                'lastname' => 'testuserlastname22',
                'password' => '34255345345+?Sa22',
                'email' => 'dummy22@example.com'
        );
        $createduser = external::create_users(array($user1, $user2));
        $createduser = external_api::clean_returnvalue(external::create_users_returns(),
                $createduser);
        $this->assertEquals(2, count($createduser));
        $user1 = $DB->get_record('user', array('username' => $user1['username']));
        $user2 = $DB->get_record('user', array('username' => $user2['username']));
        $userfields = array();
        foreach ($createduser as $newuser) {
            $userfields['institutionkey'] = '123456';
            $userfields['id'] = $newuser['id'];
        }
        external::delete_users(array(
                array('institutionkey' => '123456', 'id' => $user1->id),
                array('institutionkey' => '123456', 'id' => $user2->id)
        ));
        // Check we retrieve no users + no error on capability.
        $this->assertEquals(0, $DB->count_records_select('user', 'deleted = 0 AND (id = :userid1 OR id = :userid2)',
                array('userid1' => $user1->id, 'userid2' => $user2->id)));
        // Call without required capability.
        $this->unassignUserCapability('local/providerapi:delete_user', $contextid, $roleid);
        $this->expectException('required_capability_exception');
        external::delete_users(array(
                array('institutionkey' => '123456', 'id' => $user1->id),
                array('institutionkey' => '123456', 'id' => $user2->id)
        ));

    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     * @throws dml_transaction_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws required_capability_exception
     * @throws restricted_context_exception
     */
    public function test_delete_user_other_institutuion() {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $institution = $providergenerator->create_institution();

        $contextid = context_system::instance()->id;
        $this->assignUserCapability('local/providerapi:delete_user', $contextid);
        $user1 = $generator->create_user();
        $this->expectException('moodle_exception');
        external::delete_users(array(
                array('institutionkey' => $institution->secretkey, 'id' => $user1->id),
        ));

    }
}