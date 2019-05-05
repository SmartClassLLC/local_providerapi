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

    'local_providerapi_checkInstitution' => array(
        'classname' => 'local_providerapi\webservice\institution\external',
        'methodname' => 'checkInstitution',
        'description' => 'check institution exist moodle',
        'type' => 'read',
        'capabilities' => 'local/providerapi:check_institution'
    )

);

$services = array(
    'ProviderApi' => array(
        'functions' => array(
            'local_providerapi_checkInstitution'
        ),
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'providerapi'
    )
);
