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
 * Defines the class representing a asp.
 *
 * @package   block_asp
 * @copyright 2011 Lancaster University Network Services Limited
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');


/**
 * ASP class
 *
 * Class for handling asp operations, and retrieving information from a asp
 *
 * @copyright  2011 Lancaster University Network Services Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @property-read int       $id                 The ID of the asp
 * @property-read string    $shortname          The shortname of the asp
 * @property-read string    $name               The full name of the asp
 * @property-read string    $description        The formatted description of the asp
 * @property-read int       $descriptionformat  The format of the description field
 * @property-read string    $appliesto          The type of module that the asp applies to, or course
 * @property-read int       $atendgobacktostep  The number of the step to go back to when reaching the final step
 * @property-read int       $obsolete           The visibility of this asp
 */
class block_asp_asp {

    public $id;
    public $shortname;
    public $name;
    public $description;
    public $descriptionformat;
    public $appliesto;
    public $atendgobacktostep;
    public $obsolete;

    /**
     * Constructor to obtain a asp
     *
     * See documentation for {@link load_asp} for further information.
     *
     * @param int $aspid The ID of the asp to load
     * @return Object The asp
     */
    public function __construct($aspid = null) {
        if ($aspid) {
            $this->load_asp($aspid);
        }
    }

    /**
     * Private function to overload the current class instance with a
     * asp object
     *
     * @param stdClass $asp Database record to overload into the
     * object instance
     * @return The instantiated block_asp_asp object
     * @access private
     */
    private function _load($asp) {
        $this->id                   = $asp->id;
        $this->shortname            = $asp->shortname;
        $this->name                 = $asp->name;
        $this->description          = $asp->description;
        $this->descriptionformat    = $asp->descriptionformat;
        $this->appliesto            = $asp->appliesto;
        $this->atendgobacktostep    = $asp->atendgobacktostep;
        $this->obsolete             = $asp->obsolete;
        return $this;
    }

    /**
     * A list of expected settings for a asp
     *
     * @return  array   The list of available settings
     */
    public function expected_settings() {
        return array('id',
            'shortname',
            'name',
            'description',
            'descriptionformat',
            'appliesto',
            'atendgobacktostep',
            'obsolete'
        );
    }

    /**
     * Load a asp given it's ID
     *
     * @param   int $aspid The ID of the asp to load
     * @return  The instantiated block_asp_asp object
     * @throws  block_asp_invalid_asp_exception if the id is not found
     */
    public function load_asp($aspid) {
        global $DB;
        $asp = $DB->get_record('block_asp_asps', array('id' => $aspid));
        if (!$asp) {
            throw new block_asp_invalid_asp_exception(get_string('invalidasp', 'block_asp'));
        }
        return $this->_load($asp);
    }

    /**
     * Load a asp given it's shortname
     *
     * @param   String $shortname The shortname of the asp to load
     * @return  The instantiated block_asp_asp object
     * @throws  block_asp_invalid_asp_exception if the shortname is not found
     */
    public function load_asp_from_shortname($shortname) {
        global $DB;
        $asp = $DB->get_record('block_asp_asps', array('shortname' => $shortname));
        if (!$asp) {
            throw new block_asp_invalid_asp_exception(get_string('invalidasp', 'block_asp'));
        }
        return $this->_load($asp);
    }

    /**
     * Load all asps associated with a context.
     *
     * @param   int $contextid The ID of the context to load asps for.
     * @return  array of stdClasses as returned by the database, most recent first.
     *           Each object has a single field id. This is also the array keys.
     * abstraction layer
     */
    public function load_context_asps($contextid) {
        global $DB;
        $sql = "SELECT asps.id
            FROM {block_asp_step_states} states
            INNER JOIN {block_asp_steps} steps ON steps.id = states.stepid
            INNER JOIN {block_asp_asps} asps ON asps.id = steps.aspid
            WHERE states.contextid = ?
            GROUP BY asps.id
            ORDER BY MAX(states.timemodified) DESC";
        $asps = $DB->get_records_sql($sql, array($contextid));
        return $asps;
    }

    /**
     * Function to create a new asp
     *
     * @param   object  $asp    stdClass containing the shortname, name and description.
     *                               descriptionformat, appliesto and obsolete can additionally be
     *                               specified
     * @param   boolean $createstep  Whether to create the first step
     * @param   boolean $makenamesunique Whether shortname and name should be unique
     * @return  The newly created block_asp_asp object
     * @throws  block_asp_invalid_asp_exception if the supplied shortname is already in use
     */
    public function create_asp($asp, $createstep = true, $makenamesunique = false) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();

        // Check whether a shortname was specified.
        if (empty($asp->shortname)) {
            $transaction->rollback(new block_asp_invalid_asp_exception('invalidshortname', 'block_asp'));
        }

        // Check whether this shortname is already in use.
        if ($DB->get_record('block_asp_asps', array('shortname' => $asp->shortname))) {
            if ($makenamesunique) {
                // Create new name by adding a digit and incrementing it if
                // name already has digit at the end.
                $shortnameclean = preg_replace('/\d+$/', '', $asp->shortname);
                $sql = 'SELECT shortname FROM {block_asp_asps} WHERE shortname LIKE ? ORDER BY shortname DESC LIMIT 1';
                $lastshortname = $DB->get_record_sql($sql, array($shortnameclean."%"));
                if (preg_match('/\d+$/', $lastshortname->shortname)) {
                    $asp->shortname = $lastshortname->shortname;
                    $asp->shortname++;
                } else {
                    $asp->shortname .= '1';
                }
            } else {
                $transaction->rollback(new block_asp_invalid_asp_exception('shortnameinuse', 'block_asp'));
            }
        }

        // Check whether a valid name was specified.
        if (empty($asp->name)) {
            $transaction->rollback(new block_asp_invalid_asp_exception('invalidaspname', 'block_asp'));
        }

        // Check whether this name is already in use.
        if ($DB->get_record('block_asp_asps', array('name' => $asp->name))) {
            if ($makenamesunique) {
                // Create new name by adding a digit and incrementing it if
                // name already has digit at the end.
                $nameclean = preg_replace('/\d+$/', '', $asp->name);
                $sql = 'SELECT name FROM {block_asp_asps} WHERE name LIKE ? ORDER BY name DESC LIMIT 1';
                $lastname = $DB->get_record_sql($sql, array($nameclean."%"));
                if (preg_match('/\d+$/', $lastname->name)) {
                    $asp->name = $lastname->name;
                    $asp->name++;
                } else {
                    $asp->name .= '1';
                }
            } else {
                $transaction->rollback(new block_asp_invalid_asp_exception('nameinuse', 'block_asp'));
            }
        }

        // Set the default description.
        if (!isset($asp->description)) {
            $asp->description = '';
        }

        // Set the default descriptionformat.
        if (!isset($asp->descriptionformat)) {
            $asp->descriptionformat = FORMAT_HTML;
        }

        // Set the default appliesto to 'course'.
        if (!isset($asp->appliesto)) {
            $asp->appliesto = 'course';
        }

        // Check that the appliesto given is valid.
        $pluginlist = block_asp_appliesto_list();
        if (!isset($pluginlist[$asp->appliesto])) {
            $transaction->rollback(new block_asp_invalid_asp_exception('invalidappliestomodule', 'block_asp'));
        }

        // Set the default obsolete value.
        if (!isset($asp->obsolete)) {
            $asp->obsolete = 0;
        }

        // Check that the obsolete value is valid.
        if ($asp->obsolete != 0 && $asp->obsolete != 1) {
            $transaction->rollback(new block_asp_invalid_asp_exception('invalidobsoletesetting', 'block_asp'));
        }

        // Remove any atendgobacktostep -- the steps can't exist yet.
        if (isset($asp->atendgobacktostep)) {
            $transaction->rollback(new block_asp_invalid_asp_exception(
                    get_string('atendgobackataspcreate', 'block_asp')));
        }

        // Check that each of the submitted data is a valid field.
        $expectedsettings = $this->expected_settings();
        foreach ((array) $asp as $k => $v) {
            if (!in_array($k, $expectedsettings)) {
                $transaction->rollback(new block_asp_invalid_asp_exception(
                        get_string('invalidfield', 'block_asp', $k)));
            }
        }

        // Create the asp.
        $asp->id = $DB->insert_record('block_asp_asps', $asp);

        if ($createstep) {
            // Create the initial step using default options.
            $emptystep = new stdClass;
            $emptystep->aspid          = $asp->id;
            $emptystep->name                = get_string('defaultstepname',         'block_asp');
            $emptystep->instructions        = get_string('defaultstepinstructions', 'block_asp');
            $emptystep->instructionsformat  = FORMAT_HTML;
            $emptystep->onactivescript      = get_string('defaultonactivescript',   'block_asp');
            $emptystep->oncompletescript    = get_string('defaultoncompletescript', 'block_asp');
            $emptystep->onextranotifyscript = get_string('defaultonextranotifyscript', 'block_asp');

            $step = new block_asp_step();
            $step->create_step($emptystep);
        }

        $transaction->allow_commit();

        // Reload the object using the returned asp id and return it.
        return $this->load_asp($asp->id);
    }

    /**
     * Clone an existing asp, substituting the data provided
     *
     * @param int $srcid The ID of the asp to clone
     * @param Object  $data  An object containing any data to override
     * @return  The newly created block_asp_asp object
     * @throws  block_asp_invalid_asp_exception if the supplied shortname is already in use
     * @static
     */
    public static function clone_asp($srcid, $data) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();

        // Retrieve the source and copy it.
        $src = new block_asp_asp($srcid);
        $dst = new stdClass();

        // Copy the source based on the allowed settings.
        foreach ($src->expected_settings() as $k) {
            $dst->$k = $src->$k;
        }

        // Grab the description and format if submitted by a mform editor.
        if (isset($data->description_editor)) {
            $data->description          = $data->description_editor['text'];
            $data->descriptionformat    = $data->description_editor['format'];
            unset($data->description_editor);
        }

        // Merge any other new fields in.
        $dst = (object) array_merge((array) $src, (array) $data);

        // Check whether this shortname is already in use.
        if ($DB->get_record('block_asp_asps', array('shortname' => $dst->shortname))) {
            $transaction->rollback(new block_asp_invalid_asp_exception('shortnameinuse', 'block_asp'));
        }

        // Create a clean record.
        // Note: we can't set the atendgobacktostep until we've copied the steps.
        $record = new stdClass();
        $record->shortname          = $dst->shortname;
        $record->name               = $dst->name;
        $record->description        = $dst->description;
        $record->descriptionformat  = $dst->descriptionformat;
        $record->appliesto          = $dst->appliesto;
        $record->obsolete           = $dst->obsolete;

        // Create the asp.
        $record->id = $DB->insert_record('block_asp_asps', $record);

        // Clone any steps.
        foreach ($src->steps() as $step) {
            block_asp_step::clone_step($step->id, $record->id);
        }

        // Set the atendgobacktostep now we have all of our steps.
        $update = new stdClass();
        $update->id                 = $record->id;
        $update->atendgobacktostep  = $dst->atendgobacktostep;
        $DB->update_record('block_asp_asps', $update);

        $transaction->allow_commit();

        // Reload the object using the returned asp id and return it.
        return new block_asp_asp($record->id);
    }

    /**
     * Delete the currently loaded asp
     *
     * Before the asp is actually removed, {@link require_deletable} is
     * called to ensure that it is ready for removal.
     *
     * @return void
     */
    public function delete() {
        global $DB;
        $transaction = $DB->start_delegated_transaction();

        // Check whether we can remove this asp.
        $this->require_deletable();

        // First remove any steps and their associated doers and todos.
        $steps = $DB->get_records('block_asp_step_states', array('id' => $this->id), null, 'id');
        $steplist = array_map(create_function('$a', 'return $a->id;'), $steps);

        $DB->delete_records_list('block_asp_step_doers', 'stepid', $steplist);
        $DB->delete_records_list('block_asp_step_todos', 'stepid', $steplist);
        $DB->delete_records('block_asp_steps', array('aspid' => $this->id));

        // Finally, remove the asp itself.
        $DB->delete_records('block_asp_asps', array('id' => $this->id));
        $transaction->allow_commit();
    }

    /**
     * Return an array of available asps
     *
     * @param   String for  The context in which the asp is for
     * @return  Array of stdClass objects as returned by the database
     *          abstraction layer
     */
    public static function available_asps($for) {
        global $DB;
        $asps = $DB->get_records('block_asp_asps',
                array('appliesto' => $for, 'obsolete' => 0), 'name');
        return $asps;
    }

    /**
     * Add the currently loaded asp to the specified context
     *
     * @param   int $contextid The ID of the context to assign
     */
    public function add_to_context($contextid) {
        global $DB, $USER;
        $transaction = $DB->start_delegated_transaction();

        // Grab a setp quickly.
        $step = new block_asp_step();

        // Can only assign a context to a asp if that context has no asps assigned already.
        try {
            $step->load_active_step($contextid);
            $transaction->rollback(new block_asp_exception(get_string('aspalreadyassigned', 'block_asp')));

        } catch (block_asp_not_assigned_exception $e) {
            // A asp shouldn't be assigned to this context already. A
            // context may only have one asp assigned at a time.
        }

        // ASPs are associated using a step_state.
        // Retrieve the first step of this asp.
        $step->load_asp_stepno($this->id, 1);

        $state = new stdClass();
        $state->stepid              = $step->id;
        $state->timemodified        = time();
        $state->state               = BLOCK_ASP_STATE_ACTIVE;

        // Check whether this asp has been previously assigned to this context.
        $existingstate = $DB->get_record('block_asp_step_states',
                array('stepid' => $step->id, 'contextid' => $contextid));
        if ($existingstate) {
            $state->id              = $existingstate->id;
            $DB->update_record('block_asp_step_states', $state);

        } else {
            // Create a new state to associate the asp with the context.
            $state->comment             = '';
            $state->commentformat       = 1;
            $state->contextid           = $contextid;
            $state->id = $DB->insert_record('block_asp_step_states', $state);
        }
        $state = new block_asp_step_state($state->id);

        // Make a note of the change.
        $statechange = new stdClass;
        $statechange->stepstateid   = $state->id;
        $statechange->newstate      = BLOCK_ASP_STATE_ACTIVE;
        $statechange->userid        = $USER->id;
        $statechange->timestamp     = $state->timemodified; // Use the timestamp from $state to ensure the data matches.
        $DB->insert_record('block_asp_state_changes', $statechange);

        // Process any required scripts for this state.
        $step->process_script($state, $step->onactivescript);

        $transaction->allow_commit();

        // This is a workaround for a limitation of the message_send system.
        // This must be called outside of a transaction.
        block_asp_command_email::message_send();

        return $state;
    }

    /**
     * Aborts the currently loaded asp for the specified context, and
     * removes all traces of it being used
     *
     * @param   int $contextid The ID of the context to abort
     */
    public function remove_asp($contextid) {
        global $DB;

        $transaction = $DB->start_delegated_transaction();

        // Grab the current step_states and check that the asp is assigned to this context.
        $stepstates = $this->step_states($contextid);
        $used = array_filter($stepstates, create_function('$a', 'return isset($a->stateid);'));
        if (count($used) == 0) {
            $transaction->rollback(new block_asp_not_assigned_exception(
                    get_string('aspnotassigned', 'block_asp', $this->name)));
        }

        // We can only abort if the asp is assigned to this contextid.
        $state = new block_asp_step_state();
        try {
            $state->require_active_state($contextid);

            // Abort the step by jumping to no step at all.
            $state->jump_to_step();

        } catch (block_asp_not_assigned_exception $e) {
            // The asp may be inactive so it's safe to catch this exception.
        }

        // Retrieve a list of the step_states.
        $statelist = array_map(create_function('$a', 'return $a->stateid;'), $stepstates);

        // Remove all of the state_change history.
        $DB->delete_records_list('block_asp_state_changes', 'stepstateid', $statelist);

        // Remove the todo_done entries.
        $DB->delete_records_list('block_asp_todo_done', 'stepstateid', $statelist);

        // Remove the states.
        $DB->delete_records('block_asp_step_states', array('contextid' => $contextid));

        // These are all of the required steps for removing a asp from a context, so commit.
        $transaction->allow_commit();
    }

    /**
     * Update the atendgobacktostep setting for the currently loaded
     * asp
     *
     * @param   int $atendgobacktostep The step number to go back to at
     *          the end of the asp, or null for the asp to end
     * @return  An update block_asp_asp record as returned by
     *          {@link load_asp}.
     * @throws  block_asp_invalid_asp_exception if the supplied stepno is
     *          invalid
     */
    public function atendgobacktostep($atendgobacktostep = null) {
        global $DB;

        $transaction = $DB->start_delegated_transaction();

        // Check that we've been given a valid step to loop back to.
        if ($atendgobacktostep && !$DB->get_record('block_asp_steps',
                array('aspid' => $this->id, 'stepno' => $atendgobacktostep))) {
            $transaction->rollback(new block_asp_invalid_asp_exception('invalidstepno', 'block_asp'));
        }

        // Update the asp record.
        $update = new stdClass();
        $update->atendgobacktostep  = $atendgobacktostep;
        $update->id                 = $this->id;
        $DB->update_record('block_asp_asps', $update);

        $transaction->allow_commit();

        // Return the updated asp object.
        return $this->load_asp($this->id);
    }

    /**
     * Toggle the obselete flag for the currently loaded asp
     *
     * @return  An update block_asp_asp record as returned by
     *          {@link load_asp}.
     */
    public function toggle() {
        global $DB;
        $transaction = $DB->start_delegated_transaction();

        $update = new stdClass();
        $update->id = $this->id;

        // Switch the obsolete state of the asp.
        if ($this->obsolete == BLOCK_ASP_ENABLED) {
            $update->obsolete = BLOCK_ASP_OBSOLETE;
        } else {
            $update->obsolete = BLOCK_ASP_ENABLED;
        }

        // Update the record.
        $DB->update_record('block_asp_asps', $update);
        $transaction->allow_commit();

        // Return the updated asp object.
        return $this->load_asp($this->id);
    }

    /**
     * Determine whether the currently loaded asp is in use or not,
     * and thus whether it can be removed.
     *
     * A asp may not be removed if it is currently in use, or has
     * ever been used and thus has state information
     *
     * @param   int $id The ID of the asp (defaults to the id of the current asp)
     * @return  bool whether the asp may be deleted.
     */
    public function is_deletable($id = null) {
        return self::is_asp_deletable($this->id);
    }

    /**
     * Determine whether a asp is in use or not, and thus whether it can be removed.
     *
     * ASPs can only be removed if they are not in use.
     *
     * @param   int $id The ID of the asp.
     * @return  bool whether the asp may be deleted.
     */
    public static function is_asp_deletable($id) {
        return self::in_use_by($id) == 0;
    }

    /**
     * Convenience function to require that a asp is deletable
     *
     * @param   int $id The ID of the asp (defaults to the id of the current asp)
     * @throws  block_asp_exception If the asp is currently in use
     */
    public function require_deletable($id = null) {
        if ($id === null) {
            // Get the current asp id.
            $id = $this->id;
        }

        if (!$this->is_deletable($id)) {
            throw new block_asp_exception(get_string('cannotremoveaspinuse', 'block_asp'));
        }
        return true;
    }

    /**
     * Determine how many locations the specified worklow is in use.
     *
     * @param   int  $id The ID of the asp to check.
     * @param   bool $activeonly Include active states only?
     * @return  int  How many places the asp is in use.
     */
    public static function in_use_by($id, $activeonly = false) {
        global $DB;

        // Determine whether the asp is currently assigned to any
        // step_states, regardless of whether those states are are active
        // or not.
        $sql = "SELECT COUNT(w.id)
                FROM {block_asp_asps} w
                INNER JOIN {block_asp_steps} s ON s.aspid = w.id
                INNER JOIN {block_asp_step_states} st ON st.stepid = s.id
                WHERE w.id = ?
        ";
        if ($activeonly) {
            $sql .= " AND st.state IN ('active')";
        }
        return $DB->count_records_sql($sql, array($id));
    }

    /**
     * Renumber the steps of the currently loaded asp from 1 to n
     *
     * If a step has been removed, or stepno data has somehow entered an inconsistent state, then
     * this function will re-number the steps based upon their current stepno.
     *
     * Database constraints prevent steps with a null stepno, or multiple steps with the same stepno.
     *
     * return   int The number of steps
     */
    public function renumber_steps($from = null, $moveup = 0) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();

        if ($from == null) {
            $from = 0;
        }

        // Retrieve the list of current steps ordered ascendingly by their stepno ASC.
        $sql = 'SELECT id,stepno
                FROM {block_asp_steps}
                WHERE aspid = ? AND stepno > ?
                ORDER BY stepno ASC';
        $steps = $DB->get_records_sql($sql, array($this->id, $from));

        // Check whether the steps are incorrectly ordered in any way.
        $sql = 'SELECT COUNT(stepno) AS count, MAX(stepno) AS max, MIN(stepno) AS min
                FROM {block_asp_steps}
                WHERE aspid = ?';
        $checksteps = $DB->get_record_sql($sql, array($this->id));

        if (($checksteps->count != $checksteps->max) || ($checksteps->min != 0)) {
            $topstep = $checksteps->max + 1;
            foreach ($steps as $step) {
                $topstep++;
                $step->stepno = $topstep;
                $DB->update_record('block_asp_steps', $step);
            }
        }

        // Renumber the steps starting from count($steps) + $from + 1 and going down.
        $topstep = count($steps) + $from + $moveup;

        // Pop elements off the *end* of the array to give them in reverse order.
        while ($step = array_pop($steps)) {
            $step->stepno = $topstep;
            $DB->update_record('block_asp_steps', $step);
            $topstep--;
        }

        $transaction->allow_commit();

        return $from;
    }

    /**
     * Return a list of the steps in a asp
     *
     * @return  Array of stdClass objects as returned by the database
     *          abstraction layer
     */
    public function steps() {
        global $DB;

        // Retrieve all of the steps for this aspid, in order of their
        // ascending stepno.
        $steps = $DB->get_records('block_asp_steps', array('aspid' => $this->id), 'stepno ASC');

        return $steps;
    }

    /**
     * The context level for this asp
     *
     * @return  constant Either CONTEXT_COURSE or CONTEXT_MODULE
     */
    public function context() {
        if ($this->appliesto == 'course') {
            return CONTEXT_COURSE;
        } else {
            // If this asp applies does not apply to a course, then it
            // must be a module.
            return CONTEXT_MODULE;
        }
    }

    /**
     * Return a list of steps with their current state for a specific
     * context
     *
     * @param   int $contextid The ID of the context to retrieve data
     *          for
     * @return  Array of stdClass objects as returned by the database
     *          abstraction layer
     */
    public function step_states($contextid) {
        global $DB;

        // The 'complete' subquery below is written in a more complex way than
        // necessary to work around a MyQSL short-coming.
        // (It was not possible to refer to states.id in an ON clause, only in a WHERE clause.)
        $sql = "SELECT steps.id,
                       steps.stepno,
                       steps.name,
                       steps.instructions,
                       steps.instructionsformat,
                       states.id AS stateid,
                       states.state,
                       states.timemodified,
                       states.comment,
                       states.commentformat,
                       states.contextid,
                       " . $DB->sql_fullname('u.firstname', 'u.lastname') . " AS modifieduser,
                       (
                             SELECT CASE WHEN COUNT(todos.id) > 0 THEN
                                        100.0 * COUNT(done.id) / COUNT(todos.id)
                                    ELSE
                                        NULL
                                    END
                               FROM {block_asp_step_states} inner_states
                               JOIN {block_asp_steps}       inner_steps  ON inner_steps.id   = inner_states.stepid
                               JOIN {block_asp_step_todos}  todos        ON todos.stepid     = inner_steps.id
                          LEFT JOIN {block_asp_todo_done}   done         ON done.steptodoid  = todos.id
                                                                             AND done.stepstateid = inner_states.id
                              WHERE inner_states.id = states.id
                       ) AS complete

                  FROM {block_asp_asps}   asps
                  JOIN {block_asp_steps}         steps  ON steps.aspid = asps.id
             LEFT JOIN {block_asp_step_states}   states ON states.stepid = steps.id
                                                            AND states.contextid = :contextid
             LEFT JOIN {block_asp_state_changes} wsc    ON wsc.id = (
                               SELECT MAX(iwsc.id)
                                 FROM {block_asp_state_changes} iwsc
                                WHERE iwsc.stepstateid = states.id
                       )
             LEFT JOIN {user} u ON u.id = wsc.userid

                 WHERE asps.id = :aspid

        ORDER BY steps.stepno";

        $steps = $DB->get_records_sql($sql, array('contextid' => $contextid, 'aspid' => $this->id));
        return $steps;
    }

    /**
     * Update the current asp with the data provided
     *
     * @param   stdClass $data A stdClass containing the fields to update
     *          for this asp. The id cannot be changed, or specified
     *          in this data set
     * @return  An update block_asp_asp record as returned by
     *          {@link load_asp}.
     */
    public function update($data) {
        global $DB;

        // Retrieve the id for the current asp.
        $data->id = $this->id;

        $transaction = $DB->start_delegated_transaction();

        // Check whether this shortname is already in use.
        if (isset($data->shortname) &&
                ($id = $DB->get_field('block_asp_asps', 'id', array('shortname' => $data->shortname)))) {
            if ($id != $data->id) {
                $transaction->rollback(new block_asp_invalid_asp_exception('shortnameinuse', 'block_asp'));
            }
        }

        // Check that the appliesto given is valid.
        if (isset($data->appliesto)) {
            $pluginlist = block_asp_appliesto_list();
            if (!isset($pluginlist[$data->appliesto])) {
                $transaction->rollback(new block_asp_invalid_asp_exception('invalidappliestomodule', 'block_asp'));
            }
        }

        // Check that the obsolete value is valid.
        if (isset($data->obsolete) && ($data->obsolete != 0 && $data->obsolete != 1)) {
            $transaction->rollback(new block_asp_invalid_asp_exception('invalidobsoletesetting', 'block_asp'));
        }

        // Check the validity of the atendgobacktostep if specified.
        if (isset($data->atendgobacktostep)) {
            $step = new block_asp_step();
            try {
                $step->load_asp_stepno($this->id, $data->atendgobacktostep);
            } catch (Exception $e) {
                $transaction->rollback($e);
            }
        }

        // Check that each of the submitted data is a valid field.
        $expectedsettings = $this->expected_settings();
        foreach ((array) $data as $k => $v) {
            if (!in_array($k, $expectedsettings)) {
                $transaction->rollback(new block_asp_invalid_asp_exception(
                        get_string('invalidfield', 'block_asp', $k)));
            }
        }

        // Update the record.
        $DB->update_record('block_asp_asps', $data);

        $transaction->allow_commit();

        // Return the updated asp object.
        return $this->load_asp($data->id);
    }
}

