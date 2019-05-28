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

use coding_exception;
use local_providerapi\local\batch\batch;
use moodleform;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Class addinstitution
 *
 * @package local_providerapi\form
 */
class addbatch extends moodleform {

    /**
     * Form definition. Abstract method - always override!
     */
    protected function definition() {
        $mform = $this->_form;

        if (!is_array($this->_customdata)) {
            throw new coding_exception('invalid custom data');
        }
        $data = $this->_customdata['data'];
        if (!property_exists($data, 'institutionid')) {
            throw new \moodle_exception('required');
        }

        // Add some extra hidden fields.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'institutionid');
        $mform->setType('institutionid', PARAM_INT);

        $mform->addElement('header', 'classroom', get_string('general'));

        $mform->addElement('text', 'name', get_string('name'), ['maxlength' => 254, 'size' => 50]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required', null, 'client');
        // Capacity.
        $mform->addElement('text', 'capacity', get_string('capacity', 'local_providerapi'), ['maxlength' => 3, 'size' => 5]);
        $mform->setType('capacity', PARAM_INT);
        $mform->addRule('capacity', null, 'numeric', null, 'client');
        $mform->addRule('capacity', null, 'maxlength', '3', 'client');
        $mform->addHelpButton('capacity', 'helpcapacity', 'local_providerapi');

        $this->add_action_buttons();
        $this->set_data($data);

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
        $select = "name = ? AND institutionid = ?";
        $param = array($data->name, $data->institutionid);
        if (!empty($data->id)) {
            $select .= " AND id <> ?";
            $param[] = $data->id;
        }
        if ($DB->record_exists_select(batch::$dbname, $select, $param)) {
            $err['name'] = get_string('alreadyexists', 'local_providerapi', 'name');
        }

        if (isset($data->capacity)) {
            $lenghtcapacity = strlen($data->capacity);
            if ($lenghtcapacity > 3 || $data->capacity < 0) {
                $err['capacity'] = get_string('notcorrect', 'local_providerapi');
            }
        }

        return $err;
    }
}