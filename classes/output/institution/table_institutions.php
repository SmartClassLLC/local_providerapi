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

namespace local_providerapi\output\institution;

use confirm_action;
use context_system;
use moodle_url;
use pix_icon;
use table_sql;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/tablelib.php');

/**
 * Class table_institutions
 *
 * @package local_providerapi\outpu\institution
 */
class table_institutions extends table_sql implements \renderable {

    /**
     * @var context_system
     */
    private $context;

    /**
     * table_institutions constructor.
     *
     * @param $baseurl
     * @param $pagesize
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function __construct($baseurl, $pagesize) {
        parent::__construct('table_institutions');
        $this->define_baseurl($baseurl);
        $this->collapsible(true);
        $this->sortable(true);
        $this->pageable(true);
        $this->is_downloadable(true);
        $this->is_downloading(optional_param('download', 0, PARAM_ALPHA),
                'institutions', 'page1');
        $this->show_download_buttons_at(array(TABLE_P_BOTTOM, TABLE_P_TOP));
        $context = context_system::instance();
        $this->context = $context;
        $this->pagesize = $pagesize;
        // Define the headers and columns.
        $headers = [];
        $columns = [];

        $headers[] = get_string('fullname');
        $columns[] = 'name';

        $headers[] = get_string('shortname');
        $columns[] = 'shortname';

        $headers[] = get_string('secretkey', 'local_providerapi');
        $columns[] = 'Secretkey';

        $headers[] = 'Creater';
        $columns[] = 'createrid';

        if (!$this->is_downloading()) {
            $headers[] = get_string('manage', 'local_providerapi');
            $columns[] = 'manage';

        }

        $this->define_columns($columns);
        $this->define_headers($headers);

        // Make this table sorted by first name by default.
        $this->no_sorting('createrid');
        $this->no_sorting('manage');

    }

    /**
     * @param object $row
     * @return string
     */
    public function col_name($row) {
        return format_string($row->name);
    }

    /**
     * @param $row
     * @return string
     */
    public function col_shortname($row) {
        return format_string($row->shortname);
    }

    /**
     * @param $row
     * @return string
     */
    public function col_secretkey($row) {
        return format_string($row->secretkey);
    }

    /**
     * @param $row
     * @return string
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function col_createrid($row) {
        $user = \core_user::get_user($row->createrid);
        $name = fullname($user);
        if ($this->is_downloading()) {
            return format_string($name);
        } else {
            $profileurl = new moodle_url('/user/profile.php', array('id' => $user->id));
            return \html_writer::link($profileurl, $name);
        }
    }

    /**
     * @param $row
     * @return string
     */
    public function col_manage($row) {
        global $OUTPUT;

        $buttons = array();
        if (has_capability('local/providerapi:viewinstitutionmembers', $this->context)) {
            $urlparams = array('id' => $row->id, 'returnurl' => $this->baseurl->out_as_local_url());
            $editurl = new moodle_url('/local/providerapi/modules/institution/members.php', $urlparams);
            $visibleimg = $OUTPUT->pix_icon('i/cohort', get_string('view'));
            $buttons[] = \html_writer::link($editurl, $visibleimg, array('title' => get_string('view')));
        }
        if (has_capability('local/providerapi:editinstitution', $this->context)) {
            $urlparams = array('id' => $row->id, 'returnurl' => $this->baseurl->out_as_local_url());
            $editurl = new moodle_url('/local/providerapi/modules/institution/editinstitution.php',
                    $urlparams + array('sesskey' => sesskey()));
            $visibleimg = $OUTPUT->pix_icon('t/edit', get_string('edit'));
            $buttons[] = \html_writer::link($editurl, $visibleimg, array('title' => get_string('edit')));
        }
        if (has_capability('local/providerapi:deleteinstitution', $this->context)) {
            $deleteurl = new moodle_url('/local/providerapi/modules/institution/editinstitution.php',
                    array('delid' => $row->id, 'returnurl' => $this->baseurl->out_as_local_url(), 'sesskey' => sesskey()));
            $visibleimg = new pix_icon('t/delete', get_string('delete'));
            $buttons[] = $OUTPUT->action_icon($deleteurl, $visibleimg,
                    new confirm_action(get_string('areyousuredel', 'local_providerapi', $row->name)));
        }
        return \html_writer::div(implode(' ', $buttons), 'text-nowrap');
    }

}