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

/*
* services
*
* long_description
*
* @package    local_providerapi
* @copyright  2019 çağlar MERSİNLİ <ceremy@gmail.com>
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

defined('MOODLE_INTERNAL') || die();

$functions = array(

        'local_providerapi_checkinstitution' => array(
                'classname' => 'local_providerapi\webservice\institution\external',
                'methodname' => 'checkinstitution',
                'description' => 'check institution exist moodle',
                'type' => 'read',
                'capabilities' => 'local/providerapi:check_institution',
                'services' => array('providerapi')
        ),
        'local_providerapi_assigncourseweb' => array(
                'classname' => 'local_providerapi\webservice\batch\external',
                'methodname' => 'assigncourseweb',
                'description' => 'Assign course via web',
                'type' => 'write',
                'capabilities' => 'local/providerapi:assigncourse',
                'ajax' => true
        ),
        'local_providerapi_create_users' => array(
                'classname' => 'local_providerapi\webservice\institution\external',
                'methodname' => 'create_users',
                'description' => 'User Create in moodle and this user add institutiton\'s cohort',
                'type' => 'write',
                'capabilities' => 'local/providerapi:create_user',
                'services' => array('providerapi')
        ),
        'local_providerapi_update_users' => array(
                'classname' => 'local_providerapi\webservice\institution\external',
                'methodname' => 'update_users',
                'description' => 'Update user in moodle and this user update institutiton\'s cohort',
                'type' => 'write',
                'capabilities' => 'local/providerapi:update_user',
                'services' => array('providerapi')
        ),
        'local_providerapi_delete_users' => array(
                'classname' => 'local_providerapi\webservice\institution\external',
                'methodname' => 'delete_users',
                'description' => 'Delete user in moodle and this user delete institutiton\'s cohort',
                'type' => 'write',
                'capabilities' => 'local/providerapi:delete_user',
                'services' => array('providerapi')
        ),
        'local_providerapi_get_users' => array(
                'classname' => 'local_providerapi\webservice\institution\external',
                'methodname' => 'get_users',
                'description' => 'Get User Information',
                'type' => 'read',
                'capabilities' => 'local/providerapi:get_users,moodle/user:viewdetails',
                'services' => array('providerapi')
        ),
        'local_providerapi_get_courses' => array(
                'classname' => 'local_providerapi\webservice\course\external',
                'methodname' => 'get_courses',
                'description' => 'Get Moodle shared course',
                'type' => 'read',
                'capabilities' => 'local/providerapi:viewassigncourse',
                'services' => array('providerapi')
        ),
        'local_providerapi_create_batches' => array(
                'classname' => 'local_providerapi\webservice\batch\external',
                'methodname' => 'create_batches',
                'description' => 'Create Batch',
                'type' => 'write',
                'capabilities' => 'local/providerapi:addbatch',
                'services' => array('providerapi')
        ),
        'local_providerapi_update_batches' => array(
                'classname' => 'local_providerapi\webservice\batch\external',
                'methodname' => 'update_batches',
                'description' => 'Update Batch',
                'type' => 'write',
                'capabilities' => 'local/providerapi:editbatch',
                'services' => array('providerapi')
        ),
        'local_providerapi_delete_batches' => array(
                'classname' => 'local_providerapi\webservice\batch\external',
                'methodname' => 'delete_batches',
                'description' => 'Delete Batch',
                'type' => 'write',
                'capabilities' => 'local/providerapi:deletebatch',
                'services' => array('providerapi')
        ),
        'local_providerapi_get_batches' => array(
                'classname' => 'local_providerapi\webservice\batch\external',
                'methodname' => 'get_batches',
                'description' => 'Get all Batches',
                'type' => 'read',
                'capabilities' => 'local/providerapi:viewbatch',
                'services' => array('providerapi')
        ),
        'local_providerapi_assign_batchmembers' => array(
                'classname' => 'local_providerapi\webservice\batch\external',
                'methodname' => 'assign_batchmembers',
                'description' => 'Assign user to batch',
                'type' => 'write',
                'capabilities' => 'local/providerapi:assignbatchmembers',
                'services' => array('providerapi')
        ),
        'local_providerapi_unassign_batchmembers' => array(
                'classname' => 'local_providerapi\webservice\batch\external',
                'methodname' => 'unassign_batchmembers',
                'description' => 'Unassign user from batch',
                'type' => 'write',
                'capabilities' => 'local/providerapi:unassignbatchmembers',
                'services' => array('providerapi')
        ),
        'local_providerapi_get_batchmembers' => array(
                'classname' => 'local_providerapi\webservice\batch\external',
                'methodname' => 'get_batchmembers',
                'description' => 'Get users from batch',
                'type' => 'read',
                'capabilities' => 'local/providerapi:viewbatchmembers',
                'services' => array('providerapi')
        ),
        'local_providerapi_assign_course_to_batch' => array(
                'classname' => 'local_providerapi\webservice\course\external',
                'methodname' => 'assign_course_to_batch',
                'description' => 'Assign course to batch',
                'type' => 'write',
                'capabilities' => 'local/providerapi:assignbtcourse',
                'services' => array('providerapi')
        ),
        'local_providerapi_unassign_course_to_batch' => array(
                'classname' => 'local_providerapi\webservice\course\external',
                'methodname' => 'unassign_course_to_batch',
                'description' => 'Unassign course to batch',
                'type' => 'write',
                'capabilities' => 'local/providerapi:unassignbtcourse',
                'services' => array('providerapi')
        ),
        'local_providerapi_get_batch_courses' => array(
                'classname' => 'local_providerapi\webservice\course\external',
                'methodname' => 'get_batch_courses',
                'description' => 'Get courses of batch',
                'type' => 'read',
                'capabilities' => 'local/providerapi:unassignbtcourse',
                'services' => array('providerapi')
        ),
        'local_providerapi_get_lti_info' => array(
                'classname' => 'local_providerapi\webservice\course\external',
                'methodname' => 'get_lti_info',
                'description' => 'Get lti info of course',
                'type' => 'read',
                'capabilities' => 'local/providerapi:get_lti_info',
                'services' => array('providerapi')
        ),
        'local_providerapi_manual_enrol' => array(
                'classname' => 'local_providerapi\webservice\course\external',
                'methodname' => 'manual_enrol',
                'description' => 'Enrol and add batch group',
                'type' => 'write',
                'capabilities' => 'enrol/manual:enrol,moodle/course:view,moodle/role:assign',
                'services' => array('providerapi')
        ),
        'local_providerapi_manual_unenrol' => array(
                'classname' => 'local_providerapi\webservice\course\external',
                'methodname' => 'manual_unenrol',
                'description' => 'Complete Unenrol and drop all batch group',
                'type' => 'write',
                'capabilities' => 'enrol/manual:unenrol',
                'services' => array('providerapi')
        ),
        'local_providerapi_get_grade_items' => array(
                'classname' => 'local_providerapi\webservice\course\external',
                'methodname' => 'get_grade_items',
                'description' => 'Get user\'s grades in course',
                'type' => 'read',
                'capabilities' => 'gradereport/user:view,moodle/grade:viewall,moodle/site:accessallgroups',
                'services' => array('providerapi')
        )

);

$services = array(
        'ProviderApi' => array(
                'functions' => array(
                        'local_providerapi_checkinstitution'
                ),
                'restrictedusers' => 1,
                'enabled' => 1,
                'shortname' => 'providerapi'
        )
);
