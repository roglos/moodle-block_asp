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
 * Form for editing steps
 *
 * @package   block_asp
 * @copyright 2011 Lancaster University Network Services Limited
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once(dirname(__FILE__) . '/locallib.php');
require_once($CFG->libdir . '/formslib.php');

class step_edit extends moodleform {

    protected function definition() {
        global $DB;
        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('stepsettings', 'block_asp'));

        // Step data.
        $mform->addElement('text', 'name', get_string('name', 'block_asp'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', null, 'maxlength', 255);

        $mform->addElement('editor',   'instructions_editor', get_string('instructions', 'block_asp'),
                block_asp_editor_options());
        $mform->setType('instructions_editor', PARAM_RAW);
        $mform->addRule('instructions_editor', null, 'required', null, 'client');

        // Scripts.
        $scriptoptions = array('cols' => 80, 'rows' => 8);

        // IDs.
        $mform->addElement('hidden', 'stepid');
        $mform->setType('stepid', PARAM_INT);
        $mform->addElement('hidden', 'aspid');
        $mform->setType('aspid', PARAM_INT);

        // Before or after.
        $mform->addElement('hidden', 'beforeafter');
        $mform->setType('beforeafter', PARAM_INT);

        list($options, $days) = block_asp_step::get_autofinish_options(
                $this->_customdata['appliesto']);

        // Step activation.
        $mform->addElement('header', 'stepactivation', get_string('stepactivation', 'block_asp'));
        $mform->addHelpButton('stepactivation', 'stepactivation', 'block_asp');
        $mform->addElement('textarea', 'onactivescript', get_string('onactivescript', 'block_asp'), $scriptoptions);
        $mform->setType('onactivescript', PARAM_RAW);

        // Step extra notification.
        $mform->addElement('header', 'stepextranotify', get_string('stepextranotify', 'block_asp'));
        $mform->addHelpButton('stepextranotify', 'stepextranotify', 'block_asp');

        $options[''] = get_string('donotnotify', 'block_asp');
        $notificationdate[] = $mform->createElement('select', 'extranotifyoffset', null, $days);
        $notificationdate[] = $mform->createElement('select', 'extranotify', null, $options);
        $mform->addGroup($notificationdate, null, get_string('notificationdate', 'block_asp'), ' ', true);

        $mform->addElement('textarea', 'onextranotifyscript', get_string('onextranotifyscript', 'block_asp'), $scriptoptions);
        $mform->setType('onextranotifyscript', PARAM_RAW);
        $mform->setDefault('onextranotifyscript', '');
        $mform->setDefault('extranotifyoffset', 0);
        $mform->setDefault('extranotify', '');
        $mform->disabledIf('extranotifyoffset', 'extranotify', 'eq', '');
        $mform->disabledIf('onextranotifyscript', 'extranotify', 'eq', '');

        // Step completion.
        $mform->addElement('header', 'stepcompletion', get_string('stepcompletion', 'block_asp'));
        $mform->addHelpButton('stepcompletion', 'stepcompletion', 'block_asp');

        $options[''] = get_string('donotautomaticallyfinish', 'block_asp');
        $mform->addElement('textarea', 'oncompletescript', get_string('oncompletescript', 'block_asp'), $scriptoptions);
        $mform->setType('oncompletescript', PARAM_RAW);
        $autofinish = array();
        $autofinish[] = $mform->createElement('select', 'autofinishoffset', null, $days);
        $autofinish[] = $mform->createElement('select', 'autofinish', null, $options);
        $mform->addGroup($autofinish, null, get_string('automaticallyfinish', 'block_asp'), ' ', true);
        $mform->setDefault('autofinishoffset', 0);
        $mform->setDefault('autofinish', '');
        $mform->disabledIf('autofinishoffset', 'autofinish', 'eq', '');

        // We disable the autofinish functionality for modules other than quiz or external quiz.
        $autofinshallowed = array('course', 'quiz', 'externalquiz');
        if (!in_array($this->_customdata['appliesto'], $autofinshallowed)) {
            // We use disbaleIf function without dependency and conditions, because
            // other modules apart from 'quiz and 'externalquiz' do not support the functionality.
            $mform->disabledIf('autofinishoffset', '');
            $mform->disabledIf('autofinish', '');
        }

        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $step = new block_asp_step($data['stepid']);

        // If the aspid was specified, this step has not yet been created.
        // We need to set the asp temporarily (it'll be overwritten
        // shortly anyway) for script validation to succeed.
        if ($data['aspid']) {
            $step->set_asp($data['aspid']);
        }

        if (isset($data['onactivescript'])) {
            // Validate the onactivescript.
            $script = $step->validate_script($data['onactivescript']);
            if ($script->errors) {
                // Only display the first error.
                $errors['onactivescript'] = get_string('invalidscript', 'block_asp', $script->errors[0]);
            }
        }

        if (isset($data['onextrascript'])) {
            // Validate the onextranotifyscript.
            $script = $step->validate_script($data['onextranotifyscript']);
            if ($script->errors) {
                // Only display the first error.
                $errors['onextranotifyscript'] = get_string('invalidscript', 'block_asp', $script->errors[0]);
            }
        }

        if (isset($data['oncompletescript'])) {
            // Validate the oncompletescript.
            $script = $step->validate_script($data['oncompletescript']);
            if ($script->errors) {
                // Only display the first error.
                $errors['oncompletescript'] = get_string('invalidscript', 'block_asp', $script->errors[0]);
            }
        }

        return $errors;
    }
}
