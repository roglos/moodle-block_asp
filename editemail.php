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
 * create or update an existing email template
 *
 * @package   block_asp
 * @copyright 2011 Lancaster University Network Services Limited
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/editemail_form.php');
require_once($CFG->libdir . '/adminlib.php');

// Get the submitted paramaters.
$emailid = optional_param('emailid', 0, PARAM_INT);
$email   = new block_asp_email();

// This is an admin page.
admin_externalpage_setup('blocksettingasp');

// Require login.
require_login();

// Require the asp:editdefinitions capability.
require_capability('block/asp:editdefinitions', context_system::instance());

// Set the page and return urls.
$PAGE->set_url('/blocks/asp/editemail.php');
$returnurl  = new moodle_url('/blocks/asp/manage.php');

if ($emailid) {
    $email->load_email_id($emailid);
    $title = get_string('editemail', 'block_asp', $email->shortname);
} else {
    $title = get_string('createemail', 'block_asp');
}

// Set the heading and page title.
$PAGE->set_title($title);
$PAGE->set_heading($title);

// Add the breadcrumbs.
if ($email->id) {
    $PAGE->navbar->add(get_string('edittemplate', 'block_asp'));
} else {
    $PAGE->navbar->add(get_string('createtemplate', 'block_asp'));
}

// Create the form.
$emailform = new email_edit();

if ($emailform->is_cancelled()) {
    // Form was cancelled.
    redirect($returnurl);
} else if ($formdata = $emailform->get_data()) {
    // Form has been submitted.
    $data = new stdClass();
    $data->shortname        = $formdata->shortname;
    $data->subject          = $formdata->subject;
    $data->message          = $formdata->message['text'];
    $data->messageformat    = $formdata->message['format'];

    if ($formdata->emailid) {
        // Email id specified, so we're updating.
        $email->update($data);
    } else {
        // Creating a new template.
        $email->create($data);
    }
    redirect($returnurl);
}

// Set the form defaults.
 $email->emailid = $email->id;
 $message = $email->message;
 $email->message = array();
 $email->message['text'] = $message;
 $email->message['format'] = FORMAT_HTML;

 $emailform->set_data($email);

// Grab the renderer.
$renderer = $PAGE->get_renderer('block_asp');

// Display the page and form.
echo $OUTPUT->header();
echo $renderer->email_template_instructions($email);
$emailform->display();
echo $OUTPUT->footer();
