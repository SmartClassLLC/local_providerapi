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
 * Plugin strings are defined here.
 *
 * @package     local_providerapi
 * @category    string
 * @copyright   2019 Çağlar MERSİNLİ <ceremy@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'providerapi';

// Capabilities.
$string['providerapi:add_group_to_course'] = 'Add Course Grouping';
$string['providerapi:add_grouping_to_course'] = 'Add Course Grouping';
$string['providerapi:create_user'] = 'Create User';
$string['providerapi:delete_course_group'] = 'Delete course group';
$string['providerapi:delete_user'] = 'Delete User ';
$string['providerapi:edit_course_group'] = 'Create course group';
$string['providerapi:edit_user'] = 'Edit User';
$string['providerapi:enrol_course'] = 'Enrol Course';
$string['providerapi:get_shared_courses'] = 'Get Shared Course from İnstitution';
$string['providerapi:get_site_info'] = 'Get Site İnfo';
$string['providerapi:check_institution'] = 'Check Institution in moodle instilation';
$string['local/providerapi:viewrootnav'] = 'View root navigation of providerapi plugin';
$string['local/providerapi:viewinstitutionnav'] = 'View institution tab';
$string['local/providerapi:createinstitution'] = 'Create Institution';
$string['local/providerapi:editinstitution'] = 'Edit Institution';
$string['local/providerapi:deleteinstitution'] = 'Delete Institution';
$string['local/providerapi:sharedcourse'] = 'Share Course';
$string['local/providerapi:deletesharedcourse'] = 'Delete Shared Course';
$string['local/providerapi:viewbatch'] = 'View Classroom of Institution';
$string['local/providerapi:addbatch'] = 'Add batch to Institution';
$string['local/providerapi:editbatch'] = 'Edit batch of Institution';
$string['local/providerapi:deletebatch'] = 'Delete batch of Institution';
$string['local/providerapi:assigncourse'] = 'Assign shared course to batch';
$string['local/providerapi:deleteassigncourse'] = 'Delete assign shared course';
$string['local/providerapi:viewassigncourse'] = 'View assign shared course';

// Privacy.
$string['privacy:metadata'] = 'The Providerapi plugin does not store any personal data.';

// Helpbuttons.
$string['fullname'] = 'Institution fullname explain';
$string['fullname_help'] = 'Institution fullname';
$string['secretkeyhelp'] = 'Help';
$string['secretkeyhelp_help'] = 'secretkey for webservice auth.Min 6 , max 10 characters only accept alphanumeric';

$string['shortname'] = 'Institution shortname explain';
$string['shortname_help'] = 'Institution shortname must contain 3 characters';
$string['helpcapacity'] = 'Help';
$string['helpcapacity_help'] = 'Capacity of batch must contain 3 digit';

// Events.
$string['createdinstitution'] = 'Institution Created';
$string['updatedinstitution'] = 'Institution Updated';
$string['deletedinstitution'] = 'Institution Deleted';
$string['createdsharedcourse'] = 'Course Shared';
$string['deletedsharedcourse'] = 'Shared course Deleted';
$string['deletedbtcourse'] = 'Batch \'s course Deleted';
$string['deletedbatch'] = 'Batch Deleted';
$string['createdbatch'] = 'Batch Created';
$string['updatedbatch'] = 'Batch updated';
$string['eventcreated'] = 'The {$a->objname} with id \'{$a->objid}\' has created by {$a->by}';
$string['eventupdated'] = 'The {$a->objname} with id \'{$a->objid}\' has updated by {$a->by}';
$string['eventdeleted'] = 'The {$a->objname} with id \'{$a->objid}\' has deleted by {$a->by}';

// Exceptions.
$string['notexistinstitution'] = 'The Institution is not exist';
$string['notexistbatch'] = 'The Batch is not exist';
$string['missingproperty'] = 'required property missing';
$string['requiredproperty'] = 'required property name \'{$a}\' missing';
$string['hackattempt'] = 'Unauthorized transaction';

// Strings.
$string['institutions'] = 'Institutions';
$string['addinstitution'] = 'Add Institution';
$string['error'] = 'some errors occurred';
$string['secretkey'] = 'Secret Key';
$string['alreadyexists'] = '{$a} already exist';
$string['manage'] = 'Manage';
$string['areyousuredel'] = 'Are you sure delete {$a} ?';
$string['notcorrect'] = 'Not correct';
$string['createdbyplugin'] = 'Created by Providerapi';
$string['courses'] = 'Courses';
$string['selectinstitution'] = 'Select Institution:';
$string['successswitchinstitution'] = 'Institution changed successfully';
$string['somethingwrong'] = 'something went wrong';
$string['havetoselectinstitution'] = 'You have to select institution';
$string['assigncourse'] = 'Share Course';
$string['istitutionsharedcourse'] = '<strong>{$a} \'s</strong> &nbsp; Shared Courses';
$string['istitutionbatches'] = '<strong>{$a} \'s</strong> &nbsp; Batches';
$string['batchcourses'] = '<strong>{$a} \'s</strong> &nbsp; Courses';
$string['batches'] = 'Batches';
$string['addbatch'] = 'Add batch';
$string['capacity'] = 'Capacity';
$string['source'] = 'Source';
$string['assigncourse'] = 'Assign Course';
$string['sharedcourse'] = 'Shared Course';
$string['assigncoursetobatch'] = '{$a} \'s Courses';



