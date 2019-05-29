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
        'local/providerapi:viewrootnav' => [
                'captype' => 'view',
                'contextlevel' => 10,
                'archetypes' => [
                        'manager' => CAP_ALLOW
                ],
        ],
        'local/providerapi:viewinstitutionnav' => [
                'captype' => 'view',
                'contextlevel' => 10,
                'archetypes' => [
                        'manager' => CAP_ALLOW
                ],
        ],
        'local/providerapi:createinstitution' => [
                'captype' => 'write',
                'contextlevel' => 10,
                'archetypes' => [
                        'manager' => CAP_ALLOW
                ],
        ],
        'local/providerapi:editinstitution' => [
                'captype' => 'write',
                'contextlevel' => 10,
                'archetypes' => [
                        'manager' => CAP_ALLOW
                ],
        ],
        'local/providerapi:deleteinstitution' => [
                'captype' => 'write',
                'contextlevel' => 10,
                'archetypes' => [
                        'manager' => CAP_ALLOW
                ],
        ],
        'local/providerapi:check_institution' => [
                'captype' => 'view',
                'contextlevel' => 10,
                'archetypes' => [
                ],
        ],
        'local/providerapi:viewinstitutionmembers' => [
                'captype' => 'view',
                'contextlevel' => 10,
                'archetypes' => [
                        'manager' => CAP_ALLOW
                ],
        ],
        'local/providerapi:assigninstitutionmembers' => [
                'captype' => 'view',
                'contextlevel' => 10,
                'archetypes' => [
                        'manager' => CAP_ALLOW
                ],
        ], 'local/providerapi:unassigninstitutionmembers' => [
                'captype' => 'view',
                'contextlevel' => 10,
                'archetypes' => [
                        'manager' => CAP_ALLOW
                ],
        ],
        'local/providerapi:sharedcourse' => [
                'captype' => 'write',
                'contextlevel' => 10,
                'archetypes' => [
                        'manager' => CAP_ALLOW
                ],
        ],
        'local/providerapi:assigncourse' => [
                'captype' => 'write',
                'contextlevel' => 10,
                'archetypes' => [
                        'manager' => CAP_ALLOW
                ],
        ],
        'local/providerapi:assignbtcourse' => [
                'captype' => 'write',
                'contextlevel' => 10,
                'archetypes' => [
                        'manager' => CAP_ALLOW
                ],
        ],
        'local/providerapi:unassignbtcourse' => [
                'captype' => 'write',
                'contextlevel' => 10,
                'archetypes' => [
                        'manager' => CAP_ALLOW
                ],
        ],
        'local/providerapi:viewassignbtcourse' => [
                'captype' => 'write',
                'contextlevel' => 10,
                'archetypes' => [
                        'manager' => CAP_ALLOW
                ],
        ],
        'local/providerapi:viewassigncourse' => [
                'captype' => 'write',
                'contextlevel' => 10,
                'archetypes' => [
                        'manager' => CAP_ALLOW
                ],
        ],
        'local/providerapi:deleteassigncourse' => [
                'captype' => 'write',
                'contextlevel' => 10,
                'archetypes' => [
                        'manager' => CAP_ALLOW
                ],
        ],
        'local/providerapi:viewbatch' => [
                'captype' => 'write',
                'contextlevel' => 10,
                'archetypes' => [
                        'manager' => CAP_ALLOW
                ],
        ],
        'local/providerapi:addbatch' => [
                'captype' => 'write',
                'contextlevel' => 10,
                'archetypes' => [
                        'manager' => CAP_ALLOW
                ],
        ],
        'local/providerapi:editbatch' => [
                'captype' => 'write',
                'contextlevel' => 10,
                'archetypes' => [
                        'manager' => CAP_ALLOW
                ],
        ],
        'local/providerapi:deletebatch' => [
                'captype' => 'write',
                'contextlevel' => 10,
                'archetypes' => [
                        'manager' => CAP_ALLOW
                ],
        ],
        'local/providerapi:viewbatchmembers' => [
                'captype' => 'write',
                'contextlevel' => 10,
                'archetypes' => [
                        'manager' => CAP_ALLOW
                ],
        ],
        'local/providerapi:assignbatchmembers' => [
                'captype' => 'write',
                'contextlevel' => 10,
                'archetypes' => [
                        'manager' => CAP_ALLOW
                ],
        ],
        'local/providerapi:unassignbatchmembers' => [
                'captype' => 'write',
                'contextlevel' => 10,
                'archetypes' => [
                        'manager' => CAP_ALLOW
                ],
        ],
        'local/providerapi:deletesharedcourse' => [
                'captype' => 'write',
                'contextlevel' => 10,
                'archetypes' => [
                        'manager' => CAP_ALLOW
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
        'local/providerapi:update_user' => [
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
        ], 'local/providerapi:get_users' => [
                'captype' => 'read',
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
