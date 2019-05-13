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

defined('MOODLE_INTERNAL') || die();

/**
 * navigation
 *
 * @param global_navigation $nav
 * @return void
 */
function local_providerapi_extend_navigation(global_navigation $nav) {
    global $CFG;
    if (isloggedin()) {

        $systemcontext = context_system::instance();
        $url = new moodle_url($CFG->wwwroot . '/local/providerapi/modules/institution/index.php');
        $root = $nav->add(get_string('pluginname', 'local_providerapi'), $url, navigation_node::TYPE_SITE_ADMIN, null,
                'providerroot', new pix_icon('database', '', 'local_providerapi'));
        if (has_capability('local/providerapi:viewrootnav', $systemcontext)) {
            $root->showinflatnavigation = true;
        }

        // Institutions.

        $institutions = $root->add(get_string('institutions', 'local_providerapi'),
                new moodle_url('/local/providerapi/modules/institution/index.php'),
                navigation_node::TYPE_SETTING, null, 'institutionmodule', null);
        $institutions->nodetype = navigation_node::NODETYPE_BRANCH;
        // institution::generate_nodes($institutions);
        if (has_capability('local/providerapi:viewbatch', $systemcontext)) {
            $batches = $root->add(get_string('batches', 'local_providerapi'),
                    new moodle_url('/local/providerapi/modules/batch/index.php'), navigation_node::TYPE_SETTING, null,
                    'batchmmodule');
            $batches->nodetype = navigation_node::NODETYPE_BRANCH;
        }
        if (has_capability('local/providerapi:sharedcourse', $systemcontext)) {
            $courses = $root->add(get_string('courses', 'local_providerapi'),
                    new moodle_url('/local/providerapi/modules/course/index.php'), navigation_node::TYPE_SETTING, null,
                    'coursemodule');
            $courses->nodetype = navigation_node::NODETYPE_BRANCH;
        }

    }

}

/**
 * Get icon mapping for font-awesome.
 */
function local_providerapi_get_fontawesome_icon_map() {
    return array(
            'local_providerapi:home' => 'fa fa-home',
            'local_providerapi:setting' => 'fa fa-cog',
            'local_providerapi:settingspin' => 'fa fa-lg fa-cog fa-spin',
            'local_providerapi:fa-hand-o-right' => 'fa-hand-o-right',
            'local_providerapi:database' => 'fa fa-database',
            'local_providerapi:arrow-right' => 'fa fa-arrow-right',
            'local_providerapi:bank' => 'fa fa-university',
            'local_providerapi:sube' => 'fa fa-building',
            'local_providerapi:users' => 'fa fa-users',
            'local_providerapi:try' => 'fa fa-try fa-fw',
            'local_providerapi:percent' => 'fa fa-percent fa-fw',
            'local_providerapi:pdf' => 'fa-file-pdf-o',
            'local_providerapi:square' => 'fa fa-square-o',
            'local_providerapi:pencil-square' => 'fa fa-pencil-square-o',
            'local_providerapi:amountdown' => 'fa fa-sort-amount-down',
            'local_providerapi:cap' => 'fa fa-graduation-cap',
            'local_providerapi:share' => 'fa fa-share-square',
            'local_providerapi:tasks' => 'fa fa-tasks',
            'local_providerapi:list' => 'fa fa-list',
            'local_providerapi:send' => 'fa fa-paper-plane',
            'local_providerapi:draft' => 'fa fa-firstdraft',
            'local_providerapi:public' => 'fa fa-share-square',
            'local_providerapi:comment' => 'fa fa-comment',
            'local_providerapi:down' => 'fa fa-arrow-alt-circle-down',
            'local_providerapi:atlas' => 'fa fa-atlas',
            'local_providerapi:book' => 'fa fa-book',
            'local_providerapi:bookmark' => 'fa fa-bookmark',
            'local_providerapi:school' => 'fa fa-school',
            'local_providerapi:award' => 'fa fa-award',
            'local_providerapi:usergraduate' => 'fa fa-user-graduate',
            'local_providerapi:chalkboard' => 'fa fa-chalkboard',
            'local_providerapi:bookopen' => 'fa fa-book-open',
            'local_providerapi:calendar' => 'fa fa-calendar-alt',
            'local_providerapi:utensils' => 'fa fa-coffee',
            'local_providerapi:cookie' => 'fa fa-cookie-bite',
            'local_providerapi:copy' => 'fa fa-copy',

    );
}