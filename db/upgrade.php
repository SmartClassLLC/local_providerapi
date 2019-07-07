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
 * Plugin upgrade steps are defined here.
 *
 * @package     local_providerapi
 * @category    upgrade
 * @copyright   2019 Çağlar MERSİNLİ <ceremy@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute local_providerapi upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_providerapi_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2019050206) {

        // Define table local_providerapi_companies to be created.
        $table = new xmldb_table('local_providerapi_companies');

        // Adding fields to table local_providerapi_companies.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('fullname', XMLDB_TYPE_CHAR, '254', null, XMLDB_NOTNULL, null, null);
        $table->add_field('shortname', XMLDB_TYPE_CHAR, '3', null, XMLDB_NOTNULL, null, null);
        $table->add_field('secretkey', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('descriptionformat', XMLDB_TYPE_INTEGER, '4', null, null, null, null);
        $table->add_field('createrid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('modifiedby', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table local_providerapi_companies.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_providerapi_companies.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Providerapi savepoint reached.
        upgrade_plugin_savepoint(true, 2019050206, 'local', 'providerapi');
    }
    if ($oldversion < 2019050207) {

        // Rename field fullname on table local_providerapi_companies to NEWNAMEGOESHERE.
        $table = new xmldb_table('local_providerapi_companies');
        $field = new xmldb_field('fullname', XMLDB_TYPE_CHAR, '254', null, XMLDB_NOTNULL, null, null, 'id');

        // Launch rename field fullname.
        $dbman->rename_field($table, $field, 'name');

        // Providerapi savepoint reached.
        upgrade_plugin_savepoint(true, 2019050207, 'local', 'providerapi');
    }
    if ($oldversion < 2019050208) {

        // Define field cohortid to be added to local_providerapi_companies.
        $table = new xmldb_table('local_providerapi_companies');
        $field = new xmldb_field('cohortid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'secretkey');

        // Conditionally launch add field cohortid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $key = new xmldb_key('cohortid', XMLDB_KEY_FOREIGN, ['cohortid'], 'cohort', ['id']);

        // Launch add key cohortid.
        $dbman->add_key($table, $key);
        // Providerapi savepoint reached.
        upgrade_plugin_savepoint(true, 2019050208, 'local', 'providerapi');
    }
    if ($oldversion < 2019050210) {

        // Define key secretkey (unique) to be added to local_providerapi_companies.
        $table = new xmldb_table('local_providerapi_companies');
        $key = new xmldb_key('secretkey', XMLDB_KEY_UNIQUE, ['secretkey']);

        // Launch add key secretkey.
        $dbman->add_key($table, $key);

        // Providerapi savepoint reached.
        upgrade_plugin_savepoint(true, 2019050210, 'local', 'providerapi');
    }

    if ($oldversion < 2019050211) {

        // Define table local_providerapi_courses to be created.
        $table = new xmldb_table('local_providerapi_courses');

        // Adding fields to table local_providerapi_courses.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('institutionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('createrid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table local_providerapi_courses.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('institutionid', XMLDB_KEY_FOREIGN, ['institutionid'], 'local_providerapi_companies', ['id']);
        $table->add_key('courseid', XMLDB_KEY_FOREIGN, ['courseid'], 'course', ['id']);
        $table->add_key('createrid', XMLDB_KEY_FOREIGN, ['createrid'], 'user', ['id']);

        // Conditionally launch create table for local_providerapi_courses.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Providerapi savepoint reached.
        upgrade_plugin_savepoint(true, 2019050211, 'local', 'providerapi');
    }
    if ($oldversion < 2019050214) {

        // Define table local_providerapi_batches to be created.
        $table = new xmldb_table('local_providerapi_batches');

        // Adding fields to table local_providerapi_batches.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('institutionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('cohortid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '254', null, XMLDB_NOTNULL, null, null);
        $table->add_field('capacity', XMLDB_TYPE_INTEGER, '3', null, null, null, null);
        $table->add_field('createrid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('modifiedby', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table local_providerapi_batches.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('institutionid', XMLDB_KEY_FOREIGN, ['institutionid'], 'local_providerapi_companies', ['id']);
        $table->add_key('cohortid', XMLDB_KEY_FOREIGN, ['cohortid'], 'cohort', ['id']);

        // Conditionally launch create table for local_providerapi_batches.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Providerapi savepoint reached.
        upgrade_plugin_savepoint(true, 2019050214, 'local', 'providerapi');
    }
    if ($oldversion < 2019050217) {

        // Define field source to be added to local_providerapi_batches.
        $table = new xmldb_table('local_providerapi_batches');
        $field = new xmldb_field('source', XMLDB_TYPE_CHAR, '3', null, null, null, 'web', 'capacity');

        // Conditionally launch add field source.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Providerapi savepoint reached.
        upgrade_plugin_savepoint(true, 2019050217, 'local', 'providerapi');
    }
    if ($oldversion < 2019050219) {

        // Define table local_providerapi_btcourses to be created.
        $table = new xmldb_table('local_providerapi_btcourses');

        // Adding fields to table local_providerapi_btcourses.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('batchid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sharedcourseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('source', XMLDB_TYPE_CHAR, '3', null, null, null, 'web');
        $table->add_field('createrid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('modifiedby', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table local_providerapi_btcourses.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('batchid', XMLDB_KEY_FOREIGN, ['batchid'], 'local_providerapi_batches', ['id']);
        $table->add_key('sharedcourseid', XMLDB_KEY_FOREIGN, ['sharedcourseid'], 'local_providerapi_courses', ['id']);

        // Conditionally launch create table for local_providerapi_btcourses.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Providerapi savepoint reached.
        upgrade_plugin_savepoint(true, 2019050219, 'local', 'providerapi');
    }
    if ($oldversion < 2019050224) {

        // Define field groupid to be added to local_providerapi_btcourses.
        $table = new xmldb_table('local_providerapi_btcourses');
        $field = new xmldb_field('groupid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'timemodified');

        // Conditionally launch add field groupid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $key = new xmldb_key('groupid', XMLDB_KEY_FOREIGN, ['groupid'], 'groups', ['id']);

        // Launch add key groupid.
        $dbman->add_key($table, $key);
        // Providerapi savepoint reached.
        upgrade_plugin_savepoint(true, 2019050224, 'local', 'providerapi');
    }
    if ($oldversion < 2019050226) {

        // Define field enrolinstanceid to be added to local_providerapi_btcourses.
        $table = new xmldb_table('local_providerapi_btcourses');
        $field = new xmldb_field('enrolinstanceid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'groupid');

        // Conditionally launch add field enrolinstanceid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $key = new xmldb_key('enrolinstanceid', XMLDB_KEY_FOREIGN, ['enrolinstanceid'], 'enrol', ['id']);

        // Launch add key enrolinstanceid.
        $dbman->add_key($table, $key);

        // Providerapi savepoint reached.
        upgrade_plugin_savepoint(true, 2019050226, 'local', 'providerapi');
    }
    if ($oldversion < 2019050229) {

        // Define key groupid (foreign-unique) to be dropped form local_providerapi_btcourses.
        $table = new xmldb_table('local_providerapi_btcourses');
        $key = new xmldb_key('groupid', XMLDB_KEY_FOREIGN, ['groupid'], 'groups', ['id']);

        // Launch drop key groupid.
        $dbman->drop_key($table, $key);
        $key = new xmldb_key('groupid', XMLDB_KEY_FOREIGN_UNIQUE, ['groupid'], 'groups', ['id']);

        // Launch add key groupid.
        $dbman->add_key($table, $key);
        $key = new xmldb_key('enrolinstanceid', XMLDB_KEY_FOREIGN, ['enrolinstanceid'], 'enrol', ['id']);

        // Launch drop key enrolinstanceid.
        $dbman->drop_key($table, $key);
        $key = new xmldb_key('enrolinstanceid', XMLDB_KEY_FOREIGN_UNIQUE, ['enrolinstanceid'], 'enrol', ['id']);

        // Launch add key enrolinstanceid.
        $dbman->add_key($table, $key);

        // Providerapi savepoint reached.
        upgrade_plugin_savepoint(true, 2019050229, 'local', 'providerapi');
    }
    if ($oldversion < 2019070500) {

        // Define table local_api_tools to be created.
        $table = new xmldb_table('local_api_tools');

        // Adding fields to table local_api_tools.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('contextid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('institution', XMLDB_TYPE_CHAR, '40', null, null, null, null);
        $table->add_field('lang', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, 'tr');
        $table->add_field('timezone', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, '99');
        $table->add_field('maildisplay', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '2');
        $table->add_field('city', XMLDB_TYPE_CHAR, '120', null, null, null, null);
        $table->add_field('country', XMLDB_TYPE_CHAR, '2', null, null, null, null);
        $table->add_field('gradesync', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('gradesynccompletion', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('membersync', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('membersyncmode', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('secret', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_api_tools.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('contextid', XMLDB_KEY_FOREIGN, ['contextid'], 'context', ['id']);

        // Conditionally launch create table for local_api_tools.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        // Define table local_api_users to be created.
        $table = new xmldb_table('local_api_users');

        // Adding fields to table local_api_users.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('toolid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('serviceurl', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('sourceid', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('consumerkey', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('consumersecret', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('membershipsurl', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('membershipsid', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('lastgrade', XMLDB_TYPE_NUMBER, '10, 5', null, null, null, null);
        $table->add_field('lastaccess', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table local_api_users.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_key('toolid', XMLDB_KEY_FOREIGN, ['toolid'], 'local_api_tools', ['id']);

        // Conditionally launch create table for local_api_users.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        // Define table local_api_lti2_consumer to be created.
        $table = new xmldb_table('local_api_lti2_consumer');

        // Adding fields to table local_api_lti2_consumer.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('consumerkey256', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('consumerkey', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('secret', XMLDB_TYPE_CHAR, '1024', null, XMLDB_NOTNULL, null, null);
        $table->add_field('ltiversion', XMLDB_TYPE_CHAR, '10', null, null, null, null);
        $table->add_field('consumername', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('consumerversion', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('consumerguid', XMLDB_TYPE_CHAR, '1024', null, null, null, null);
        $table->add_field('profile', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('toolproxy', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('settings', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('protected', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('enablefrom', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('enableuntil', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('lastaccess', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('created', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('updated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_api_lti2_consumer.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Adding indexes to table local_api_lti2_consumer.
        $table->add_index('consumerkey256_uniq', XMLDB_INDEX_UNIQUE, ['consumerkey256']);

        // Conditionally launch create table for local_api_lti2_consumer.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        // Define table local_api_lti2_tool_proxy to be created.
        $table = new xmldb_table('local_api_lti2_tool_proxy');

        // Adding fields to table local_api_lti2_tool_proxy.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('toolproxykey', XMLDB_TYPE_CHAR, '32', null, XMLDB_NOTNULL, null, null);
        $table->add_field('consumerid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('toolproxy', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('created', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('updated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_api_lti2_tool_proxy.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('toolproxykey_uniq', XMLDB_KEY_UNIQUE, ['toolproxykey']);
        $table->add_key('consumerid', XMLDB_KEY_FOREIGN, ['consumerid'], 'local_api_lti2_consumer', ['id']);

        // Conditionally launch create table for local_api_lti2_tool_proxy.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        // Define table local_api_lti2_context to be created.
        $table = new xmldb_table('local_api_lti2_context');

        // Adding fields to table local_api_lti2_context.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('consumerid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('lticontextkey', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('type', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('settings', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('created', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('updated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_api_lti2_context.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('consumerid', XMLDB_KEY_FOREIGN, ['consumerid'], 'local_api_lti2_consumer', ['id']);

        // Conditionally launch create table for local_api_lti2_context.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        // Define table local_api_lti2_nonce to be created.
        $table = new xmldb_table('local_api_lti2_nonce');

        // Adding fields to table local_api_lti2_nonce.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('consumerid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('value', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL, null, null);
        $table->add_field('expires', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_api_lti2_nonce.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('consumerid', XMLDB_KEY_FOREIGN, ['consumerid'], 'local_api_lti2_consumer', ['id']);

        // Conditionally launch create table for local_api_lti2_nonce.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_api_lti2_resource_link to be created.
        $table = new xmldb_table('local_api_lti2_resource_link');

        // Adding fields to table local_api_lti2_resource_link.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('contextid', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('consumerid', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('ltiresourcelinkkey', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('settings', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('primaryresourcelinkid', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('shareapproved', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('created', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('updated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_api_lti2_resource_link.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('contextid', XMLDB_KEY_FOREIGN, ['contextid'], 'local_api_lti2_context', ['id']);
        $table->add_key('primaryresourcelinkid', XMLDB_KEY_FOREIGN, ['primaryresourcelinkid'], 'local_api_lti2_resource_link',
                ['id']);
        $table->add_key('consumerid', XMLDB_KEY_FOREIGN, ['consumerid'], 'local_api_lti2_consumer', ['id']);

        // Conditionally launch create table for local_api_lti2_resource_link.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        // Define table local_api_lti2_share_key to be created.
        $table = new xmldb_table('local_api_lti2_share_key');

        // Adding fields to table local_api_lti2_share_key.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('sharekey', XMLDB_TYPE_CHAR, '32', null, XMLDB_NOTNULL, null, null);
        $table->add_field('resourcelinkid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('autoapprove', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('expires', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_api_lti2_share_key.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('sharekey', XMLDB_KEY_UNIQUE, ['sharekey']);
        $table->add_key('resourcelinkid', XMLDB_KEY_FOREIGN_UNIQUE, ['resourcelinkid'], 'local_api_lti2_resource_link', ['id']);

        // Conditionally launch create table for local_api_lti2_share_key.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        // Define table local_api_lti2_user_result to be created.
        $table = new xmldb_table('local_api_lti2_user_result');

        // Adding fields to table local_api_lti2_user_result.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('resourcelinkid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('ltiuserkey', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('ltiresultsourcedid', XMLDB_TYPE_CHAR, '1024', null, XMLDB_NOTNULL, null, null);
        $table->add_field('created', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('updated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_api_lti2_user_result.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('resourcelinkid', XMLDB_KEY_FOREIGN, ['resourcelinkid'], 'local_api_lti2_resource_link', ['id']);

        // Conditionally launch create table for local_api_lti2_user_result.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        // Define table local_api_tool_consumer_map to be created.
        $table = new xmldb_table('local_api_tool_consumer_map');

        // Adding fields to table local_api_tool_consumer_map.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('toolid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('consumerid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_api_tool_consumer_map.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('toolid', XMLDB_KEY_FOREIGN, ['toolid'], 'local_api_tools', ['id']);
        $table->add_key('consumerid', XMLDB_KEY_FOREIGN, ['consumerid'], 'local_api_lti2_consumer', ['id']);

        // Conditionally launch create table for local_api_tool_consumer_map.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Providerapi savepoint reached.
        upgrade_plugin_savepoint(true, 2019070500, 'local', 'providerapi');
    }
    if ($oldversion < 2019070501) {

        // Define field courseid to be added to local_api_tools.
        $table = new xmldb_table('local_api_tools');
        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'id');

        // Conditionally launch add field courseid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $key = new xmldb_key('courseid', XMLDB_KEY_FOREIGN, ['courseid'], 'course', ['id']);

        // Launch add key courseid.
        $dbman->add_key($table, $key);

        // Providerapi savepoint reached.
        upgrade_plugin_savepoint(true, 2019070501, 'local', 'providerapi');
    }
    if ($oldversion < 2019070502) {

        // Define key courseid (foreign) to be dropped form local_api_tools.
        $table = new xmldb_table('local_api_tools');
        $key = new xmldb_key('courseid', XMLDB_KEY_FOREIGN, ['courseid'], 'course', ['id']);

        // Launch drop key courseid.
        $dbman->drop_key($table, $key);
        $key = new xmldb_key('courseid', XMLDB_KEY_FOREIGN_UNIQUE, ['courseid'], 'course', ['id']);

        // Launch add key courseid.
        $dbman->add_key($table, $key);
        // Providerapi savepoint reached.
        upgrade_plugin_savepoint(true, 2019070502, 'local', 'providerapi');
    }
    if ($oldversion < 2019070504) {
        global $DB;
        $sharedcourses = $DB->get_fieldset_select(\local_providerapi\local\course\course::$dbname, 'courseid', null);
        if ($sharedcourses) {
            foreach ($sharedcourses as $courseid) {
                \local_providerapi\local\helper::create_tool($courseid);
            }
        }
        upgrade_plugin_savepoint(true, 2019070504, 'local', 'providerapi');
    }
    return true;
}
