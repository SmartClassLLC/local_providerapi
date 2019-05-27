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
 * external class
 *
 * long_description
 *
 * @package    local_providerapi
 * @copyright  2019 çağlar MERSİNLİ <ceremy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_providerapi\webservice\institution;

use context_system;
use core_component;
use core_user;
use external_api;
use external_format_value;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use external_warnings;
use invalid_parameter_exception;
use local_providerapi\local\cohortHelper;
use local_providerapi\local\institution\institution;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . "/externallib.php");

/**
 * Class external
 *
 * @package local_providerapi\webservice\institution
 */
class external extends external_api {
    /**
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
     * @param [type] $institutionkey
     * @return bool
     */
    public static function checkinstitution($institutionkey) {
        $params = self::validate_parameters(self::checkinstitution_parameters(), array(
                'institutionkey' => $institutionkey
        ));

        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
        require_capability('local/providerapi:check_institution', $systemcontext);
        if ($institution = institution::get_by_secretkey($params['institutionkey'])) {
            return true;
        }
        return false;
    }

    /**
     * @return external_value
     */
    public static function checkinstitution_returns() {
        return new external_value(PARAM_BOOL, 'ok');
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.2
     */
    public static function create_users_parameters() {
        global $CFG;
        $userfields = [
                'institutionkey' => new external_value(PARAM_ALPHANUM, 'Institution SecretKey'),
                'studentno' => new external_value(PARAM_TEXT, 'Studentno must be 6 digits and must be unique each intitution'),
                'createpassword' => new external_value(PARAM_BOOL, 'True if password should be created and mailed to user.',
                        VALUE_OPTIONAL),
            // General.
                'username' => new external_value(core_user::get_property_type('username'),
                        'Username policy is defined in Moodle security config.'),
                'auth' => new external_value(core_user::get_property_type('auth'), 'Auth plugins include manual, ldap, etc',
                        VALUE_DEFAULT, 'manual', core_user::get_property_null('auth')),
                'password' => new external_value(core_user::get_property_type('password'),
                        'Plain text password consisting of any characters', VALUE_OPTIONAL),
                'firstname' => new external_value(core_user::get_property_type('firstname'), 'The first name(s) of the user'),
                'lastname' => new external_value(core_user::get_property_type('lastname'), 'The family name of the user'),
                'email' => new external_value(core_user::get_property_type('email'), 'A valid and unique email address'),
                'maildisplay' => new external_value(core_user::get_property_type('maildisplay'), 'Email display', VALUE_OPTIONAL),
                'city' => new external_value(core_user::get_property_type('city'), 'Home city of the user', VALUE_OPTIONAL),
                'country' => new external_value(core_user::get_property_type('country'),
                        'Home country code of the user, such as AU or CZ', VALUE_OPTIONAL),
                'timezone' => new external_value(core_user::get_property_type('timezone'),
                        'Timezone code such as Australia/Perth, or 99 for default', VALUE_OPTIONAL),
                'description' => new external_value(core_user::get_property_type('description'),
                        'User profile description, no HTML',
                        VALUE_OPTIONAL),
            // Additional names.
                'firstnamephonetic' => new external_value(core_user::get_property_type('firstnamephonetic'),
                        'The first name(s) phonetically of the user', VALUE_OPTIONAL),
                'lastnamephonetic' => new external_value(core_user::get_property_type('lastnamephonetic'),
                        'The family name phonetically of the user', VALUE_OPTIONAL),
                'middlename' => new external_value(core_user::get_property_type('middlename'), 'The middle name of the user',
                        VALUE_OPTIONAL),
                'alternatename' => new external_value(core_user::get_property_type('alternatename'),
                        'The alternate name of the user',
                        VALUE_OPTIONAL),
            // Interests.
                'interests' => new external_value(PARAM_TEXT, 'User interests (separated by commas)', VALUE_OPTIONAL),
                'department' => new external_value(core_user::get_property_type('department'), 'department', VALUE_OPTIONAL),
                'phone1' => new external_value(core_user::get_property_type('phone1'), 'Phone 1', VALUE_OPTIONAL),
                'phone2' => new external_value(core_user::get_property_type('phone2'), 'Phone 2', VALUE_OPTIONAL),
                'address' => new external_value(core_user::get_property_type('address'), 'Postal address', VALUE_OPTIONAL),
            // Other user preferences stored in the user table.
                'lang' => new external_value(core_user::get_property_type('lang'),
                        'Language code such as "en", must exist on server',
                        VALUE_DEFAULT, core_user::get_property_default('lang'), core_user::get_property_null('lang')),
                'calendartype' => new external_value(core_user::get_property_type('calendartype'),
                        'Calendar type such as "gregorian", must exist on server', VALUE_DEFAULT, $CFG->calendartype,
                        VALUE_OPTIONAL),
                'theme' => new external_value(core_user::get_property_type('theme'),
                        'Theme name such as "standard", must exist on server', VALUE_OPTIONAL),
                'mailformat' => new external_value(core_user::get_property_type('mailformat'),
                        'Mail format code is 0 for plain text, 1 for HTML etc', VALUE_OPTIONAL),
                'customfields' => new external_multiple_structure(
                        new external_single_structure(
                                [
                                        'type' => new external_value(PARAM_ALPHANUMEXT, 'The name of the custom field'),
                                        'value' => new external_value(PARAM_RAW, 'The value of the custom field')
                                ]
                        ), 'User custom fields (also known as user profil fields)', VALUE_OPTIONAL),
                'preferences' => new external_multiple_structure(
                        new external_single_structure(
                                [
                                        'type' => new external_value(PARAM_RAW, 'The name of the preference'),
                                        'value' => new external_value(PARAM_RAW, 'The value of the preference')
                                ]
                        ), 'User preferences', VALUE_OPTIONAL),
        ];
        return new external_function_parameters(
                [
                        'users' => new external_multiple_structure(
                                new external_single_structure($userfields)
                        )
                ]
        );
    }

    /**
     * Create one or more users.
     *
     * @throws |invalid_parameter_exception
     * @param array $users An array of users to create.
     * @return array An array of arrays
     * @since Moodle 2.2
     */
    public static function create_users($users) {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/lib/weblib.php");
        require_once($CFG->dirroot . "/user/lib.php");
        require_once($CFG->dirroot . "/user/editlib.php");
        require_once($CFG->dirroot . "/user/profile/lib.php"); // Required for customfields related function.

        // Ensure the current user is allowed to run this function.
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/providerapi:create_user', $context);

        // Do basic automatic PARAM checks on incoming data, using params description.
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::create_users_parameters(), array('users' => $users));

        $availableauths = core_component::get_plugin_list('auth');
        unset($availableauths['mnet']);       // These would need mnethostid too.
        unset($availableauths['webservice']); // We do not want new webservice users for now.

        $availablethemes = core_component::get_plugin_list('theme');
        $availablelangs = get_string_manager()->get_list_of_translations();

        $transaction = $DB->start_delegated_transaction();

        $userids = array();
        foreach ($params['users'] as $user) {
            // Istitution Check.
            $institution = institution::get_by_secretkey($user['institutionkey']);
            // Override istitutionname.
            $user['istitution'] = $institution->name;

            if (strlen($user['studentno']) != 6) {
                throw new invalid_parameter_exception('Invalid studentno type: ' . $user['studentno']);
            }
            // Override idnumber.
            $user['idnumber'] = $institution->shortname . (string) $user['studentno'];

            // Make sure that the username, firstname and lastname are not blank.
            foreach (array('username', 'firstname', 'lastname') as $fieldname) {
                if (trim($user[$fieldname]) === '') {
                    throw new invalid_parameter_exception('The field ' . $fieldname . ' cannot be blank');
                }
            }

            // Make sure that the idnumber doesn't already exist.
            if ($DB->record_exists('user', array('mnethostid' => $CFG->mnet_localhost_id,
                    'idnumber' => $user['idnumber']))) {
                throw new invalid_parameter_exception('Studentno already exists: ' . $user['idnumber']);
            }
            // Make sure that the username doesn't already exist.
            if ($DB->record_exists('user', array('username' => $user['username'], 'mnethostid' => $CFG->mnet_localhost_id))) {
                throw new invalid_parameter_exception('Username already exists: ' . $user['username']);
            }

            // Make sure auth is valid.
            if (empty($availableauths[$user['auth']])) {
                throw new invalid_parameter_exception('Invalid authentication type: ' . $user['auth']);
            }

            // Make sure lang is valid.
            if (empty($availablelangs[$user['lang']])) {
                throw new invalid_parameter_exception('Invalid language code: ' . $user['lang']);
            }

            // Make sure lang is valid.
            if (!empty($user['theme']) && empty($availablethemes[$user['theme']])) { // Theme is VALUE_OPTIONAL,
                // So no default value.
                // We need to test if the client sent it.
                throw new invalid_parameter_exception('Invalid theme: ' . $user['theme']);
            }

            // Make sure we have a password or have to create one.
            $authplugin = get_auth_plugin($user['auth']);
            if ($authplugin->is_internal() && empty($user['password']) && empty($user['createpassword'])) {
                throw new invalid_parameter_exception('Invalid password: you must provide a password, or set createpassword.');
            }

            $user['confirmed'] = true;
            $user['mnethostid'] = $CFG->mnet_localhost_id;

            // Start of user info validation.
            // Make sure we validate current user info as handled by current GUI. See user/editadvanced_form.php func validation().
            if (!validate_email($user['email'])) {
                throw new invalid_parameter_exception('Email address is invalid: ' . $user['email']);
            } else if (empty($CFG->allowaccountssameemail)) {
                // Make a case-insensitive query for the given email address.
                $select = $DB->sql_equal('email', ':email', false) . ' AND mnethostid = :mnethostid';
                $params = array(
                        'email' => $user['email'],
                        'mnethostid' => $user['mnethostid']
                );
                // If there are other user(s) that already have the same email, throw an error.
                if ($DB->record_exists_select('user', $select, $params)) {
                    throw new invalid_parameter_exception('Email address already exists: ' . $user['email']);
                }
            }
            // End of user info validation.

            $createpassword = !empty($user['createpassword']);
            unset($user['createpassword']);
            $updatepassword = false;
            if ($authplugin->is_internal()) {
                if ($createpassword) {
                    $user['password'] = '';
                } else {
                    $updatepassword = true;
                }
            } else {
                $user['password'] = AUTH_PASSWORD_NOT_CACHED;
            }

            // Create the user data now!
            $user['id'] = user_create_user($user, $updatepassword, false);

            // Add Institution Cohort.
            $institution->add_member($user['id']);

            $userobject = (object) $user;

            // Set user interests.
            if (!empty($user['interests'])) {
                $trimmedinterests = array_map('trim', explode(',', $user['interests']));
                $interests = array_filter($trimmedinterests, function($value) {
                    return !empty($value);
                });
                useredit_update_interests($userobject, $interests);
            }

            // Custom fields.
            if (!empty($user['customfields'])) {
                foreach ($user['customfields'] as $customfield) {
                    // Profile_save_data() saves profile file it's expecting a user with the correct id,
                    // and custom field to be named profile_field_"shortname".
                    $user["profile_field_" . $customfield['type']] = $customfield['value'];
                }
                profile_save_data((object) $user);
            }

            if ($createpassword) {
                setnew_password_and_mail($userobject);
                unset_user_preference('create_password', $userobject);
                set_user_preference('auth_forcepasswordchange', 1, $userobject);
            }

            // Trigger event.
            \core\event\user_created::create_from_userid($user['id'])->trigger();

            // Preferences.
            if (!empty($user['preferences'])) {
                $userpref = (object) $user;
                foreach ($user['preferences'] as $preference) {
                    $userpref->{'preference_' . $preference['type']} = $preference['value'];
                }
                useredit_update_user_preference($userpref);
            }

            $userids[] = array('id' => $user['id'], 'username' => $user['username']);
        }

        $transaction->allow_commit();

        return $userids;
    }

    /**
     * @return external_multiple_structure
     * @throws \coding_exception
     */
    public static function create_users_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                                'id' => new external_value(core_user::get_property_type('id'), 'user id'),
                                'username' => new external_value(core_user::get_property_type('username'), 'user name'),
                        )
                )
        );
    }

    /**
     * @return external_function_parameters
     * @throws \coding_exception
     */
    public static function update_users_parameters() {
        $userfields = [
                'id' => new external_value(core_user::get_property_type('id'), 'ID of the user'),
                'institutionkey' => new external_value(PARAM_ALPHANUM, 'Institution SecretKey'),
                'studentno' => new external_value(PARAM_TEXT, 'Studentno must be 6 digits and must be unique each intitution',
                        VALUE_OPTIONAL),
            // General.
                'username' => new external_value(core_user::get_property_type('username'),
                        'Username policy is defined in Moodle security config. for update', VALUE_OPTIONAL, '', NULL_NOT_ALLOWED),
                'auth' => new external_value(core_user::get_property_type('auth'),
                        'Auth plugins include manual, ldap,lti etc for update',
                        VALUE_OPTIONAL, '', NULL_NOT_ALLOWED),
                'suspended' => new external_value(core_user::get_property_type('suspended'),
                        'Suspend user account, either false to enable user login or true to disable it for update', VALUE_OPTIONAL),
                'password' => new external_value(core_user::get_property_type('password'),
                        'Plain text password consisting of any characters for update', VALUE_OPTIONAL, '', NULL_NOT_ALLOWED),
                'firstname' => new external_value(core_user::get_property_type('firstname'),
                        'The first name(s) of the user for update',
                        VALUE_OPTIONAL, '', NULL_NOT_ALLOWED),
                'lastname' => new external_value(core_user::get_property_type('lastname'), 'The family name of the user for update',
                        VALUE_OPTIONAL),
                'email' => new external_value(core_user::get_property_type('email'), 'A valid and unique email address for update',
                        VALUE_OPTIONAL,
                        '', NULL_NOT_ALLOWED),
                'maildisplay' => new external_value(core_user::get_property_type('maildisplay'), 'Email display for update',
                        VALUE_OPTIONAL),
                'city' => new external_value(core_user::get_property_type('city'), 'Home city of the user', VALUE_OPTIONAL),
                'country' => new external_value(core_user::get_property_type('country'),
                        'Home country code of the user, such as AU or CZ for update', VALUE_OPTIONAL),
                'timezone' => new external_value(core_user::get_property_type('timezone'),
                        'Timezone code such as Australia/Perth, or 99 for default for update', VALUE_OPTIONAL),
                'description' => new external_value(core_user::get_property_type('description'),
                        'User profile description, no HTML for update',
                        VALUE_OPTIONAL),
            // User picture.
                'userpicture' => new external_value(PARAM_INT,
                        'The itemid where the new user picture has been uploaded to, 0 to delete for update', VALUE_OPTIONAL),
            // Additional names.
                'firstnamephonetic' => new external_value(core_user::get_property_type('firstnamephonetic'),
                        'The first name(s) phonetically of the user for update', VALUE_OPTIONAL),
                'lastnamephonetic' => new external_value(core_user::get_property_type('lastnamephonetic'),
                        'The family name phonetically of the user for update', VALUE_OPTIONAL),
                'middlename' => new external_value(core_user::get_property_type('middlename'), 'The middle name of the user',
                        VALUE_OPTIONAL),
                'alternatename' => new external_value(core_user::get_property_type('alternatename'),
                        'The alternate name of the user',
                        VALUE_OPTIONAL),
            // Interests.
                'interests' => new external_value(PARAM_TEXT, 'User interests (separated by commas)', VALUE_OPTIONAL),
                'department' => new external_value(core_user::get_property_type('department'), 'department for update',
                        VALUE_OPTIONAL),
                'phone1' => new external_value(core_user::get_property_type('phone1'), 'Phone for update', VALUE_OPTIONAL),
                'phone2' => new external_value(core_user::get_property_type('phone2'), 'Mobile phone for update', VALUE_OPTIONAL),
                'address' => new external_value(core_user::get_property_type('address'), 'Postal address for update',
                        VALUE_OPTIONAL),
            // Other user preferences stored in the user table.
                'lang' => new external_value(core_user::get_property_type('lang'),
                        'Language code such as "en", must exist on server for update',
                        VALUE_OPTIONAL, '', NULL_NOT_ALLOWED),
                'calendartype' => new external_value(core_user::get_property_type('calendartype'),
                        'Calendar type such as "gregorian", must exist on server for update', VALUE_OPTIONAL, '', NULL_NOT_ALLOWED),
                'theme' => new external_value(core_user::get_property_type('theme'),
                        'Theme name such as "standard", must exist on server for update', VALUE_OPTIONAL),
                'mailformat' => new external_value(core_user::get_property_type('mailformat'),
                        'Mail format code is 0 for plain text, 1 for HTML etc for update', VALUE_OPTIONAL),
            // Custom user profile fields.
                'customfields' => new external_multiple_structure(
                        new external_single_structure(
                                [
                                        'type' => new external_value(PARAM_ALPHANUMEXT, 'The name of the custom field for update'),
                                        'value' => new external_value(PARAM_RAW, 'The value of the custom field for update')
                                ]
                        ), 'User custom fields (also known as user profil fields ) for update', VALUE_OPTIONAL),
                'preferences' => new external_multiple_structure(
                        new external_single_structure(
                                [
                                        'type' => new external_value(PARAM_RAW, 'The name of the preference for update'),
                                        'value' => new external_value(PARAM_RAW, 'The value of the preference for update')
                                ]
                        ), 'User preferences for update', VALUE_OPTIONAL),
        ];
        return new external_function_parameters(
                [
                        'users' => new external_multiple_structure(
                                new external_single_structure($userfields)
                        )
                ]
        );
    }

    /**
     * Update users
     *
     * @param array $users
     * @return null
     * @since Moodle 2.2
     */
    public static function update_users($users) {
        global $CFG, $DB, $USER;
        require_once($CFG->dirroot . "/user/lib.php");
        require_once($CFG->dirroot . "/user/profile/lib.php"); // Required for customfields related function.
        require_once($CFG->dirroot . '/user/editlib.php');

        // Ensure the current user is allowed to run this function.
        $context = context_system::instance();
        require_capability('local/providerapi:update_user', $context);
        self::validate_context($context);

        $params = self::validate_parameters(self::update_users_parameters(), array('users' => $users));

        $filemanageroptions = array('maxbytes' => $CFG->maxbytes,
                'subdirs' => 0,
                'maxfiles' => 1,
                'accepted_types' => 'web_image');

        $transaction = $DB->start_delegated_transaction();

        foreach ($params['users'] as $user) {

            // Istitution Check.
            $institution = institution::get_by_secretkey($user['institutionkey']);

            // Check user in institutuion.
            if (!cohortHelper::is_member($institution->cohortid, $user['id'])) {
                throw new moodle_exception('nofounduserininstitutuion', 'local_providerapi');
            }

            // Override istitutionname.
            $user['istitution'] = $institution->name;

            if (strlen($user['studentno']) != 6) {
                throw new invalid_parameter_exception('Invalid studentno type: ' . $user['studentno']);
            }
            // Override idnumber.
            if (!empty($user['studentno'])) {
                $user['idnumber'] = $institution->shortname . (string) $user['studentno'];
                // Make sure that the idnumber doesn't already exist.
                if ($DB->record_exists_select('user', 'mnethostid = :mnethostid AND idnumber = :idnumber AND id <> :id',
                        array('mnethostid' => $CFG->mnet_localhost_id,
                                'idnumber' => $user['idnumber'], 'id' => $user['id']))) {
                    throw new invalid_parameter_exception('Studentno already exists: ' . $user['idnumber']);
                }
            }

            // First check the user exists.
            if (!$existinguser = core_user::get_user($user['id'])) {
                continue;
            }
            // Check if we are trying to update an admin.
            if ($existinguser->id != $USER->id and is_siteadmin($existinguser) and !is_siteadmin($USER)) {
                continue;
            }
            // Other checks (deleted, remote or guest users).
            if ($existinguser->deleted or is_mnet_remote_user($existinguser) or isguestuser($existinguser->id)) {
                continue;
            }
            // Check duplicated emails.
            if (isset($user['email']) && $user['email'] !== $existinguser->email) {
                if (!validate_email($user['email'])) {
                    continue;
                } else if (empty($CFG->allowaccountssameemail)) {
                    // Make a case-insensitive query for the given email address and make sure to exclude the user being updated.
                    $select = $DB->sql_equal('email', ':email', false) . ' AND mnethostid = :mnethostid AND id <> :userid';
                    $params = array(
                            'email' => $user['email'],
                            'mnethostid' => $CFG->mnet_localhost_id,
                            'userid' => $user['id']
                    );
                    // Skip if there are other user(s) that already have the same email.
                    if ($DB->record_exists_select('user', $select, $params)) {
                        continue;
                    }
                }
            }

            user_update_user($user, true, false);

            $userobject = (object) $user;

            // Update user picture if it was specified for this user.
            if (empty($CFG->disableuserimages) && isset($user['userpicture'])) {
                $userobject->deletepicture = null;

                if ($user['userpicture'] == 0) {
                    $userobject->deletepicture = true;
                } else {
                    $userobject->imagefile = $user['userpicture'];
                }

                core_user::update_picture($userobject, $filemanageroptions);
            }

            // Update user interests.
            if (!empty($user['interests'])) {
                $trimmedinterests = array_map('trim', explode(',', $user['interests']));
                $interests = array_filter($trimmedinterests, function($value) {
                    return !empty($value);
                });
                useredit_update_interests($userobject, $interests);
            }

            // Update user custom fields.
            if (!empty($user['customfields'])) {

                foreach ($user['customfields'] as $customfield) {
                    // Profile_save_data() saves profile file it's expecting a user with the correct id,
                    // and custom field to be named profile_field_"shortname".
                    $user["profile_field_" . $customfield['type']] = $customfield['value'];
                }
                profile_save_data((object) $user);
            }
            // Add Institution Cohort.
            $institution->add_member($user['id']);

            // Trigger event.
            \core\event\user_updated::create_from_userid($user['id'])->trigger();

            // Preferences.
            if (!empty($user['preferences'])) {
                $userpref = clone($existinguser);
                foreach ($user['preferences'] as $preference) {
                    $userpref->{'preference_' . $preference['type']} = $preference['value'];
                }
                useredit_update_user_preference($userpref);
            }
            if (isset($user['suspended']) and $user['suspended']) {
                \core\session\manager::kill_user_sessions($user['id']);
            }
        }

        $transaction->allow_commit();

        return null;
    }

    /**
     * Returns description of method result value
     *
     * @return null
     * @since Moodle 2.2
     */
    public static function update_users_returns() {
        return null;
    }

    /**
     * @return external_function_parameters
     * @throws \coding_exception
     */
    public static function delete_users_parameters() {
        $userfields = [
                'id' => new external_value(core_user::get_property_type('id'), 'ID of the user'),
                'institutionkey' => new external_value(PARAM_ALPHANUM, 'Institution SecretKey')
        ];
        return new external_function_parameters(
                [
                        'users' => new external_multiple_structure(
                                new external_single_structure($userfields)
                        )
                ]
        );
    }

    /**
     * Delete users
     *
     * @param array $userids
     * @return null
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \dml_transaction_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @since Moodle 2.2
     */
    public static function delete_users($userids) {
        global $CFG, $DB, $USER;
        require_once($CFG->dirroot . "/user/lib.php");

        // Ensure the current user is allowed to run this function.
        $context = context_system::instance();
        require_capability('local/providerapi:delete_user', $context);
        self::validate_context($context);

        $params = self::validate_parameters(self::delete_users_parameters(), array('users' => $userids));

        $transaction = $DB->start_delegated_transaction();

        foreach ($params['users'] as $user) {
            // Istitution Check.
            $institution = institution::get_by_secretkey($user['institutionkey']);
            // Check user in institutuion.
            if (!cohortHelper::is_member($institution->cohortid, $user['id'])) {
                throw new moodle_exception('nofounduserininstitutuion', 'local_providerapi');
            }
            $userrecord = $DB->get_record('user', array('id' => $user['id'], 'deleted' => 0), '*', MUST_EXIST);
            // Must not allow deleting of admins or self!!!
            if (is_siteadmin($userrecord)) {
                throw new moodle_exception('useradminodelete', 'error');
            }
            if ($USER->id == $userrecord->id) {
                throw new moodle_exception('usernotdeletederror', 'error');
            }
            user_delete_user($userrecord);
        }

        $transaction->allow_commit();

        return null;
    }

    /**
     * Returns description of method result value
     *
     * @return null
     * @since Moodle 2.2
     */
    public static function delete_users_returns() {
        return null;
    }

    /**
     * @return external_function_parameters
     */
    public static function get_users_parameters() {
        return new external_function_parameters(
                array(
                        'institutionkey' => new external_value(PARAM_ALPHANUM, 'Institution SecretKey'),
                        'criteria' => new external_multiple_structure(
                                new external_single_structure(
                                        array(
                                                'key' => new external_value(PARAM_ALPHA, 'the user column to search,
                                                 expected keys (value format) are:
                                "institutionkey" (string) matching institutionkey,
                                "id" (int) matching user id,
                                "lastname" (string) user last name
                                (Note: you can use % for searching but it may be considerably slower!),
                                "firstname" (string) user first name
                                (Note: you can use % for searching but it may be considerably slower!),
                                "studentno" (string) matching user studentno,
                                "username" (string) matching user username,
                                "email" (string) user email (Note: you can use % for searching but it may be considerably slower!),
                                "auth" (string) matching user auth plugin'),
                                                'value' => new external_value(PARAM_RAW, 'the value to search')
                                        )
                                ), 'the key/value pairs to be considered in user search. Values can not be empty.
                        Specify different keys only once (fullname => \'user1\', auth => \'manual\', ...) -
                        key occurences are forbidden.
                        The search is executed with AND operator on the criterias. Invalid criterias (keys) are ignored,
                        the search is still executed on the valid criterias.
                        You can search without criteria, but the function is not designed for it.
                        It could very slow or timeout. The function is designed to search some specific users.'
                        )

                )
        );
    }

    /**
     * @param $institutionkey
     * @param array $criteria
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    public static function get_users($institutionkey, $criteria = array()) {
        global $CFG, $DB;

        require_once($CFG->dirroot . "/user/lib.php");

        // Ensure the current user is allowed to run this function.
        $context = context_system::instance();
        require_capability('local/providerapi:get_users', $context);
        self::validate_context($context);

        $params = self::validate_parameters(self::get_users_parameters(),
                array('institutionkey' => $institutionkey, 'criteria' => $criteria));
        // Get institution.
        $institution = institution::get_by_secretkey($params['institutionkey']);

        // Validate the criteria and retrieve the users.
        $users = array();
        $warnings = array();
        $sqlparams = array();
        $usedkeys = array();

        // Do not retrieve deleted users.
        $where = ' u.deleted = 0 AND cm.cohortid = :cohortid';
        $sqlparams['cohortid'] = $institution->cohortid;

        foreach ($params['criteria'] as $criteriaindex => $criteria) {

            if ($criteria['key'] === 'studentno') {
                $criteria['key'] = 'idnumber';
                $criteria['value'] = $institution->shortname . $criteria['value'];
            }

            // Check that the criteria has never been used.
            if (array_key_exists($criteria['key'], $usedkeys)) {
                throw new moodle_exception('keyalreadyset', '', '', null,
                        'The key ' . $criteria['key'] . ' can only be sent once');
            } else {
                $usedkeys[$criteria['key']] = true;
            }

            $invalidcriteria = false;
            // Clean the parameters.
            $paramtype = PARAM_RAW;
            switch ($criteria['key']) {
                case 'id':
                    $paramtype = core_user::get_property_type('id');
                    break;
                case 'idnumber':
                    $paramtype = core_user::get_property_type('idnumber');
                    break;
                case 'username':
                    $paramtype = core_user::get_property_type('username');
                    break;
                case 'email':
                    // We use PARAM_RAW to allow searches with %.
                    $paramtype = core_user::get_property_type('email');
                    break;
                case 'auth':
                    $paramtype = core_user::get_property_type('auth');
                    break;
                case 'lastname':
                case 'firstname':
                    $paramtype = core_user::get_property_type('firstname');
                    break;
                default:
                    // Send back a warning that this search key is not supported in this version.
                    // This warning will make the function extandable without breaking clients.
                    $warnings[] = array(
                            'item' => $criteria['key'],
                            'warningcode' => 'invalidfieldparameter',
                            'message' =>
                                    'The search key \'' . $criteria['key'] .
                                    '\' is not supported, look at the web service documentation'
                    );
                    // Do not add this invalid criteria to the created SQL request.
                    $invalidcriteria = true;
                    unset($params['criteria'][$criteriaindex]);
                    break;
            }

            if (!$invalidcriteria) {
                $cleanedvalue = clean_param($criteria['value'], $paramtype);

                $where .= ' AND ';

                // Create the SQL.
                switch ($criteria['key']) {
                    case 'id':
                    case 'idnumber':
                    case 'username':
                    case 'auth':
                        $where .= 'u.' . $criteria['key'] . ' = :' . $criteria['key'];
                        $sqlparams[$criteria['key']] = $cleanedvalue;
                        break;
                    case 'email':
                    case 'lastname':
                    case 'firstname':
                        $where .= $DB->sql_like('u.' . $criteria['key'], ':' . $criteria['key'], false);
                        $sqlparams[$criteria['key']] = $cleanedvalue;
                        break;
                    default:
                        break;
                }
            }
        }

        $sql = "SELECT u.*
                FROM {user} u
                JOIN {cohort_members} cm ON cm.userid = u.id
                WHERE
                $where
                ORDER BY u.id ASC
                ";

        $users = $DB->get_records_sql($sql, $sqlparams);

        // Finally retrieve each users information.
        $returnedusers = array();
        foreach ($users as $user) {
            $userdetails = user_get_user_details_courses($user);

            // Return the user only if all the searched fields are returned.
            // Otherwise it means that the $USER was not allowed to search the returned user.
            if (!empty($userdetails)) {
                $validuser = true;
                if ($validuser) {
                    $returnedusers[] = $userdetails;
                }
            }
        }

        return array('users' => $returnedusers, 'warnings' => $warnings);
    }

    /**
     * @return external_single_structure
     */
    public static function get_users_returns() {
        return new external_single_structure(
                array('users' => new external_multiple_structure(
                        self::user_description()
                ),
                        'warnings' => new external_warnings('always set to \'key\'', 'faulty key name')
                )
        );
    }

    /**
     * @param array $additionalfields
     * @return external_single_structure
     * @throws \coding_exception
     */
    public static function user_description($additionalfields = array()) {
        $userfields = array(
                'id' => new external_value(core_user::get_property_type('id'), 'ID of the user'),
                'username' => new external_value(core_user::get_property_type('username'), 'The username', VALUE_OPTIONAL),
                'firstname' => new external_value(core_user::get_property_type('firstname'), 'The first name(s) of the user',
                        VALUE_OPTIONAL),
                'lastname' => new external_value(core_user::get_property_type('lastname'), 'The family name of the user',
                        VALUE_OPTIONAL),
                'fullname' => new external_value(core_user::get_property_type('firstname'), 'The fullname of the user'),
                'email' => new external_value(core_user::get_property_type('email'),
                        'An email address - allow email as root@localhost', VALUE_OPTIONAL),
                'address' => new external_value(core_user::get_property_type('address'), 'Postal address', VALUE_OPTIONAL),
                'phone1' => new external_value(core_user::get_property_type('phone1'), 'Phone 1', VALUE_OPTIONAL),
                'phone2' => new external_value(core_user::get_property_type('phone2'), 'Phone 2', VALUE_OPTIONAL),
                'icq' => new external_value(core_user::get_property_type('icq'), 'icq number', VALUE_OPTIONAL),
                'skype' => new external_value(core_user::get_property_type('skype'), 'skype id', VALUE_OPTIONAL),
                'yahoo' => new external_value(core_user::get_property_type('yahoo'), 'yahoo id', VALUE_OPTIONAL),
                'aim' => new external_value(core_user::get_property_type('aim'), 'aim id', VALUE_OPTIONAL),
                'msn' => new external_value(core_user::get_property_type('msn'), 'msn number', VALUE_OPTIONAL),
                'department' => new external_value(core_user::get_property_type('department'), 'department', VALUE_OPTIONAL),
                'institution' => new external_value(core_user::get_property_type('institution'), 'institution', VALUE_OPTIONAL),
                'idnumber' => new external_value(core_user::get_property_type('idnumber'),
                        'An arbitrary ID code number perhaps from the institution', VALUE_OPTIONAL),
                'interests' => new external_value(PARAM_TEXT, 'user interests (separated by commas)', VALUE_OPTIONAL),
                'firstaccess' => new external_value(core_user::get_property_type('firstaccess'),
                        'first access to the site (0 if never)', VALUE_OPTIONAL),
                'lastaccess' => new external_value(core_user::get_property_type('lastaccess'),
                        'last access to the site (0 if never)', VALUE_OPTIONAL),
                'auth' => new external_value(core_user::get_property_type('auth'), 'Auth plugins include manual, ldap, etc',
                        VALUE_OPTIONAL),
                'suspended' => new external_value(core_user::get_property_type('suspended'),
                        'Suspend user account, either false to enable user login or true to disable it', VALUE_OPTIONAL),
                'confirmed' => new external_value(core_user::get_property_type('confirmed'),
                        'Active user: 1 if confirmed, 0 otherwise', VALUE_OPTIONAL),
                'lang' => new external_value(core_user::get_property_type('lang'),
                        'Language code such as "en", must exist on server', VALUE_OPTIONAL),
                'calendartype' => new external_value(core_user::get_property_type('calendartype'),
                        'Calendar type such as "gregorian", must exist on server', VALUE_OPTIONAL),
                'theme' => new external_value(core_user::get_property_type('theme'),
                        'Theme name such as "standard", must exist on server', VALUE_OPTIONAL),
                'timezone' => new external_value(core_user::get_property_type('timezone'),
                        'Timezone code such as Australia/Perth, or 99 for default', VALUE_OPTIONAL),
                'mailformat' => new external_value(core_user::get_property_type('mailformat'),
                        'Mail format code is 0 for plain text, 1 for HTML etc', VALUE_OPTIONAL),
                'description' => new external_value(core_user::get_property_type('description'), 'User profile description',
                        VALUE_OPTIONAL),
                'descriptionformat' => new external_format_value(core_user::get_property_type('descriptionformat'), VALUE_OPTIONAL),
                'city' => new external_value(core_user::get_property_type('city'), 'Home city of the user', VALUE_OPTIONAL),
                'url' => new external_value(core_user::get_property_type('url'), 'URL of the user', VALUE_OPTIONAL),
                'country' => new external_value(core_user::get_property_type('country'),
                        'Home country code of the user, such as AU or CZ', VALUE_OPTIONAL),
                'profileimageurlsmall' => new external_value(PARAM_URL, 'User image profile URL - small version'),
                'profileimageurl' => new external_value(PARAM_URL, 'User image profile URL - big version'),
                'customfields' => new external_multiple_structure(
                        new external_single_structure(
                                array(
                                        'type' => new external_value(PARAM_ALPHANUMEXT,
                                                'The type of the custom field - text field, checkbox...'),
                                        'value' => new external_value(PARAM_RAW, 'The value of the custom field'),
                                        'name' => new external_value(PARAM_RAW, 'The name of the custom field'),
                                        'shortname' => new external_value(PARAM_RAW,
                                                'The shortname of the custom field -
                                                to be able to build the field class in the code'),
                                )
                        ), 'User custom fields (also known as user profile fields)', VALUE_OPTIONAL),
                'preferences' => new external_multiple_structure(
                        new external_single_structure(
                                array(
                                        'name' => new external_value(PARAM_RAW, 'The name of the preferences'),
                                        'value' => new external_value(PARAM_RAW, 'The value of the preference'),
                                )
                        ), 'Users preferences', VALUE_OPTIONAL)
        );
        if (!empty($additionalfields)) {
            $userfields = array_merge($userfields, $additionalfields);
        }
        return new external_single_structure($userfields);
    }

}
