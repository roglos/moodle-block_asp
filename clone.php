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
 * Clone a asp
 *
 * @package   block_asp
 * @copyright 2011 Lancaster University Network Services Limited
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/clone_form.php');
require_once($CFG->libdir . '/adminlib.php');

// Get the submitted paramaters.
$aspid = required_param('aspid', PARAM_INT);

// This is an admin page.
admin_externalpage_setup('blocksettingasp');

// Require login.
require_login();

// Require the asp:editdefinitions capability.
require_capability('block/asp:editdefinitions', context_system::instance());

// Grab a asp object.
$asp   = new block_asp_asp($aspid);

// Set page and return urls.
$returnurl  = new moodle_url('/blocks/asp/manage.php');
$PAGE->set_url('/blocks/asp/clone.php', array('aspid' => $aspid));

// Page settings.
$title = get_string('cloneaspname', 'block_asp', $asp->name);
$PAGE->set_title($title);
$PAGE->set_heading($title);

// Add the breadcrumbs.
$PAGE->navbar->add(get_string('clone', 'block_asp'));

// Grab the renderer.
$renderer = $PAGE->get_renderer('block_asp');

// Moodle form to clone the asp.
$cloneform = new clone_asp();

if ($cloneform->is_cancelled()) {
    // Form was cancelled.
    redirect($returnurl);
} else if ($data = $cloneform->get_data()) {
    // Form was submitted.
    unset($data->submitbutton);
    unset($data->aspid);

    // Clone the asp using the data given.
    $asp = block_asp_asp::clone_asp($aspid, $data);

    // Redirect to the newly created asp.
    redirect(new moodle_url('/blocks/asp/editsteps.php', array('aspid' => $asp->id)));
}

// Set the clone asp form defaults.
$data = new stdClass();
$data->aspid           = $asp->id;
$data->shortname            = get_string('clonedshortname', 'block_asp', $asp->shortname);
$data->name                 = get_string('clonedname', 'block_asp', $asp->name);
$data->description          = $asp->description;
$data->descriptionformat    = $asp->descriptionformat;
$data->appliesto            = block_asp_appliesto($asp->appliesto);
$data = file_prepare_standard_editor($data, 'description', array('noclean' => true));

$cloneform->set_data($data);

// Display the page and form.
echo $OUTPUT->header();
echo $renderer->clone_asp_instructions($asp);
$cloneform->display();
echo $OUTPUT->footer();
