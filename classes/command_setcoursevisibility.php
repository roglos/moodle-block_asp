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
 * ASP script command to toggle the course visibility.
 *
 * @package   block_asp
 * @copyright 2011 Lancaster University Network Services Limited
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');


/**
 * The command to set the visibility of the course that the asp is assigned to
 *
 * @package    block
 * @subpackage asp
 * @copyright  2011 Lancaster University Network Services Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class block_asp_command_setcoursevisibility extends block_asp_command {
    public function parse($args, $step, $state = null) {
        $data = new stdClass();
        $data->errors = array();

        // Check that this step asp relatees to an activity.
        if (!parent::is_course($step->asp())) {
            $data->errors[] = get_string('notacourse', 'block_asp');
        }

        // Check for the correct visibility option.
        if ($args == 'hidden') {
            $data->visible = 0;
        } else if ($args == 'visible') {
            $data->visible = 1;
        } else {
            $data->errors[] = get_string('invalidvisibilitysetting', 'block_asp', $args);
        }

        if ($state) {
            $data->id = $state->context()->instanceid;
        }

        return $data;
    }

    public function execute($args, $state) {
        global $DB;
        $data = $this->parse($args, $state->step(), $state);

        // Change the visiblity.
        $DB->update_record('course', $data);
    }
}
