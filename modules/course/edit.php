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
use local_providerapi\form\assigncourse;
use local_providerapi\local\institution\institution;

require('../../../../config.php');
require_once($CFG->dirroot . '/local/providerapi/locallib.php');
require_once($CFG->libdir . '/formslib.php');
require_login();

// System context.
$context = context_system::instance();

// Params.
$id = optional_param('id', -1, PARAM_INT);
$delid = optional_param('delid', null, PARAM_INT);
// Baseurl.
$baseurl = new moodle_url('/local/providerapi/modules/course/edit.php');
$institutionurl = new moodle_url('/local/providerapi/modules/course/index.php');
$returnurl = optional_param('returnurl', null, PARAM_LOCALURL);

if ($returnurl) {
    $returnurl = new moodle_url($returnurl);
} else {
    $returnurl = $institutionurl;
}

// Page settings.
$PAGE->set_url($baseurl);
$PAGE->set_context($context);
$PAGE->set_title(get_string('courses', 'local_providerapi'));
$PAGE->set_pagelayout('base');
$PAGE->set_heading(get_string('courses', 'local_providerapi'));

if ($delid and has_capability('local/providerapi:deleteinstitution', $context) and confirm_sesskey()) {
    if (institution::get($delid)->delete()) {
        notification::success(get_string('success'));
    }
    redirect($returnurl);
}

// Nav.
$node = $PAGE->navigation->find('coursemodule', navigation_node::TYPE_SETTING);

if ($node) {
    if ($id === -1) {
        $mynode = $node->add('Assign...', $baseurl);
    } else {
        $mynode = $node->add('Editing...', $baseurl);
    }
    $mynode->make_active();
}

if ($id == -1) {
    $institution = new stdClass();
    $institution->id = -1;
} else {
    $institution = institution::get($id)->get_db_record();

}

$form = new assigncourse(new moodle_url($PAGE->url, array('returnurl' => $returnurl)),
        array(
                'data' => $institution
        ));

if ($form->is_cancelled()) {
    redirect($institutionurl);
} else if ($new = $form->get_data()) {
    if ($new->id == -1) {
        unset($new->id);

        if ($new->id = institution::get($new)->create()) {

            notification::success(get_string('success'));
            redirect($institutionurl);
        } else {
            notification::error(get_string('error', 'local_providerapi'));
            redirect($institutionurl);
        }

    } else {

        notification::success(get_string('success'));
        redirect($institutionurl);

    }
}

$output = $PAGE->get_renderer('local_providerapi');

echo $output->header();

$form->display();

echo $output->footer();