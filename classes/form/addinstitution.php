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
use moodleform;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Class addinstitution
 *
 * @package local_providerapi\form
 */
class addinstitution extends moodleform {

    /**
     * Form definition. Abstract method - always override!
     */
    protected function definition() {
        $mform = $this->_form;

        if (!is_array($this->_customdata)) {
            throw new coding_exception('invalid custom data');
        }
        $data = $this->_customdata['data'];
        $editoroptions = $this->_customdata['editoroptions'];

        // Add some extra hidden fields.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('header', 'institution', get_string('general'));

        $mform->addElement('text', 'fullname', get_string('name'), ['maxlength' => 254, 'size' => 50]);
        $mform->setType('fullname', PARAM_TEXT);
        $mform->addRule('fullname', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('fullname', 'fullname', 'local_providerapi');
        // Shortname.
        $mform->addElement('text', 'shortname', get_string('shortname'), ['maxlength' => 3, 'size' => 10]);
        $mform->setType('shortname', PARAM_TEXT);
        $mform->addRule('shortname', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('shortname', 'shortname', 'local_providerapi');

        $this->add_action_buttons();
        $this->set_data($data);

    }

    /**
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $data = (object) $data;
        $err = array();

        return $err;
    }
}