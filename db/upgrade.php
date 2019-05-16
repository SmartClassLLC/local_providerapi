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

    return true;
}
