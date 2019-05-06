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


/*
* short_description
*
* long_description
*
* @package    local_providerapi
* @copyright  2019 çağlar MERSİNLİ <ceremy@gmail.com>
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
namespace local_providerapi\webservice\institution;

use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . "/externallib.php");

class external extends external_api
{
    /**
     *
     *
     * @return external_function_parameters
     */
    public static function checkinstitution_parameters() {
        return new external_function_parameters(
            array(
                'institutionkey' => new external_value(PARAM_TEXT, 'Institution Key')
            )
        );
    }

    /**
     *
     *
     * @param [type] $institutionkey
     * @return bool
     */
    public static function checkinstitution($institutionkey) {
        $params = self::validate_parameters(self::checkinstitution_parameters(), array(
            'institutionkey' => $institutionkey
        ));

        $systemcontext = get_system_context();
        self::validate_context($systemcontext);
        require_capability('local/providerapi:check_institution', $systemcontext);

        return true;
    }

    public static function checkinstitution_returns() {
        return new external_value(PARAM_BOOL, 'ok');
    }


}
