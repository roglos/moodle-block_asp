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
 * ASP edit form
 *
 * @package   block_asp
 * @copyright 2011 Lancaster University Network Services Limited
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once(dirname(__FILE__) . '/locallib.php');
require_once($CFG->libdir . '/formslib.php');

class edit_asp extends moodleform {
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('aspsettings', 'block_asp'));

        // ASP base data.
        $mform->addElement('text',     'shortname',           get_string('shortname', 'block_asp'), array('size' => 80, 'maxlength' => 255));
        $mform->setType('shortname', PARAM_TEXT);
        $mform->addRule('shortname', null, 'required', null, 'client');
        $mform->addRule('shortname', null, 'maxlength', 255);

        $mform->addElement('text',     'name',                get_string('name', 'block_asp'), array('size' => 80, 'maxlength' => 255));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', null, 'maxlength', 255);

        $mform->addElement('editor',   'description_editor',  get_string('description', 'block_asp'),
                block_asp_editor_options());
        $mform->addRule('description_editor', null, 'required', null, 'client');
        $mform->setType('description_editor', PARAM_RAW);

        // What this asp applies to.
        $appliesto = block_asp_appliesto_list();
        $mform->addElement('select',   'appliesto',           get_string('appliesto', 'block_asp'), $appliesto);
        $mform->setType('appliesto', PARAM_TEXT);
        if (!$this->_customdata['is_deletable']) {
            $mform->hardFreeze('appliesto');
        }

        // When reaching the end of the asp, go back to.
        $steplist = array();
        $steplist[null] = get_string('atendfinishasp', 'block_asp');
        $finalstep = null;
        foreach ($this->_customdata['steps'] as $step) {
            $steplist[$step->stepno] = get_string('atendgobacktostepno', 'block_asp', $step);
            $finalstep = $step;
        }

        if ($finalstep) {
            $mform->addElement('select',   'atendgobacktostep',
                    get_string('atendgobacktostep', 'block_asp', $finalstep->stepno), $steplist);
            $mform->setType('atendgobacktostep', PARAM_INT);
        }

        // The current status of this asp.
        $enabledoptions = array();
        $enabledoptions['0'] = get_string('enabled', 'block_asp');
        $enabledoptions['1'] = get_string('disabled', 'block_asp');
        $mform->addElement('select',   'obsolete', get_string('status', 'block_asp'), $enabledoptions);
        $mform->setDefault('obsolete', 1);
        $mform->setType('obsolete', PARAM_INT);

        $mform->addElement('hidden',   'aspid');
        $mform->setType('aspid', PARAM_INT);
        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (isset($data['shortname'])) {
            $asp = new block_asp_asp();
            try {
                $asp->load_asp_from_shortname($data['shortname']);
                if ($asp->id != $data['aspid']) {
                    $errors['shortname'] = get_string('shortnametaken', 'block_asp', $asp->name);
                }
            } catch (block_asp_invalid_asp_exception $e) {
                // Ignore errors here.
            }
        }

        return $errors;
    }
}
