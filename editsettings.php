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
 * Script to allow creation or updating of basic asp settings
 *
 * @package   block_asp
 * @copyright 2011 Lancaster University Network Services Limited
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/edit_asp_form.php');
require_once($CFG->libdir . '/adminlib.php');

// Get the submitted paramaters.
$aspid = optional_param('aspid', 0, PARAM_INT);

// This is an admin page.
admin_externalpage_setup('blocksettingasp');

// Require login.
require_login();

// Require the asp:editdefinitions capability.
require_capability('block/asp:editdefinitions', context_system::instance());

// Grab a asp object.
$asp   = new block_asp_asp();

// Attempt to the set page/return url initially.
$returnurl  = new moodle_url('/blocks/asp/editsteps.php', array('aspid' => $aspid));

if ($aspid) {
    // If we've been given an existing asp.
    $asp->load_asp($aspid);
    $title = get_string('editasp', 'block_asp', $asp->name);
    $PAGE->set_url('/blocks/asp/editsettings.php', array('aspid' => $aspid));
} else {
    // We're creating a new asp.
    $title = get_string('createasp', 'block_asp');
    $PAGE->set_url('/blocks/asp/editsettings.php');
}

// Set the page header and title.
$PAGE->set_title($title);
$PAGE->set_heading($title);

// Add the breadcrumbs.
if ($aspid) {
    $PAGE->navbar->add($asp->name, $returnurl);
    $PAGE->navbar->add(get_string('edit', 'block_asp'));
} else {
    $PAGE->navbar->add(get_string('create', 'block_asp'));
}

// Moodle form to create/edit asp.
$customdata = array('steps' => $asp->steps(), 'is_deletable' => $asp->is_deletable());
$editform = new edit_asp(null, $customdata);

if ($editform->is_cancelled()) {
    // Form was cancelled.
    if ($aspid) {
        redirect($returnurl);
    } else {
        // Cancelled on form creation, so redirect to manage.php instead.
        redirect(new moodle_url('/blocks/asp/manage.php'));
    }
} else if ($data = $editform->get_data()) {
    // Form was submitted.
    $formdata = new stdClass();
    $formdata->id                 = $data->aspid;
    $formdata->shortname          = $data->shortname;
    $formdata->name               = $data->name;
    $formdata->description        = $data->description_editor['text'];
    $formdata->descriptionformat  = $data->description_editor['format'];
    $formdata->obsolete           = $data->obsolete;

    // Only update the appliesto if we have access to it.
    if (isset($data->appliesto)) {
        $formdata->appliesto          = $data->appliesto;
    }

    // Determine what to do at the end of the final asp step.
    if (isset($data->atendgobacktostep)) {
        $formdata->atendgobacktostep  = $data->atendgobacktostep;
    } else {
        $formdata->atendgobacktostep  = null;
    }

    if ($asp->id) {
        $asp->update($formdata);
    } else {
        $asp->create_asp($formdata);
    }

    // Redirect to the editsteps page.
    redirect(new moodle_url('/blocks/asp/editsteps.php', array('aspid' => $asp->id)));
}

$data = new stdClass();

if ($asp->id) {
    // We're editing an existing asp, so set the default data for the form.
    $data->aspid           = $asp->id;
    $data->shortname            = $asp->shortname;
    $data->name                 = $asp->name;
    $data->description          = $asp->description;
    $data->descriptionformat    = $asp->descriptionformat;
    $data->obsolete             = $asp->obsolete;
    $data->appliesto            = $asp->appliesto;
    $data->atendgobacktostep    = $asp->atendgobacktostep;
    $data = file_prepare_standard_editor($data, 'description', array('noclean' => true));
    $editform->set_data($data);
}

// Grab the renderer.
$renderer = $PAGE->get_renderer('block_asp');

// Display the page and form.
echo $OUTPUT->header();
echo $renderer->edit_asp_instructions($data);
$editform->display();
echo $OUTPUT->footer();
