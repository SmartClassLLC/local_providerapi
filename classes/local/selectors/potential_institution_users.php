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

namespace local_providerapi\local\selectors;

use local_providerapi\local\institution\institution;
use user_selector_base;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/user/selector/lib.php');

/**
 * Class potential_institution_users
 *
 * @package local_providerapi\local\selectors
 */
class potential_institution_users extends user_selector_base {

    /**
     * @var int
     */
    private $institutionid;

    /**
     * potential_institution_users constructor.
     *
     * @param $name
     * @param $options
     */
    public function __construct($name, $options) {
        $this->institutionid = $options['institutionid'];
        parent::__construct($name, $options);
        $this->maxusersperpage = 1000;
    }

    /**
     * Search the database for users matching the $search string, and any other
     * conditions that apply. The SQL for testing whether a user matches the
     * search string should be obtained by calling the search_sql method.
     *
     * This method is used both when getting the list of choices to display to
     * the user, and also when validating a list of users that was selected.
     *
     * When preparing a list of users to choose from ($this->is_validating()
     * return false) you should probably have an maximum number of users you will
     * return, and if more users than this match your search, you should instead
     * return a message generated by the too_many_results() method. However, you
     * should not do this when validating.
     *
     * If you are writing a new user_selector subclass, I strongly recommend you
     * look at some of the subclasses later in this file and in admin/roles/lib.php.
     * They should help you see exactly what you have to do.
     *
     * @param string $search the search string.
     * @return array An array of arrays of users. The array keys of the outer
     *      array should be the string names of optgroups. The keys of the inner
     *      arrays should be userids, and the values should be user objects
     *      containing at least the list of fields returned by the method
     *      required_fields_sql(). If a user object has a ->disabled property
     *      that is true, then that option will be displayed greyed out, and
     *      will not be returned by get_selected_users.
     */
    public function find_users($search) {
        global $DB;
        $this->extrafields = ['username'];
        $whereconditions = array();
        $institution = institution::get($this->institutionid);
        list($select, $from, $where, $params) = $institution->get_member_sql();
        list($wherecondition, $searchparams) = $this->search_sql($search, 'u');
        if ($wherecondition) {
            $whereconditions[] = $wherecondition;
            $params = array_merge($params, $searchparams);
        }
        $userids = $DB->get_fieldset_sql("SELECT u.id FROM {$from} WHERE {$where} ", $params);
        if ($userids) {
            list($usernotin, $usernotinparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'usernot', false);
            $whereconditions[] = "u.id $usernotin";
            $params = array_merge($params, $usernotinparams);
        }

        $fields = 'SELECT DISTINCT ' . $this->required_fields_sql('u') . ', u.username';
        $countfields = 'SELECT COUNT(DISTINCT u.id)';

        if ($whereconditions) {
            $wherecondition = ' WHERE ' . implode(' AND ', $whereconditions);
        }
        $sql = " FROM {user} u $wherecondition ";
        list($sort, $sortparams) = users_order_by_sql('u', $search, $this->accesscontext);
        $order = ' ORDER BY ' . $sort;

        if (!$this->is_validating()) {
            $potentialmemberscount =
                    $DB->count_records_sql($countfields . $sql, array_merge($params, $sortparams));
            if ($potentialmemberscount > $this->maxusersperpage) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }
        $availableusers =
                $DB->get_records_sql($fields . $sql . $order, array_merge($params, $sortparams));

        if (empty($availableusers)) {
            return array();
        }
        if ($search) {
            $groupname = get_string('potusersmatching', 'cohort', $search);
        } else {
            $groupname = get_string('potusers', 'cohort');
        }

        return array($groupname => $availableusers);
    }

    /**
     * @return array
     */
    protected function get_options() {
        $options = parent::get_options();
        $options['institutionid'] = $this->institutionid;
        $options['file'] = 'local/providerapi/classes/local/selectors/potential_institution_users.php';
        return $options;
    }
}