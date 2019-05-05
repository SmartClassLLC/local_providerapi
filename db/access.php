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
 * Plugin capabilities are defined here.
 *
 * @package     local_providerapi
 * @category    access
 * @copyright   2019 Çağlar MERSİNLİ <ceremy@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = [

    'local/providerapi:get_shared_courses' => [
        'captype' => 'view',
        'contextlevel' => 10,
        'archetypes' => [
        ],
    ],
    'local/providerapi:check_institution' => [
        'captype' => 'view',
        'contextlevel' => 10,
        'archetypes' => [
        ],
    ],
    'local/providerapi:get_site_info' => [
        'captype' => 'view',
        'contextlevel' => 10,
        'archetypes' => [
        ],
    ],

    'local/providerapi:enrol_course' => [
        'captype' => 'write',
        'contextlevel' => 10,
        'archetypes' => [
        ],
    ],

    'local/providerapi:add_group_to_course' => [
        'captype' => 'write',
        'contextlevel' => 10,
        'archetypes' => [
        ],
    ],

    'local/providerapi:add_grouping_to_course' => [
        'captype' => 'write',
        'contextlevel' => 10,
        'archetypes' => [
        ],
    ],

    'local/providerapi:create_user' => [
        'captype' => 'write',
        'contextlevel' => 10,
        'archetypes' => [
        ],
    ],

    'local/providerapi:edit_user' => [
        'captype' => 'write',
        'contextlevel' => 10,
        'archetypes' => [
        ],
    ],

    'local/providerapi:delete_user' => [
        'captype' => 'write',
        'contextlevel' => 10,
        'archetypes' => [
        ],
    ],

    'local/providerapi:delete_course_group' => [
        'captype' => 'write',
        'contextlevel' => 10,
        'archetypes' => [
        ],
    ],

    'local/providerapi:edit_course_group' => [
        'captype' => 'write',
        'contextlevel' => 10,
        'archetypes' => [
        ],
    ],
];
