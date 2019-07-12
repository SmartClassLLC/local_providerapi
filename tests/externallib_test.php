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

use local_providerapi\local\batch\batch;
use local_providerapi\local\batch\btcourse;
use local_providerapi\local\course\course;
use local_providerapi\local\helper;
use local_providerapi\local\institution\institution;
use local_providerapi\webservice\course\external;

defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/local/providerapi/locallib.php');

/**
 * Class local_providerapi_externallib_testcase
 */
class local_providerapi_externallib_testcase extends externallib_advanced_testcase {

    /**
     * @var institution
     */
    private $institution;

    /**
     * @var batch
     */
    private $batch1;
    /**
     * @var batch
     */
    private $batch2;

    /**
     * @throws coding_exception
     */
    protected function setUp() {
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $this->institution = $providergenerator->create_institution();

        $this->batch1 = $providergenerator->create_batch(array(
                'institutionid' => $this->institution->id,
                'name' => 'testbatch1',
                'source' => PROVIDERAPI_SOURCEWS
        ));
        $this->batch2 = $providergenerator->create_batch(array(
                'institutionid' => $this->institution->id,
                'name' => 'testbatch2',
                'source' => PROVIDERAPI_SOURCEWS
        ));

    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws invalid_response_exception
     */
    public function test_create_user() {
        $this->resetAfterTest();

        $institution = $this->institution;

        $contextid = context_system::instance()->id;
        $roleid = $this->assignUserCapability('local/providerapi:create_user', $contextid);

        $user1 = array(
                'studentno' => '123456',
                'username' => 'dummyuser1',
                'firstname' => 'testuser1',
                'lastname' => 'testuserlastname1',
                'password' => '34255345345+?Sa1',
                'email' => 'dummy1@example.com'
        );
        $user2 = array(
                'studentno' => '123457',
                'username' => 'dummyuser2',
                'firstname' => 'testuser2',
                'lastname' => 'testuserlastname2',
                'password' => '34255345345+?Sa2',
                'email' => 'dummy2@example.com'
        );

        // Call the external function.
        $this->setCurrentTimeStart();
        $createduser =
                \local_providerapi\webservice\institution\external::create_users($institution->secretkey, array($user1, $user2));
        $createduser = external_api::clean_returnvalue(\local_providerapi\webservice\institution\external::create_users_returns(),
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
        \local_providerapi\webservice\institution\external::create_users($institution->secretkey, array($user1));
    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     */
    public function test_create_user_same_idnumber() {
        $this->resetAfterTest();

        $institution = $this->institution;

        $contextid = context_system::instance()->id;
        $this->assignUserCapability('local/providerapi:create_user', $contextid);

        $user1 = array(
                'studentno' => '123456',
                'username' => 'user1',
                'firstname' => 'testuser1',
                'lastname' => 'testuserlastname1',
                'password' => '3445345+?Sa1',
                'email' => 'dummy1@example.com'
        );
        $user2 = array(
                'studentno' => '123456',
                'username' => 'user2',
                'firstname' => 'testuser2',
                'lastname' => 'testuserlastname2',
                'password' => '34245345+?Sa2',
                'email' => 'dummy2@example.com'
        );
        $this->expectException('invalid_parameter_exception');
        \local_providerapi\webservice\institution\external::create_users($institution->secretkey, array($user1, $user2));

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

        $institution = $this->institution;
        $contextid = context_system::instance()->id;
        $roleid = $this->assignUserCapability('local/providerapi:create_user', $contextid);
        $this->assignUserCapability('local/providerapi:update_user', $contextid, $roleid);

        $user1 = array(
                'studentno' => '123456',
                'username' => 'user1',
                'firstname' => 'testuser1',
                'lastname' => 'testuserlastname1',
                'password' => '3445345+?Sa1',
                'email' => 'dummy1@example.com'
        );
        $user2 = array(
                'studentno' => '765432',
                'username' => 'user2',
                'firstname' => 'testuser2',
                'lastname' => 'testuserlastname2',
                'password' => '34245345+?Sa2',
                'email' => 'dummy2@example.com'
        );
        $usercreated =
                \local_providerapi\webservice\institution\external::create_users($institution->secretkey, array($user1, $user2));
        $usercreated = external_api::clean_returnvalue(\local_providerapi\webservice\institution\external::create_users_returns(),
                $usercreated);
        $this->assertEquals(2, count($usercreated));
        $usercreated = reset($usercreated);

        $user2 = array(
                'id' => $usercreated['id'],
                'studentno' => '765432',
                'username' => 'user1',
                'firstname' => 'testuser1',
                'lastname' => 'testuserlastname1',
                'password' => '3445345+?Sa1',
                'email' => 'dummy1@example.com'
        );
        $this->expectException('invalid_parameter_exception');
        \local_providerapi\webservice\institution\external::update_users($institution->secretkey, array($user2));

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
        $institution = $this->institution;
        $contextid = context_system::instance()->id;
        $roleid = $this->assignUserCapability('local/providerapi:create_user', $contextid);
        $this->assignUserCapability('local/providerapi:delete_user', $contextid, $roleid);

        $user1 = array(
                'studentno' => '123456',
                'username' => 'dummyuser12',
                'firstname' => 'testuser12',
                'lastname' => 'testuserlastname12',
                'password' => '34255345345+?Sa12',
                'email' => 'dummy12@example.com'
        );
        $user2 = array(
                'studentno' => '123457',
                'username' => 'dummyuser22',
                'firstname' => 'testuser22',
                'lastname' => 'testuserlastname22',
                'password' => '34255345345+?Sa22',
                'email' => 'dummy22@example.com'
        );
        $createduser =
                \local_providerapi\webservice\institution\external::create_users($institution->secretkey, array($user1, $user2));
        $createduser = external_api::clean_returnvalue(\local_providerapi\webservice\institution\external::create_users_returns(),
                $createduser);
        $this->assertEquals(2, count($createduser));
        $user1 = $DB->get_record('user', array('username' => $user1['username']));
        $user2 = $DB->get_record('user', array('username' => $user2['username']));
        $userfields = array();
        foreach ($createduser as $newuser) {
            $userfields['institutionkey'] = '123456';
            $userfields['id'] = $newuser['id'];
        }
        \local_providerapi\webservice\institution\external::delete_users($institution->secretkey, array(
                array('id' => $user1->id),
                array('id' => $user2->id)
        ));
        // Check we retrieve no users + no error on capability.
        $this->assertEquals(0, $DB->count_records_select('user', 'deleted = 0 AND (id = :userid1 OR id = :userid2)',
                array('userid1' => $user1->id, 'userid2' => $user2->id)));
        // Call without required capability.
        $this->unassignUserCapability('local/providerapi:delete_user', $contextid, $roleid);
        $this->expectException('required_capability_exception');
        \local_providerapi\webservice\institution\external::delete_users($institution->secretkey, array(
                array('id' => $user1->id),
                array('id' => $user2->id)
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
        $institution = $this->institution;

        $contextid = context_system::instance()->id;
        $this->assignUserCapability('local/providerapi:delete_user', $contextid);
        $user1 = $generator->create_user();
        $this->expectExceptionObject(new moodle_exception('nofounduserininstitutuion', 'local_providerapi'));
        $this->expectExceptionMessage('User is not found in this institutuion');
        \local_providerapi\webservice\institution\external::delete_users($institution->secretkey, array(
                array('id' => $user1->id)
        ));

    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_response_exception
     */
    public function test_get_courses() {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $institution = $this->institution;
        $course1 = $generator->create_course();
        $course2 = $generator->create_course();

        $providergenerator->create_sharedcourse(array(
                'institutionid' => $institution->id,
                'courseids' => array($course1->id, $course2->id)
        ));
        $contextid = context_system::instance()->id;
        $roleid = $this->assignUserCapability('local/providerapi:viewassigncourse', $contextid);
        $courserecords = external::get_courses($institution->secretkey);
        $courserecords = external_api::clean_returnvalue(external::get_courses_returns(),
                $courserecords);
        $this->assertEquals(2, count($courserecords));
        foreach ($courserecords as $courserecord) {
            $this->assertTrue(($courserecord['id'] == $course1->id || $courserecord['id'] == $course2->id));
            $this->assertTrue(($courserecord['fullname'] === $course1->fullname ||
                    $courserecord['fullname'] === $course2->fullname));
        }
        // Call without required capability.
        $this->unassignUserCapability('local/providerapi:viewassigncourse', $contextid, $roleid);
        $this->expectException('required_capability_exception');
        external::get_courses($institution->secretkey);
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
    public function test_create_batches() {
        $this->resetAfterTest();
        $institution = $this->institution;

        $contextid = context_system::instance()->id;
        $roleid = $this->assignUserCapability('local/providerapi:addbatch', $contextid);

        $batch1 = array(
                'name' => 'test1',
                'capacity' => 11
        );
        $batch2 = array(
                'name' => 'test2'
        );

        $batchrecords =
                \local_providerapi\webservice\batch\external::create_batches($institution->secretkey, array($batch1, $batch2));
        $batchrecords = external_api::clean_returnvalue(\local_providerapi\webservice\batch\external::create_batches_returns(),
                $batchrecords);
        $this->assertEquals(2, count($batchrecords));

        foreach ($batchrecords as $batchrecord) {
            if ($batchrecord['name'] === $batch1['name']) {
                $this->assertEquals($batchrecord['capacity'], $batch1['capacity']);
            } else if ($batchrecord['name'] === $batch2['name']) {
                $this->assertEquals(0, $batchrecord['capacity']);
            } else {
                $this->fail('Unrecognised batch found');
            }
        }

        // Call without required capability.
        $this->unassignUserCapability('local/providerapi:addbatch', $contextid, $roleid);
        $this->expectException('required_capability_exception');
        \local_providerapi\webservice\batch\external::create_batches($institution->secretkey, array($batch1, $batch2));

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
    public function test_create_same_name_batch() {
        $this->resetAfterTest();
        $institution = $this->institution;

        $contextid = context_system::instance()->id;
        $this->assignUserCapability('local/providerapi:addbatch', $contextid);

        $batch1 = array(
                'name' => 'test1',
                'capacity' => 11
        );
        $batch2 = array(
                'name' => 'test1'
        );

        $this->expectException('moodle_exception');
        \local_providerapi\webservice\batch\external::create_batches($institution->secretkey, array($batch1, $batch2));

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
    public function test_create_invalid_capacity() {
        $this->resetAfterTest();
        $institution = $this->institution;

        $contextid = context_system::instance()->id;
        $this->assignUserCapability('local/providerapi:addbatch', $contextid);

        $batch1 = array(
                'name' => 'test1',
                'capacity' => 1111
        );

        $this->expectException('invalid_parameter_exception');
        \local_providerapi\webservice\batch\external::create_batches($institution->secretkey, array($batch1));

        $batch2 = array(
                'name' => 'test1',
                'capacity' => -2
        );
        $this->expectException('invalid_parameter_exception');
        \local_providerapi\webservice\batch\external::create_batches($institution->secretkey, array($batch2));

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
    public function test_update_batches() {
        global $DB;
        $this->resetAfterTest();
        $institution = $this->institution;

        $contextid = context_system::instance()->id;
        $roleid = $this->assignUserCapability('local/providerapi:addbatch', $contextid);
        $this->assignUserCapability('local/providerapi:editbatch', $contextid, $roleid);

        $batch1 = array(
                'name' => 'test1',
                'capacity' => 11
        );

        $batchrecords =
                \local_providerapi\webservice\batch\external::create_batches($institution->secretkey, array($batch1));
        $batchrecords = external_api::clean_returnvalue(\local_providerapi\webservice\batch\external::create_batches_returns(),
                $batchrecords);
        $this->assertEquals(1, count($batchrecords));
        $batchrecords = reset($batchrecords);

        $batch2 = array(
                'id' => $batchrecords['id'],
                'name' => 'test12',
                'capacity' => 111
        );
        \local_providerapi\webservice\batch\external::update_batches($institution->secretkey, array($batch2));

        $this->assertTrue($DB->record_exists(batch::$dbname,
                array('id' => $batchrecords['id'], 'name' => 'test12', 'capacity' => 111)));

        // Call without required capability.
        $this->unassignUserCapability('local/providerapi:editbatch', $contextid, $roleid);
        $this->expectException('required_capability_exception');
        \local_providerapi\webservice\batch\external::update_batches($institution->secretkey, array($batch2));

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
    public function test_delete_batches() {
        global $DB;
        $this->resetAfterTest();
        $institution = $this->institution;

        $contextid = context_system::instance()->id;
        $roleid = $this->assignUserCapability('local/providerapi:addbatch', $contextid);
        $this->assignUserCapability('local/providerapi:deletebatch', $contextid, $roleid);

        $batch1 = array(
                'name' => 'test1',
                'capacity' => 11
        );

        $batchrecords =
                \local_providerapi\webservice\batch\external::create_batches($institution->secretkey, array($batch1));
        $batchrecords = external_api::clean_returnvalue(\local_providerapi\webservice\batch\external::create_batches_returns(),
                $batchrecords);
        $this->assertEquals(1, count($batchrecords));
        $batchrecords = reset($batchrecords);

        \local_providerapi\webservice\batch\external::delete_batches($institution->secretkey,
                array(array('id' => $batchrecords['id'])));

        $this->assertNotTrue($DB->record_exists(batch::$dbname, array('id' => $batchrecords['id'])));

        // Call without required capability.
        $this->unassignUserCapability('local/providerapi:deletebatch', $contextid, $roleid);
        $this->expectException('required_capability_exception');
        \local_providerapi\webservice\batch\external::delete_batches($institution->secretkey,
                array(array('id' => $batchrecords['id'])));

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
    public function test_get_batches() {
        $this->resetAfterTest();
        $institution = $this->institution;

        $contextid = context_system::instance()->id;
        $roleid = $this->assignUserCapability('local/providerapi:addbatch', $contextid);
        $this->assignUserCapability('local/providerapi:viewbatch', $contextid, $roleid);

        $batch1 = array(
                'name' => 'test1',
                'capacity' => 11
        );

        $batchrecords =
                \local_providerapi\webservice\batch\external::create_batches($institution->secretkey, array($batch1));
        $batchrecords = external_api::clean_returnvalue(\local_providerapi\webservice\batch\external::create_batches_returns(),
                $batchrecords);
        $this->assertEquals(1, count($batchrecords));
        $batchrecords = reset($batchrecords);

        $getbatches = \local_providerapi\webservice\batch\external::get_batches($institution->secretkey);
        $getbatches = external_api::clean_returnvalue(\local_providerapi\webservice\batch\external::get_batches_returns(),
                $getbatches);
        $getbatches = reset($getbatches);

        $this->assertEquals($batchrecords['id'], $getbatches['id']);
        $this->assertEquals($batchrecords['name'], $getbatches['name']);
        $this->assertEquals($batchrecords['capacity'], $getbatches['capacity']);

        // Call without required capability.
        $this->unassignUserCapability('local/providerapi:viewbatch', $contextid, $roleid);
        $this->expectException('required_capability_exception');
        \local_providerapi\webservice\batch\external::get_batches($institution->secretkey);

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
    public function test_assign_batchmember() {
        $this->resetAfterTest();
        $institution = $this->institution;

        $contextid = context_system::instance()->id;
        $roleid = $this->assignUserCapability('local/providerapi:create_user', $contextid);
        $this->assignUserCapability('local/providerapi:addbatch', $contextid, $roleid);
        $this->assignUserCapability('local/providerapi:assignbatchmembers', $contextid, $roleid);

        $user1 = array(
                'studentno' => '123456',
                'username' => 'dummyuser1',
                'firstname' => 'testuser1',
                'lastname' => 'testuserlastname1',
                'password' => '34255345345+?Sa1',
                'email' => 'dummy1@example.com'
        );
        $user2 = array(
                'studentno' => '123457',
                'username' => 'dummyuser2',
                'firstname' => 'testuser2',
                'lastname' => 'testuserlastname2',
                'password' => '34255345345+?Sa2',
                'email' => 'dummy2@example.com'
        );
        $batch1 = array(
                'name' => 'test1',
                'capacity' => 11
        );

        // Call the external function.
        $this->setCurrentTimeStart();
        $createduser =
                \local_providerapi\webservice\institution\external::create_users($institution->secretkey, array($user1, $user2));
        $createduser = external_api::clean_returnvalue(\local_providerapi\webservice\institution\external::create_users_returns(),
                $createduser);
        $this->assertEquals(2, count($createduser));
        $users = array();
        foreach ($createduser as $newusers) {
            $users[] = array('userid' => $newusers['id']);
        }
        $batchrecords =
                \local_providerapi\webservice\batch\external::create_batches($institution->secretkey, array($batch1));
        $batchrecords = external_api::clean_returnvalue(\local_providerapi\webservice\batch\external::create_batches_returns(),
                $batchrecords);
        $this->assertEquals(1, count($batchrecords));
        $batchrecords = reset($batchrecords);
        $batch = batch::get($batchrecords['id']);
        $assignrecords =
                \local_providerapi\webservice\batch\external::assign_batchmembers($institution->secretkey, $batch->id, $users);
        $assignrecords =
                external_api::clean_returnvalue(\local_providerapi\webservice\batch\external::assign_batchmembers_returns(),
                        $assignrecords);
        $this->assertEquals(2, count($assignrecords));

        foreach ($assignrecords as $assignrecord) {
            $this->assertTrue($assignrecord['status']);
            $this->assertTrue($batch->is_member($assignrecord['userid']));
        }
        // Call without required capability.
        $this->unassignUserCapability('local/providerapi:assignbatchmembers', $contextid, $roleid);
        $this->expectException('required_capability_exception');
        \local_providerapi\webservice\batch\external::assign_batchmembers($institution->secretkey, $batch->id, $users);

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
    public function test_unassign_batchmember() {
        $this->resetAfterTest();
        $institution = $this->institution;

        $contextid = context_system::instance()->id;
        $roleid = $this->assignUserCapability('local/providerapi:create_user', $contextid);
        $this->assignUserCapability('local/providerapi:addbatch', $contextid, $roleid);
        $this->assignUserCapability('local/providerapi:assignbatchmembers', $contextid, $roleid);
        $this->assignUserCapability('local/providerapi:unassignbatchmembers', $contextid, $roleid);

        $user1 = array(
                'studentno' => '123456',
                'username' => 'dummyuser1',
                'firstname' => 'testuser1',
                'lastname' => 'testuserlastname1',
                'password' => '34255345345+?Sa1',
                'email' => 'dummy1@example.com'
        );
        $user2 = array(
                'studentno' => '123457',
                'username' => 'dummyuser2',
                'firstname' => 'testuser2',
                'lastname' => 'testuserlastname2',
                'password' => '34255345345+?Sa2',
                'email' => 'dummy2@example.com'
        );
        $batch1 = array(
                'name' => 'test1',
                'capacity' => 11
        );

        // Call the external function.
        $this->setCurrentTimeStart();
        $createduser =
                \local_providerapi\webservice\institution\external::create_users($institution->secretkey, array($user1, $user2));
        $createduser = external_api::clean_returnvalue(\local_providerapi\webservice\institution\external::create_users_returns(),
                $createduser);
        $this->assertEquals(2, count($createduser));
        $users = array();
        foreach ($createduser as $newusers) {
            $users[] = array('userid' => $newusers['id']);
        }
        $batchrecords =
                \local_providerapi\webservice\batch\external::create_batches($institution->secretkey, array($batch1));
        $batchrecords = external_api::clean_returnvalue(\local_providerapi\webservice\batch\external::create_batches_returns(),
                $batchrecords);
        $this->assertEquals(1, count($batchrecords));
        $batchrecords = reset($batchrecords);
        $batch = batch::get($batchrecords['id']);
        $assignrecords =
                \local_providerapi\webservice\batch\external::assign_batchmembers($institution->secretkey, $batch->id, $users);
        $assignrecords =
                external_api::clean_returnvalue(\local_providerapi\webservice\batch\external::assign_batchmembers_returns(),
                        $assignrecords);
        $this->assertEquals(2, count($assignrecords));

        foreach ($assignrecords as $assignrecord) {
            $this->assertTrue($assignrecord['status']);
            $this->assertTrue($batch->is_member($assignrecord['userid']));
        }

        $unassignrecord =
                \local_providerapi\webservice\batch\external::unassign_batchmembers($institution->secretkey, $batch->id, $users);
        $unassignrecord =
                external_api::clean_returnvalue(\local_providerapi\webservice\batch\external::unassign_batchmembers_returns(),
                        $unassignrecord);
        $this->assertEquals(2, count($unassignrecord));

        foreach ($unassignrecord as $record) {
            $this->assertTrue($record['status']);
            $this->assertNotTrue($batch->is_member($record['userid']));
        }

        // Call without required capability.
        $this->unassignUserCapability('local/providerapi:unassignbatchmembers', $contextid, $roleid);
        $this->expectException('required_capability_exception');
        \local_providerapi\webservice\batch\external::unassign_batchmembers($institution->secretkey, $batch->id, $users);

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
    public function test_assign_course_to_batch() {
        global $DB;
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $institution = $this->institution;

        $contextid = context_system::instance()->id;
        $roleid = $this->assignUserCapability('local/providerapi:assignbtcourse', $contextid);
        $this->assignUserCapability('local/providerapi:viewassignbtcourse', $contextid, $roleid);
        $this->assignUserCapability('local/providerapi:unassignbtcourse', $contextid, $roleid);

        $course1 = $generator->create_course();
        $course1field = array('courseid' => $course1->id);
        $course2 = $generator->create_course();
        $course2field = array('courseid' => $course2->id);
        $providergenerator->create_sharedcourse(array(
                'institutionid' => $institution->id,
                'courseids' => array($course1->id)
        ));
        $batch1 = $this->batch1;

        $assigncourses =
                external::assign_course_to_batch($institution->secretkey, $batch1->id, array($course1field, $course2field));
        $assigncourses = external_api::clean_returnvalue(external::assign_course_to_batch_returns(), $assigncourses);
        $this->assertEquals(2, count($assigncourses));

        foreach ($assigncourses as $unassignedcourse) {
            if ($unassignedcourse['courseid'] == $course1->id) {
                $this->assertTrue($unassignedcourse['status']);
                $this->assertEquals($unassignedcourse['message'], 'assign successfuly');
                $sharedcourse =
                        $DB->get_record(course::$dbname, array('institutionid' => $institution->id, 'courseid' => $course1->id));
                $this->assertNotEmpty($sharedcourse);
                $this->assertTrue($DB->record_exists_select(btcourse::$dbname,
                        'batchid = :batchid
                         AND sharedcourseid = :sharedcourseid
                         AND source = :source',
                        array(
                                'batchid' => $batch1->id,
                                'sharedcourseid' => $sharedcourse->id,
                                'source' => PROVIDERAPI_SOURCEWS
                        )));
            } else if ($unassignedcourse['courseid'] == $course2->id) {
                $this->assertNotTrue($unassignedcourse['status']);
                $this->assertEquals($unassignedcourse['message'], 'the course does not exist in this institution');
                $sharedcourse =
                        $DB->get_record(course::$dbname, array('institutionid' => $institution->id, 'courseid' => $course2->id));
                $this->assertFalse($sharedcourse);

            } else {
                $this->fail('Unrecognised btcourse found');
            }

        }
        // Test view assignbtcourses.
        $assigviewbtcourses = external::get_batch_courses($institution->secretkey, $batch1->id);
        $assigviewbtcourses = external_api::clean_returnvalue(external::get_batch_courses_returns(), $assigviewbtcourses);
        $this->assertEquals(1, count($assigviewbtcourses));
        $assigviewbtcourses = reset($assigviewbtcourses);
        $this->assertEquals($course1->id, $assigviewbtcourses['courseid']);
        $this->assertEquals($course1->fullname, $assigviewbtcourses['coursename']);

        // Test Unassign.

        $unassigncourses =
                external::unassign_course_to_batch($institution->secretkey, $batch1->id, array($course1field, $course2field));
        $unassigncourses = external_api::clean_returnvalue(external::unassign_course_to_batch_returns(), $unassigncourses);
        $this->assertEquals(2, count($unassigncourses));
        $this->assertEquals(2, count($assigncourses));
        foreach ($unassigncourses as $assignedcourse) {
            if ($assignedcourse['courseid'] == $course1->id) {
                $this->assertTrue($assignedcourse['status']);
                $this->assertEquals($assignedcourse['message'], 'unassign successfuly');
                $sharedcourse =
                        $DB->get_record(course::$dbname, array('institutionid' => $institution->id, 'courseid' => $course1->id));
                $this->assertNotEmpty($sharedcourse);
                $this->assertFalse($DB->record_exists_select(btcourse::$dbname,
                        'batchid = :batchid
                         AND sharedcourseid = :sharedcourseid',
                        array(
                                'batchid' => $batch1->id,
                                'sharedcourseid' => $sharedcourse->id
                        )));
            } else if ($assignedcourse['courseid'] == $course2->id) {
                $this->assertFalse($assignedcourse['status']);
                $this->assertEquals($assignedcourse['message'], 'the course does not exist in this institution');
                $sharedcourse =
                        $DB->get_record(course::$dbname, array('institutionid' => $institution->id, 'courseid' => $course2->id));
                $this->assertFalse($sharedcourse);

            } else {
                $this->fail('Unrecognised btcourse found');
            }

        }

        // Call without required capability.
        $this->unassignUserCapability('local/providerapi:assignbtcourse', $contextid, $roleid);
        $this->expectException('required_capability_exception');
        external::assign_course_to_batch($institution->secretkey, $batch1->id, array($course1field, $course2field));
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
    public function test_get_lti_info() {
        global $CFG;
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $institution = $this->institution;

        $contextid = context_system::instance()->id;
        $roleid = $this->assignUserCapability('local/providerapi:get_lti_info', $contextid);

        $course1 = $generator->create_course();
        $providergenerator->create_sharedcourse(array(
                'institutionid' => $institution->id,
                'courseids' => array($course1->id)
        ));
        $getltiinfo = external::get_lti_info($institution->secretkey, $course1->id);
        $getltiinfo = external_api::clean_returnvalue(external::get_lti_info_returns(), $getltiinfo);
        $this->assertEquals(4, count($getltiinfo));

        $tool = helper::get_tool_by_courseid($course1->id);

        $this->assertEquals($getltiinfo['launchurl'], $CFG->wwwroot . '/local/providerapi/tool.php?id=' . $tool->id);
        $this->assertEquals($getltiinfo['proxyurl'], helper::get_proxy_url($tool)->out(false));
        $this->assertEquals($getltiinfo['cartridgeurl'], helper::get_cartridge_url($tool)->out(false));
        $this->assertEquals($getltiinfo['secret'], $tool->secret);

        // Call without required capability.
        $this->unassignUserCapability('local/providerapi:get_lti_info', $contextid, $roleid);
        $this->expectException('required_capability_exception');
        external::get_lti_info($institution->secretkey, $course1->id);
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
    public function test_manual_enrol() {
        global $DB;
        $this->resetAfterTest();
        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);
        $institution = $this->institution;
        $institution->add_member($user->id);
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');

        $course1 = self::getDataGenerator()->create_course();
        $course1field = array('courseid' => $course1->id);
        $context = context_course::instance($course1->id);
        $instance1 = $DB->get_record('enrol', array('courseid' => $course1->id, 'enrol' => 'manual'), '*', MUST_EXIST);
        $roleid = $this->assignUserCapability('enrol/manual:enrol', $context->id);
        $this->assignUserCapability('moodle/course:view', $context->id, $roleid);
        $this->assignUserCapability('moodle/role:assign', $context->id, $roleid);
        $this->assignUserCapability('local/providerapi:assignbtcourse', context_system::instance()->id, $roleid);
        $batch1 = $this->batch1;
        core_role_set_assign_allowed($roleid, 4); // 4 mean teacher.
        try {
            external::manual_enrol($institution->secretkey,
                    array(array('userid' => $user->id, 'courseid' => $course1->id, 'batchid' => $batch1->id,
                            'roleshortname' => 'teacher')));
            $this->fail('The Course is not member of institutuion');
        } catch (moodle_exception $e) {
            $this->assertSame('notexistcourse', $e->errorcode);
        }
        $this->assertEquals(0, $DB->count_records('user_enrolments'));
        $providergenerator->create_sharedcourse(array(
                'institutionid' => $institution->id,
                'courseids' => array($course1->id)
        ));
        try {
            external::manual_enrol($institution->secretkey,
                    array(array('userid' => $user->id, 'courseid' => $course1->id, 'batchid' => $batch1->id,
                            'roleshortname' => 'teacher')));
            $this->fail('The Course is not member of batch');
        } catch (moodle_exception $e) {
            $this->assertSame('notexistcourseinbatch', $e->errorcode);
        }
        $this->assertEquals(0, $DB->count_records('user_enrolments'));
        external::assign_course_to_batch($institution->secretkey, $batch1->id, array($course1field));
        $btcourse = $DB->get_record_sql(
                "SELECT bt.* FROM {local_providerapi_btcourses} bt
                      JOIN {local_providerapi_courses} sc ON sc.id = bt.sharedcourseid
                      JOIN {course} c ON c.id = sc.courseid
                      WHERE c.id = :courseid
                      AND bt.batchid = :batchid", array('courseid' => $course1->id, 'batchid' => $batch1->id));

        $response = external::manual_enrol($institution->secretkey,
                array(array('userid' => $user->id, 'courseid' => $course1->id, 'batchid' => $batch1->id,
                        'roleshortname' => 'teacher')));
        $response = external_api::clean_returnvalue(external::manual_enrol_returns(), $response);
        $this->assertEquals(1, count($response));
        $response = reset($response);
        $this->assertTrue($response['enrolstatus']);
        $this->assertTrue($response['groupstatus']);
        $this->assertEquals($course1->id, $response['courseid']);
        $this->assertEquals($user->id, $response['userid']);
        $this->assertEquals(1, $DB->count_records('user_enrolments', array('enrolid' => $instance1->id)));
        $this->assertEquals(1, $DB->count_records('groups_members', array('groupid' => $btcourse->groupid, 'userid' => $user->id)));

        $institution->remove_member($user->id);
        $DB->delete_records('user_enrolments');
        try {
            external::manual_enrol($institution->secretkey,
                    array(array('userid' => $user->id, 'courseid' => $course1->id, 'batchid' => $batch1->id,
                            'roleshortname' => 'teacher')));
            $this->fail('The user is not member of institutuion');

        } catch (moodle_exception $e) {
            $this->assertSame('notexistuser', $e->errorcode);
        }
        $this->assertEquals(0, $DB->count_records('user_enrolments'));
        $institution->add_member($user->id);
        $this->unassignUserCapability('enrol/manual:enrol', $context->id, $roleid);
        try {
            external::manual_enrol($institution->secretkey,
                    array(array('userid' => $user->id, 'courseid' => $course1->id, 'batchid' => $batch1->id,
                            'roleshortname' => 'teacher')));
            $this->fail('Exception expected if not having capability to enrol');

        } catch (moodle_exception $e) {
            $this->assertInstanceOf('required_capability_exception', $e);
            $this->assertSame('nopermissions', $e->errorcode);
        }
        $this->assignUserCapability('enrol/manual:enrol', $context->id, $roleid);
        $this->assertEquals(0, $DB->count_records('user_enrolments'));
        try {
            external::manual_enrol($institution->secretkey,
                    array(array('userid' => $user->id, 'courseid' => $course1->id, 'batchid' => $batch1->id,
                            'roleshortname' => 'nonteacher')));
            $this->fail('The role is not exist');

        } catch (moodle_exception $e) {
            $this->assertSame('notexistrole', $e->errorcode);
        }
        $this->assertEquals(0, $DB->count_records('user_enrolments'));
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
    public function test_manual_unenrol() {
        global $DB, $CFG;
        require_once($CFG->libdir . '/enrollib.php');
        require_once($CFG->dirroot . '/group/lib.php');
        $this->resetAfterTest();
        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);
        $enrol = enrol_get_plugin('manual');
        $institution = $this->institution;
        $institution->add_member($user->id);
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');

        $course1 = self::getDataGenerator()->create_course();
        $course1field = array('courseid' => $course1->id);
        $context = context_course::instance($course1->id);
        $instance = $DB->get_record('enrol', array('courseid' => $course1->id, 'enrol' => 'manual'), '*', MUST_EXIST);
        $roleid = $this->assignUserCapability('enrol/manual:enrol', $context->id);
        $this->assignUserCapability('enrol/manual:unenrol', $context->id, $roleid);
        $this->assignUserCapability('moodle/course:view', $context->id, $roleid);
        $this->assignUserCapability('moodle/role:assign', $context->id, $roleid);
        $this->assignUserCapability('local/providerapi:assignbtcourse', context_system::instance()->id, $roleid);
        $batch1 = $this->batch1;
        core_role_set_assign_allowed($roleid, 4); // 4 mean teacher.
        $providergenerator->create_sharedcourse(array(
                'institutionid' => $institution->id,
                'courseids' => array($course1->id)
        ));
        external::assign_course_to_batch($institution->secretkey, $batch1->id, array($course1field));
        $btcourse = $DB->get_record_sql(
                "SELECT bt.* FROM {local_providerapi_btcourses} bt
                      JOIN {local_providerapi_courses} sc ON sc.id = bt.sharedcourseid
                      JOIN {course} c ON c.id = sc.courseid
                      WHERE c.id = :courseid
                      AND bt.batchid = :batchid", array('courseid' => $course1->id, 'batchid' => $batch1->id));
        $enrol->enrol_user($instance, $user->id, 4);
        groups_add_member($btcourse->groupid, $user->id, 'enrol_manual', $instance->id);
        $this->assertTrue(is_enrolled($context, $user));

        external::manual_unenrol($institution->secretkey,
                array(array('userid' => $user->id, 'courseid' => $course1->id, 'batchid' => $batch1->id)));
        $this->assertTrue(is_enrolled($context, $user));
        $this->assertFalse(groups_is_member($btcourse->groupid, $user->id));
        $enrol->enrol_user($instance, $user->id, 4);
        groups_add_member($btcourse->groupid, $user->id, 'enrol_manual', $instance->id);
        $this->unassignUserCapability('enrol/manual:unenrol', $context->id, $roleid);
        try {
            external::manual_unenrol($institution->secretkey,
                    array(array('userid' => $user->id, 'courseid' => $course1->id, 'batchid' => $batch1->id)));
            $this->fail('Exception expected if not having capability to unenrol');

        } catch (moodle_exception $e) {
            $this->assertInstanceOf('required_capability_exception', $e);
            $this->assertSame('nopermissions', $e->errorcode);
        }
        $this->assignUserCapability('enrol/manual:unenrol', $context->id, $roleid);
        $this->assertTrue(is_enrolled($context, $user));
        $this->assertTrue(groups_is_member($btcourse->groupid, $user->id));

        external::manual_unenrol($institution->secretkey,
                array(array('userid' => $user->id, 'courseid' => $course1->id)));
        $this->assertFalse(is_enrolled($context, $user));
        $this->assertFalse(groups_is_member($btcourse->groupid, $user->id));
    }

}