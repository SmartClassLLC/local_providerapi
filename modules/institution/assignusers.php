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
use local_providerapi\local\selectors\existing_institution_users;
use local_providerapi\local\selectors\potential_institution_users;

require('../../../../config.php');
require_once($CFG->dirroot . '/local/providerapi/locallib.php');

global $CFG, $PAGE, $OUTPUT;
require_login();

// System context.
$context = context_system::instance();
// Caps.
require_capability('local/providerapi:assigninstitutionmembers', $context);
// Params.
$institutionid = required_param('id', PARAM_INT);
$returnurl = optional_param('returnurl', null, PARAM_LOCALURL);

// Baseurl.
$baseurl = new moodle_url('/local/providerapi/modules/institution/assignusers.php', array('id' => $institutionid));

$institutionmember = new moodle_url('/local/providerapi/modules/institution/members.php', array('id' => $institutionid));
if ($returnurl) {
    $baseurl->param('returnurl', $returnurl);
    $returnurl = new moodle_url($returnurl);
} else {
    $returnurl = $institutionmember;
}

// Page settings.
$PAGE->set_url($baseurl);
$PAGE->set_context($context);
$PAGE->set_title(get_string('assignmembers', 'local_providerapi'));
$PAGE->set_pagelayout('base');
$PAGE->set_heading(get_string('assignmembers', 'local_providerapi'));

// Nav.
$node = $PAGE->navigation->find('institutionmodule', navigation_node::TYPE_SETTING);
$viewnode =
        $node->add(get_string('institutionsmembers', 'local_providerapi'), $institutionmember, navigation_node::TYPE_SETTING, null,
                'viewmembers');
$assignnode = $viewnode->add(get_string('assignmembers', 'local_providerapi'), $baseurl, navigation_node::TYPE_SETTING, null,
        'assignmembers');
$assignnode->make_active();

if (optional_param('cancel', false, PARAM_BOOL)) {
    redirect($returnurl);
}

$output = $PAGE->get_renderer('local_providerapi', 'institution');

echo $output->header();
// Get the user_selector we will need.
$potentialuserselector = new potential_institution_users('addselect', array('institutionid' => $institutionid));
$existinguserselector = new existing_institution_users('removeselect', array('institutionid' => $institutionid));

// Process incoming user assignments to the cohort.
$institution = institution::get($institutionid);
if (optional_param('add', false, PARAM_BOOL) && confirm_sesskey()) {
    $userstoassign = $potentialuserselector->get_selected_users();
    if (!empty($userstoassign)) {

        foreach ($userstoassign as $adduser) {
            $institution->add_member($adduser->id);
        }

        $potentialuserselector->invalidate_selected_users();
        $existinguserselector->invalidate_selected_users();
    }
}

// Process removing user assignments to the cohort.
if (optional_param('remove', false, PARAM_BOOL) && confirm_sesskey()) {
    $userstoremove = $existinguserselector->get_selected_users();
    if (!empty($userstoremove)) {
        foreach ($userstoremove as $removeuser) {
            $institution->remove_member($removeuser->id);
        }
        $potentialuserselector->invalidate_selected_users();
        $existinguserselector->invalidate_selected_users();
    }
}

// Print the form.
?>
    <form id="assignform" method="post" action="<?php echo $PAGE->url ?>">
        <div>
            <input type="hidden" name="sesskey" value="<?php echo sesskey() ?>"/>
            <input type="hidden" name="returnurl" value="<?php echo $returnurl->out_as_local_url() ?>"/>

            <table summary="" class="admintable groupmanagementtable generaltable" cellspacing="0">
                <tr>
                    <td id="existingcell" class="col-md-5">
                        <p><label for="removeselect"><?php print_string('currentusers', 'cohort'); ?></label></p>
                        <?php $existinguserselector->display() ?>
                    </td>
                    <td id="buttonscell" class="col-md-2">
                        <div id="addcontrols">
                            <input name="add" id="add" type="submit"
                                   value="<?php echo $OUTPUT->larrow() . '&nbsp;' . s(get_string('add')); ?>"
                                   title="<?php p(get_string('add')); ?>"/><br/>
                        </div>

                        <div id="removecontrols">
                            <input name="remove" id="remove" type="submit"
                                   value="<?php echo s(get_string('remove')) . '&nbsp;' . $OUTPUT->rarrow(); ?>"
                                   title="<?php p(get_string('remove')); ?>"/>
                        </div>
                    </td>
                    <td id="potentialcell" class="col-md-5">
                        <p><label for="addselect"><?php print_string('potusers', 'cohort'); ?></label></p>
                        <?php $potentialuserselector->display() ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" id='backcell'>
                        <input type="submit" name="cancel" value="<?php p(get_string('back', 'local_providerapi')); ?>"/>
                    </td>
                </tr>
            </table>
        </div>
    </form>

<?php

echo $output->footer();
