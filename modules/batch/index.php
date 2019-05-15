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
 * course index
 *
 * long_description
 *
 * @package    local_providerapi
 * @copyright  2019 çağlar MERSİNLİ <ceremy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_providerapi\local\batch\batch;
use local_providerapi\local\course\course;
use local_providerapi\local\institution\institution;
use local_providerapi\output\batch\table_batches;

require('../../../../config.php');
require_once($CFG->dirroot . '/local/providerapi/locallib.php');

global $CFG, $PAGE;
require_login();

// System context.
$context = context_system::instance();

// Caps.
require_capability('local/providerapi:viewbatch', $context);
// Baseurl.
$baseurl = new moodle_url('/local/providerapi/modules/batch/index.php');

// Page settings.
$PAGE->set_url($baseurl);
$PAGE->set_context($context);
$PAGE->set_title(get_string('batches', 'local_providerapi'));
$PAGE->set_pagelayout('base');
$PAGE->set_heading(get_string('batches', 'local_providerapi'));

// Nav.
$node = $PAGE->navigation->find('providerroot', navigation_node::TYPE_SITE_ADMIN);

$output = $PAGE->get_renderer('local_providerapi');

$table = new table_batches($baseurl, 100);

if (!$table->is_downloading()) {
    echo $output->header();
    if ($node) {
        call_user_func_array('print_tabs', $node->get_tabs_array());
    }
    $output->institutionmenu();

    // Check Institution.
    if (!$institutionid = local_providerapi_getinstitution()) {
        $output->notifyselectinstitution();
        die();
    }

    $output->addbutton(new moodle_url('/local/providerapi/modules/batch/edit.php',
            array('id' => -1, 'institutionid' => $institutionid)),
            get_string('addbatch', 'local_providerapi'));

}
$institution = institution::get($SESSION->institution);
list($select, $from, $where, $params) = batch::get_sql($institutionid);
$table->set_sql($select, $from, $where, $params);
$table->set_istitutionname($institution->name);
echo $output->render_table($table);

if (!$table->is_downloading()) {
    echo $output->footer();
}