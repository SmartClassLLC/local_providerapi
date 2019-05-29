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
use local_providerapi\output\batch\table_batchmembers;

require('../../../../config.php');
require_once($CFG->dirroot . '/local/providerapi/locallib.php');

global $CFG, $PAGE, $OUTPUT;
require_login();

// System context.
$context = context_system::instance();

// Caps.
require_capability('local/providerapi:viewbatchmembers', $context);
// Params.
$batchid = required_param('id', PARAM_INT);
$returnurl = optional_param('returnurl', null, PARAM_LOCALURL);

// Baseurl.
$baseurl = new moodle_url('/local/providerapi/modules/batch/members.php', array('id' => $batchid));

$batchurl = new moodle_url('/local/providerapi/modules/batch/index.php');
if ($returnurl) {
    $baseurl->param('returnurl', $returnurl);
    $returnurl = new moodle_url($returnurl);
} else {
    $returnurl = $batchurl;
}
// Page settings.
$PAGE->set_url($baseurl);
$PAGE->set_context($context);
$PAGE->set_title(get_string('batchmembers', 'local_providerapi'));
$PAGE->set_pagelayout('base');
$PAGE->set_heading(get_string('batchmembers', 'local_providerapi'));

// Nav.
$node = $PAGE->navigation->find('batchmmodule', navigation_node::TYPE_SETTING);
$viewnode = $node->add(get_string('batchmembers', 'local_providerapi'), $baseurl, navigation_node::TYPE_SETTING, null,
        'batchmembers');
$viewnode->make_active();

$output = $PAGE->get_renderer('local_providerapi', 'batch');
$batch = batch::get($batchid);

$table = new table_batchmembers($baseurl, 100);
list($select, $from, $where, $params) = $batch->get_member_sql();
$table->set_sql($select, $from, $where, $params);

if (!$table->is_downloading()) {
    echo $output->header();
    // Check Institution.
    if (!$institutionid = local_providerapi_getinstitution()) {
        $output->notifyselectinstitution();
        die();
    }
    if (has_capability('local/providerapi:assignbatchmembers', $context) && $batch->source == PROVIDERAPI_SOURCEWEB) {
        $output->addbutton(new moodle_url('/local/providerapi/modules/batch/assignusers.php',
                array('id' => $batchid, 'institutionid' => $institutionid, 'returnurl' => $baseurl->out_as_local_url())),
                get_string('assignmembers', 'local_providerapi'));
    }
}
echo $output->render_table($table);

if (!$table->is_downloading()) {
    echo $output->footer();
}