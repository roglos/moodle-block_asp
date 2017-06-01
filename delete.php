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
 * Script for deleting asps
 *
 * @package   block_asp
 * @copyright 2011 Lancaster University Network Services Limited
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once($CFG->libdir . '/adminlib.php');

// Get the submitted paramaters.
$aspid = required_param('aspid', PARAM_INT);
$confirm    = optional_param('confirm', false, PARAM_BOOL);

// This is an admin page.
admin_externalpage_setup('blocksettingasp');

// Require login.
require_login();

// Require the asp:editdefinitions capability.
require_capability('block/asp:editdefinitions', context_system::instance());

// Load the asp and check that we are allowed to delete it.
$asp   = new block_asp_asp($aspid);
$asp->require_deletable();

// The confirmation strings.
$confirmstr = get_string('deleteaspcheck', 'block_asp', $asp->name);
$confirmurl = new moodle_url('/blocks/asp/delete.php', array('aspid' => $aspid, 'confirm' => 1));
$returnurl  = new moodle_url('/blocks/asp/manage.php');

// Set page url.
$PAGE->set_url('/blocks/asp/delete.php', array('aspid' => $aspid));

// Set the heading and page title.
$title = get_string('confirmaspdeletetitle', 'block_asp', $asp->shortname);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add(get_string('deleteasp', 'block_asp'));

if ($confirm) {
    // Confirm the session key to stop CSRF.
    require_sesskey();

    // Delete the step.
    $asp->delete();

    // Redirect.
    redirect($returnurl);
}

// Display the delete confirmation dialogue.
echo $OUTPUT->header();
echo $OUTPUT->confirm($confirmstr, $confirmurl, $returnurl);
echo $OUTPUT->footer();
