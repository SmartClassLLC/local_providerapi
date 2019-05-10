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

/**
 * Class addinstitution
 *
 * @package local_providerapi\form
 */
class assigncourse extends moodleform {

    /**
     * Form definition. Abstract method - always override!
     */
    protected function definition() {
        $mform = $this->_form;

        if (!is_array($this->_customdata)) {
            throw new coding_exception('invalid custom data');
        }

        $data = $this->_customdata['data'];

        // Add some extra hidden fields.

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('header', 'assigncourse', get_string('general'));

        $mform->addElement('course', 'courseids', get_string('courses'), ['multiple' => true]);
        $mform->setType('courseids', PARAM_INT);
        $mform->addRule('courseids', get_string('required'), 'required', null, 'client');

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
