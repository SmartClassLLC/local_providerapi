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

}
