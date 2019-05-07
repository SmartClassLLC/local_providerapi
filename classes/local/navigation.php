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

namespace local_providerapi\local;
defined('MOODLE_INTERNAL') || die();

use action_menu;
use action_menu_link_secondary;
use context_system;
use moodle_url;
use navigation_node;
use pix_icon;

/**
 * Trait navigation
 *
 * @package local_providerapi\local
 */
trait navigation {

    /**
     * @param navigation_node $node
     * @param null $pages
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function generate_nodes($node, $pages = null) {
        if (empty($pages)) {
            $pages = self::$pages;
        }
        $systemcontext = context_system::instance();
        if (!empty($pages)) {
            foreach ($pages as $key => $page) {
                $url = (isset($page['url']) and !empty($page['url'])) ? new moodle_url($page['url']) : null;
                $icon = (isset($page['icon']) and
                        !empty($page['icon'])) ? new pix_icon($page['icon'], '', 'local_providerapi') : null;
                $nodemain = $node->add(get_string($page['text'], 'local_providerapi'),
                        $url, navigation_node::TYPE_SETTING, null, $key, $icon);
                $nodemain->nodetype = navigation_node::NODETYPE_LEAF;
                if (isset($page['flat']) and $page['flat'] === 1) {
                    $nodemain->showinflatnavigation = 1;
                }
                // Capability check.
                if (isset($page['capability']) and
                        !empty($page['capability']) and !has_capability($page['capability'], $systemcontext)) {
                    $nodemain->remove();
                }
                // Child kontrolü.
                if (isset($page['childs']) and !empty($page['childs'])) {
                    self::generate_nodes($nodemain, $page['childs']);
                }

            }
        }

    }

    /**
     * @return string
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function generate_setting_menu() {
        global $OUTPUT;
        $systemcontext = context_system::instance();
        if (isset(self::$pages['settings']['childs']) and !empty(self::$pages['settings']['childs'])) {
            $actionmenu = new action_menu();
            $actionmenu->set_menu_trigger('Ayarlar');
            foreach (self::$pages['settings']['childs'] as $key => $page) {
                if (has_capability($page['capability'], $systemcontext)) {
                    $link = new moodle_url($page['url']);
                    $icon = (isset($page['icon']) and !empty($page['icon'])) ? new pix_icon($page['icon'], '', 'local_cms') : null;
                    $secondarylink = new action_menu_link_secondary($link, $icon, $page['text']);
                    $actionmenu->add_secondary_action($secondarylink);
                }

            }
            return $OUTPUT->render($actionmenu);
        } else {
            return null;
        }
    }

}
