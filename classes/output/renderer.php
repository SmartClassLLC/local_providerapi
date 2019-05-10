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
 * Renderer
 *
 * long_description
 *
 * @package    local_providerapi
 * @copyright  2019 çağlar MERSİNLİ <ceremy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_providerapi\output;

use local_providerapi\local\institution\institution;
use renderable;
use single_button;

defined('MOODLE_INTERNAL') || die();

/**
 * Class renderer
 *
 * @package local_providerapi\output\institution
 */
class renderer extends \plugin_renderer_base {

    /**
     * @param renderable $renderable
     * @return string
     */
    public function render_table(renderable $renderable) {
        $o = '';
        ob_start();
        $renderable->out($renderable->pagesize, false);
        $o .= ob_get_contents();
        ob_end_clean();
        return $o;
    }

    /**
     * @param \moodle_url $url
     * @param string $text
     */
    public function addbutton(\moodle_url $url, string $text) {
        $addbutton = new single_button($url, $text, 'post', true);
        $addbutton->class = 'singlebutton pull-left';
        $addbutton->tooltip = $text;
        echo $this->render($addbutton);
        echo $this->box('', 'clearfix');
    }

    /**
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function institutionmenu() {
        globaL $SESSION, $PAGE;

        $selected = null;
        if (isset($SESSION->institution) && !empty($SESSION->institution)) {
            $selected = $SESSION->institution;
        }

        $url = new \moodle_url('/local/providerapi/switch.php',
                array('returnurl' => $PAGE->url->out_as_local_url(), 'sesskey' => sesskey()));
        $options = institution::get_menu();
        $select = new \single_select($url, 'institutionid', $options, $selected);
        $select->label = get_string('selectinstitution', 'local_providerapi');
        $select->class = 'pull-right';

        echo $this->render($select);
        echo $this->box('', 'clearfix');
    }

    /**
     * @throws \coding_exception
     */
    public function checkinstitution() {
        global $OUTPUT, $SESSION;
        if (!isset($SESSION->institution) || empty($SESSION->institution)) {
            echo $OUTPUT->notification(get_string('havetoselectinstitution', 'local_providerapi'), 'error');
            echo $OUTPUT->footer();
            die();
        }
    }

}
