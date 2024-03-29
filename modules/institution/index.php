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

use local_providerapi\local\institution\institution;
use local_providerapi\output\institution\table_institutions;

require('../../../../config.php');
require_once($CFG->dirroot . '/local/providerapi/locallib.php');

global $CFG, $PAGE, $OUTPUT;
require_login();

// System context.
$context = context_system::instance();

// Caps.

// Baseurl.
$baseurl = new moodle_url('/local/providerapi/modules/institution/index.php');

// Page settings.
$PAGE->set_url($baseurl);
$PAGE->set_context($context);
$PAGE->set_title(get_string('institutions', 'local_providerapi'));
$PAGE->set_pagelayout('base');
$PAGE->set_heading(get_string('institutions', 'local_providerapi'));

// Nav.
$node = $PAGE->navigation->find('providerroot', navigation_node::TYPE_SITE_ADMIN);

$output = $PAGE->get_renderer('local_providerapi', 'institution');

$table = new table_institutions($baseurl, 50);
list($select, $from, $where, $params) = institution::get_sql();
$table->set_sql($select, $from, $where, $params);

if (!$table->is_downloading()) {
    echo $output->header();
    if ($node) {
        call_user_func_array('print_tabs', $node->get_tabs_array());
    }
    if (has_capability('local/providerapi:createinstitution', $context)) {
        $output->addbutton(new moodle_url('/local/providerapi/modules/institution/editinstitution.php', array('id' => -1)),
                get_string('addinstitution', 'local_providerapi'));
    }
}
echo $output->render_table($table);

if (!$table->is_downloading()) {
    echo $output->footer();
}