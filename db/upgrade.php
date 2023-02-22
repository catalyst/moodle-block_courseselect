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
 * Tracks upgrades to the Course Select block.
 *
 * @package   block_courseselect
 * @author    Alex Morris <alex.morris@catalyst.net.nz
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade the Course Select block
 *
 * @param int $oldversion
 */
function xmldb_block_courseselect_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2022072800) {
        $table = new xmldb_table('block_courseselect_conflevel');
        $field = new xmldb_field('superseded', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, "0", 'userid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Drop old indexes
        $index = new xmldb_index('block_courseselect_conflevel_courseid', XMLDB_INDEX_NOTUNIQUE, ['courseid']);
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }
        $index = new xmldb_index('block_courseselect_conflevel_userid', XMLDB_INDEX_NOTUNIQUE, ['userid']);
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Create new indexes that combine the fields we search for.
        $index = new xmldb_index('block_courseselect_conflevel_courseid_userid_idx', XMLDB_INDEX_NOTUNIQUE, ['courseid', 'userid']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        $index = new xmldb_index('block_courseselect_conflevel_courseid_userid_superseded_idx', XMLDB_INDEX_NOTUNIQUE, ['courseid', 'userid', 'superseded']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        upgrade_block_savepoint(true, 2022072800, 'courseselect');
    }

    return true;
}
