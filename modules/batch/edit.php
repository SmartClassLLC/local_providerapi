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

use core\notification;
use local_providerapi\form\addbatch;
use local_providerapi\local\batch\batch;
use local_providerapi\local\institution\institution;

require('../../../../config.php');
require_once($CFG->dirroot . '/local/providerapi/locallib.php');
require_once($CFG->libdir . '/formslib.php');
require_login();

// System context.
$context = context_system::instance();

// Params.
$id = optional_param('id', -1, PARAM_INT);
$institutionid = required_param('institutionid', PARAM_INT);
$delid = optional_param('delid', null, PARAM_INT);
// Baseurl.
$baseurl = new moodle_url('/local/providerapi/modules/batch/edit.php');
$batchurl = new moodle_url('/local/providerapi/modules/batch/index.php');
$returnurl = optional_param('returnurl', null, PARAM_LOCALURL);

if ($returnurl) {
    $returnurl = new moodle_url($returnurl);
} else {
    $returnurl = $batchurl;
}

// Page settings.
$PAGE->set_url($baseurl);
$PAGE->set_context($context);
$PAGE->set_title(get_string('batches', 'local_providerapi'));
$PAGE->set_pagelayout('base');
$PAGE->set_heading(get_string('batches', 'local_providerapi'));

if ($delid and has_capability('local/providerapi:deletebatch', $context) and confirm_sesskey()) {
    if (institution::get($delid)->delete()) {
        notification::success(get_string('success'));
    }
    redirect($returnurl);
}

// Cap edit?
if ($id != -1) {
    require_capability('local/providerapi:editbatch', $context);
}

// Nav.
$node = $PAGE->navigation->find('batchmodule', navigation_node::TYPE_SETTING);

if ($node) {
    if ($id === -1) {
        $mynode = $node->add('Adding...', $baseurl);
    } else {
        $mynode = $node->add('Editing...', $baseurl);
    }
    $mynode->make_active();
}

if ($id == -1) {
    $batch = new stdClass();
    $batch->id = -1;
    $batch->institutionid = $institutionid;
} else {
    $batch = batch::get($id)->get_db_record();
}

$form = new addbatch(new moodle_url($PAGE->url, array('returnurl' => $returnurl)),
        array(
                'data' => $batch
        ));

if ($form->is_cancelled()) {
    redirect($batchurl);
} else if ($new = $form->get_data()) {
    if ($new->id == -1) {
        unset($new->id);
        if ($new->id = batch::get($new)->create()) {
            notification::success(get_string('success'));
            redirect($batchurl);
        } else {
            notification::error(get_string('error', 'local_providerapi'));
            redirect($batchurl);
        }

    } else {
        batch::get($new)->update();
        notification::success(get_string('success'));
        redirect($batchurl);

    }
}

$output = $PAGE->get_renderer('local_providerapi', 'batch');

echo $output->header();

$form->display();

echo $output->footer();