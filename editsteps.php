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
 * Script to display infromation about asp and controls to edit it
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

// This is an admin page.
admin_externalpage_setup('blocksettingasp');

// Require login.
require_login();

// Require the asp:editdefinitions capability.
require_capability('block/asp:editdefinitions', context_system::instance());

// Grab the asp.
$asp   = new block_asp_asp($aspid);

// Set the returnurl as we'll use this in a few places.
$returnurl  = new moodle_url('/blocks/asp/editsteps.php', array('aspid' => $aspid));

// Set various page settings.
$title = get_string('editasp', 'block_asp', $asp->name);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_url($returnurl);

// Add the breadcrumbs.
$PAGE->navbar->add($asp->name, $returnurl);

// Grab the renderer.
$renderer   = $PAGE->get_renderer('block_asp');

// Display the page header.
echo $OUTPUT->header();

// List the current asp settings.
echo $renderer->display_asp($asp);

// List the current asp steps.
echo $renderer->list_steps($asp);

// Display the page footer.
echo $OUTPUT->footer();
