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

namespace local_providerapi\form;

use local_providerapi\local\batch\batch;
use local_providerapi\local\batch\btcourse;
use local_providerapi\local\course\course;
use moodleform;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/formslib.php');
/**
 * Class addinstitution
 *
 * @package local_providerapi\form
 */
class assigncourse_form extends moodleform {

    /**
     * Form definition. Abstract method - always override!
     */
    protected function definition() {
        $mform = $this->_form;
        $batchid = $this->_customdata['batchid'];
        $batch = batch::get($batchid);
        $institutionid = $batch->institutionid;
        $allcoursesmenu = course::get_course_menu($institutionid);
        $options = array_diff_key($allcoursesmenu, array_flip(btcourse::get_btsharecourseids($batchid)));

        // Add some extra hidden fields.
        $mform->addElement('hidden', 'source', 'web');
        $mform->setType('source', PARAM_TEXT);
        $mform->addElement('hidden', 'batchid', $batchid);
        $mform->setType('batchid', PARAM_INT);

        $mform->addElement('header', 'assigncourse', get_string('general'));

        $mform->addElement('autocomplete', 'sharedcourseids', get_string('assigncourse', 'local_providerapi'), $options,
                ['multiple' => true]);
        $mform->setType('sharedcourseid', PARAM_INT);
        $mform->disable_form_change_checker();

    }

    /**
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        global $DB;
        $data = (object) $data;
        $err = array();
        if (!empty($data->sharedcourseids)) {
            foreach ($data->sharedcourseids as $sharedcourseid) {
                if ($DB->record_exists('local_providerapi_btcourses',
                        array('batchid' => $data->batchid, 'sharedcourseid' => $sharedcourseid))) {
                    $err['sharedcourseid'] = get_string('alreadyexists', 'local_providerapi', 'some course');
                }
            }
        }
        return $err;
    }
}
