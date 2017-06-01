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
 * DB upgrade
 *
 * @package   block_asp
 * @copyright 2012 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_block_asp_upgrade($oldversion) {
    global $DB, $CFG;

    $dbman = $DB->get_manager();

    if ($oldversion < 2012101700) {
        $table = new xmldb_table('block_asp_steps');

        $field = new xmldb_field('autofinish', XMLDB_TYPE_CHAR, '64', null, null, null, null, 'oncompletescript');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('autofinishoffset', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'autofinish');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_block_savepoint(true, 2012101700, 'asp');
    }

    // Override the oldformat.
    if ($oldversion < 2013042300) {
        $sql = "SELECT step.*
            FROM {block_asp_steps} step
            WHERE step.autofinish = 'quiz_timeopen'
                OR step.autofinish = 'quiz_timeclose'
                OR step.autofinish = 'course_startdate'
            ORDER BY step.id ASC";
        $steps = $DB->get_records_sql($sql);
        if ($steps) {
            foreach ($steps as $key => $step) {
                $step->autofinish = str_replace('_', ';', $step->autofinish);
                $DB->update_record('block_asp_steps', $step);
            }
        }
        upgrade_block_savepoint(true, 2013042300, 'asp');
    }

    // Add 'messageformat' field to the 'block_asp_emails' table.
    if ($oldversion < 2013071600) {
        $table = new xmldb_table('block_asp_emails');
        $field = new xmldb_field('messageformat', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'message');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            if ($CFG->texteditors !== 'textarea') {
                $rs = $DB->get_recordset('block_asp_emails',
                        array('messageformat' => FORMAT_MOODLE), '', 'id,message,messageformat');
                foreach ($rs as $b) {
                    $b->message = text_to_html($b->message, false, false, true);
                    $b->messageformat = FORMAT_HTML;
                    $DB->update_record('block_asp_emails', $b);
                    upgrade_set_timeout();
                }
                $rs->close();
            }
        }
        upgrade_block_savepoint(true, 2013071600, 'asp');
    }

    // Replace 'course:startdate' with 'course;startdate'.
    if ($oldversion < 2013072200) {
        $sql = "UPDATE {block_asp_steps} SET autofinish = :new WHERE autofinish = :old";
        $DB->execute($sql, array('new' => 'course;startdate', 'old' => 'course:startdate'));
        upgrade_block_savepoint(true, 2013072200, 'asp');
    }

    // Fix broken autofinish values.
    if ($oldversion < 2014030500) {
        $DB->set_field_select('block_asp_steps', 'autofinish', null,
                'autofinish IN (?, ?)', array('', 'donotautomaticallyfinish'));
        upgrade_block_savepoint(true, 2014030500, 'asp');
    }

    if ($oldversion < 2015101500) {
        $table = new xmldb_table('block_asp_steps');

        $field = new xmldb_field('extranotify', XMLDB_TYPE_CHAR, '64', null, null, null, null, 'autofinishoffset');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('extranotifyoffset', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'extranotify');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('onextranotifyscript', XMLDB_TYPE_TEXT, null, null, null, null, null, 'extranotifyoffset');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_block_savepoint(true, 2015101500, 'asp');
    }

    return true;
}
