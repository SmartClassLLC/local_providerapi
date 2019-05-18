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
use local_providerapi\local\batch\btcourse;
use local_providerapi\local\institution\institution;
use local_providerapi\output\batch\table_btcourses;

require('../../../../config.php');
require_once($CFG->dirroot . '/local/providerapi/locallib.php');
global $CFG, $PAGE;
require_login();

// System context.
$context = context_system::instance();

// Caps.
require_capability('local/providerapi:viewassigncourse', $context);

// Params.
$batchid = required_param('batchid', PARAM_INT);
$institutionid = required_param('institutionid', PARAM_INT);
// Baseurl.
$baseurl = new moodle_url('/local/providerapi/modules/batch/assigncourse.php', array(
        'batchid' => $batchid,
        'institutionid' => $institutionid
));

// Batch obj.
$batch = batch::get($batchid);

// Check Institution.
if ($institutionid !== local_providerapi_getinstitution()) {
    redirect(new moodle_url('/local/providerapi/modules/batch/index.php'));
}
$institution = institution::get($institutionid);
// Page settings.
$PAGE->set_url($baseurl);
$PAGE->set_context($context);
$PAGE->set_title(get_string('assigncourse', 'local_providerapi'));
$PAGE->set_pagelayout('base');
$PAGE->set_heading(get_string('assigncoursetobatch', 'local_providerapi', $batch->name));
// Nav.
$node = $PAGE->navigation->find('batchmmodule', navigation_node::TYPE_SETTING);
$nodecourse =
        $node->add(get_string('assigncourse', 'local_providerapi'), $baseurl, navigation_node::TYPE_SETTING, null, 'assigncourse');
$nodecourse->make_active();

$output = $PAGE->get_renderer('local_providerapi', 'batch');

$table = new table_btcourses($baseurl, 100);

if (!$table->is_downloading()) {
    echo $output->header();
    if ($batch->source === 'web') {
        echo \html_writer::link('#',
                $output->pix_icon('t/add', get_string('assigncourse', 'local_providerapi')) . get_string('assigncourse',
                        'local_providerapi'),
                ['class' => 'courseassign btn btn-outline-primary btn-lg pull-left',
                        'data-batchid' => $batchid,
                        'data-institutionid' => $institutionid,
                        'data-contextid' => $context->id
                ]);
        echo $output->box('', 'clearfix');
    }
}

list($select, $from, $where, $params) = btcourse::get_sql('b.id = :batchid', array('batchid' => $batchid));
$table->set_sql($select, $from, $where, $params);
$table->set_batch($batch);
echo $output->render_table($table);

$PAGE->requires->js_amd_inline("
  require(['jquery','local_providerapi/addassigncourse'], function($,AddAssignCourse) {
  $(document).ready(function() {
       $('.courseassign').one('click',function(e){
       e.preventDefault();
       var batchid = $(this).data('batchid');
       var contextid = $(this).data('contextid');
       var institutionid = $(this).data('institutionid');
       AddAssignCourse.init(contextid,batchid,institutionid);
       });
       });
    });
");

if (!$table->is_downloading()) {
    echo $output->footer();
}