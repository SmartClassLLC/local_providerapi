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
