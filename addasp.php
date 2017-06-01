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
 * Add a asp to a context specified
 *
 * @package   block_asp
 * @copyright 2011 Lancaster University Network Services Limited
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');

// Get the submitted paramaters.
$contextid  = required_param('contextid', PARAM_INT);
$aspid = required_param('asp', PARAM_INT);

// Determine the context and cm.
list($context, $course, $cm) = get_context_info_array($contextid);

// Require login and a valid session key.
require_login($course, false, $cm);
require_sesskey();

if ($cm) {
    $PAGE->set_cm($cm);
} else {
    $PAGE->set_context($context);
}

// Require the asp:manage capability.
require_capability('block/asp:manage', $context);

// Add the asp to the specified context.
$asp = new block_asp_asp($aspid);
$asp->add_to_context($contextid);

// Redirect based on the context's URL.
redirect($context->get_url());
