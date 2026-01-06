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
 * Local plugin "staticpage" - Upgrade plugin tasks
 *
 * @package    local_staticpage
 * @copyright  2013 Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\html_writer;

/**
 * Upgrade steps for this plugin
 * @param int $oldversion the version we are upgrading from
 * @return boolean
 */
function xmldb_local_staticpage_upgrade($oldversion) {

    // Fetch documents from documents directory and put them into the new documents filearea.
    if ($oldversion < 2016020309) {
        // Prepare filearea.
        $context = \context_system::instance();
        $fs = get_file_storage();
        $filerecord = ['component' => 'local_staticpage', 'filearea' => 'documents',
                            'contextid' => $context->id, 'itemid' => 0, 'filepath' => '/',
                            'filename' => '', ];

        // Prepare documents directory.
        $documentsdirectory = get_config('local_staticpage', 'documentdirectory');
        $handle = @opendir($documentsdirectory);

        if ($handle) {
            // Array to remember file to be deleted from documents directory.
            $todelete = [];

            // Fetch all files from documents directory.
            while (false !== ($file = readdir($handle))) {
                // Only process .html files.
                $ishtml = strpos($file, '.html');
                if (!$ishtml) {
                    continue;
                }

                // Compose file name and path.
                $filerecord['filename'] = $file;
                $fullpath = $documentsdirectory . '/' . $file;

                // Put file into filearea.
                $fs->create_file_from_pathname($filerecord, $fullpath);

                // Remember file to be deleted.
                $todelete[] = $fullpath;
            }

            // Close documents directory.
            if ($handle) {
                closedir($handle);
            }

            // Show an info message that documents directory is no longer needed.
            $message = get_string('upgrade_notice_2016020307', 'local_staticpage', $documentsdirectory);
            echo html_writer::tag('div', $message, ['class' => 'alert alert-info']);
        }

        // Remove documents directory setting because it is not needed anymore.
        set_config('documentdirectory', null, 'local_staticpage');

        // Remember upgrade savepoint.
        upgrade_plugin_savepoint(true, 2016020309, 'local', 'staticpage');
    }

    if ($oldversion < 2021120803) {
        // Remove documentnavbarsource setting because it was removed from the plugin.
        unset_config('documentnavbarsource', 'local_staticpage');

        // Remember upgrade savepoint.
        upgrade_plugin_savepoint(true, 2021120803, 'local', 'staticpage');
    }

    // PreparaOposiciones: Add database storage for pages.
    if ($oldversion < 2026010601) {
        global $DB;

        $dbman = $DB->get_manager();

        // Define table local_staticpage_pages.
        $table = new xmldb_table('local_staticpage_pages');

        // Adding fields.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('slug', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('title', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('content', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('contentformat', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('metadescription', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('ogimage', XMLDB_TYPE_CHAR, '500', null, null, null, null);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('showintoc', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('usermodified', XMLDB_KEY_FOREIGN, ['usermodified'], 'user', ['id']);

        // Adding indexes.
        $table->add_index('slug', XMLDB_INDEX_UNIQUE, ['slug']);
        $table->add_index('status_sortorder', XMLDB_INDEX_NOTUNIQUE, ['status', 'sortorder']);

        // Create table if it doesn't exist.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Remember upgrade savepoint.
        upgrade_plugin_savepoint(true, 2026010601, 'local', 'staticpage');
    }

    // PreparaOposiciones: Add ogimage field for social sharing.
    if ($oldversion < 2026010602) {
        global $DB;

        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_staticpage_pages');

        // Add ogimage field if it doesn't exist.
        $field = new xmldb_field('ogimage', XMLDB_TYPE_CHAR, '500', null, null, null, null, 'metadescription');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Remember upgrade savepoint.
        upgrade_plugin_savepoint(true, 2026010602, 'local', 'staticpage');
    }

    return true;
}
