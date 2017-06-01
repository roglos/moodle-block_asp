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
 * Script to allow a asp to be removed from a context
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
$contextid  = required_param('contextid',  PARAM_INT);
$confirm    = optional_param('confirm', false, PARAM_BOOL);

// Determine the context and cm.
list($context, $course, $cm) = get_context_info_array($contextid);

// Require login.
require_login($course, false, $cm);

if ($cm) {
    $PAGE->set_cm($cm);
} else {
    $PAGE->set_context($context);
}

// Require the asp:manage capability.
require_capability('block/asp:manage', $context);

// Set various page options.
$PAGE->set_pagelayout('standard');
$PAGE->set_course($course);
$PAGE->set_url('/blocks/asp/removeasp.php', array('aspid' => $aspid, 'contextid' => $contextid));

// Grab the asp.
$asp = new block_asp_asp($aspid);
$tparams = array('aspname' => $asp->name, 'contexttitle' => $context->get_context_name());

// Check that this asp is assigned to this context.
$stepstates = $asp->step_states($contextid);
$statelist = array_filter($stepstates, create_function('$a', 'return isset($a->stateid);'));
if (count($statelist) == 0) {
    throw new block_asp_not_assigned_exception(get_string('aspnotassignedtocontext', 'block_asp', $tparams));
}

// Set the heading and page title.
$title = get_string('removeaspfromcontext', 'block_asp', $tparams);
$PAGE->set_heading($title);
$PAGE->set_title($title);

// Add the breadcrumbs.
$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname', 'block_asp'));
$PAGE->navbar->add($asp->name);
$PAGE->navbar->add(get_string('remove', 'block_asp'));

// The confirmation strings.
$confirmstr = get_string('removeaspcheck', 'block_asp', $tparams);
$confirmurl = new moodle_url('/blocks/asp/removeasp.php',
        array('aspid' => $aspid, 'contextid' => $contextid, 'confirm' => 1));
$returnurl  = new moodle_url('/blocks/asp/overview.php',
        array('aspid' => $aspid, 'contextid' => $contextid));

if ($confirm) {
    // Confirm the session key to stop CSRF.
    require_sesskey();

    // Remove the asp from the context.
    $asp->remove_asp($contextid);

    // Redirect.
    redirect($context->get_url());
}

// Display the delete confirmation dialogue.
echo $OUTPUT->header();
echo $OUTPUT->confirm($confirmstr, $confirmurl, $returnurl);
echo $OUTPUT->footer();
