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
 * ASP script command to send an email.
 *
 * @package   block_asp
 * @copyright 2011 Lancaster University Network Services Limited
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');


/**
 * The command handling for sending e-mail
 *
 * @package    block
 * @subpackage asp
 * @copyright  2011 Lancaster University Network Services Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class block_asp_command_email extends block_asp_command {

    /**
     * Parse the supplied arguments into a email name, and list of roles
     *
     * @param   string $args The list of arguments
     * @return  stdClass containing:
     *          - emailname
     *          - roles
     */
    public function parse_args($args) {
        $data = new stdClass();
        $data->errors = array();

        // Break down the line. It should be in the format:
        //      {email} to {rolea} {roleb} {rolen}
        // with any number of role shortnames.
        $line = preg_split('/[\s+]/', $args);

        // Grab the email name.
        $data->emailname = array_shift($line);

        // Shift off the 'to' component.
        $to = array_shift($line);
        if ($to !== 'to') {
            $data->errors[] = get_string('invalidsyntaxmissingto', 'block_asp');
            return $data;
        }

        // Return the remaining roles unprocessed.
        $data->roles = $line;

        return $data;
    }

    /**
     * Static function to parse a command given it's arguments, the step it is associated with, and optionally the state
     *
     * If a state is specified, this may be used to parse the script in a specific context.
     * The provided roles are validated for present with {@link require_role_exists}.
     * The arguments are parsed by {@link parse}.
     *
     * @param   string $args  The list of arguments passed to the command in the script
     * @param   object $step  The step that this command is associated with
     * @param   object $state The state for this script. This may be used to validate this step in the context of the
     *                        provided state.
     * @return  stdClass containing the validated data
     *          - All fields as provided by {@link parse}
     *          - email     - The full body of the email
     *          - context   - If the $state was specified, the context for that state
     *          - users     - The list of users
     *          - errors    - Any errors returned
     */
    public function parse($args, $step, $state = null) {
        // Parse the arguments.
        $data = $this->parse_args($args);

        // Check that the e-mail email exists.
        $data->email = $this->email($data->emailname, $data->errors);
        if ($data->errors) {
            return $data;
        }

        // If we were given a state, then retrieve it's context for use in the execution.
        if ($state) {
            $data->context = $state->context();
        }

        // Check that some roles were specified.
        if (count($data->roles) <= 0) {
            $data->errors[] = get_string('norolesspecified', 'block_asp');
            return $data;
        }

        // Check whether the specified roles exist and fill the list of target users.
        $data->users = array();
        foreach ($data->roles as $role) {
            $thisrole = parent::require_role_exists($role, $data->errors);
            if ($data->errors) {
                return $data;
            }

            if ($state) {
                // We can only get the list of users if we've got a specific context.
                $data->users = array_merge($data->users, parent::role_users($thisrole, $data->context));
            }
        }

        return $data;
    }

    /**
     * Execute the command given the supplied arguments and state.
     * The function calls {@link validate} with the arguments, step and state.
     *
     * Owing to a restriction in the moodle message_send function which prevents messages from being
     * sent whilst in a transaction, we pass sending to block_asp_command_email::message_send
     * which stores them for later.
     *
     * To process the message queue, block_asp_command_email::message_send() must be called
     * outside of a transaction
     *
     * @param   string $args  The list of arguments passed to the command in the script
     * @param   object $state The state for this script. This may be used to validate this step in the context of the
     *                        provided state.
     * @return  void
     */
    public function execute($args, $state) {
        // Validate the command and use it to retrieve the required data.
        $email = $this->parse($args, $state->step(), $state);

        if ($email->errors) {
            // We should never be able to execute a script which contains errors.
            throw new block_asp_invalid_command_exception(get_string('invalidscript', 'block_asp', $email->errors[0]));
        }

        // Fill in the blanks.
        $this->email_params($email, $state);

        // Send the e-mail.
        $eventdata = new stdClass();
        $eventdata->component   = 'block_asp';
        $eventdata->name        = 'notification';
        $eventdata->userfrom    = core_user::get_noreply_user();
        $eventdata->subject     = $email->email->subject;
        $eventdata->fullmessage = $email->email->message;
        $eventdata->fullmessageformat   = FORMAT_HTML;
        $eventdata->fullmessagehtml     = $email->email->message;
        $eventdata->smallmessage        = $eventdata->fullmessage;
        $eventdata->contexturl          = (string) $email->context->get_url();
        $eventdata->contexturlname      = $email->context->get_context_name(false, true);

        /*
         * Because of an issue with the message_send function in moodle core whereby it is not
         * possible to call the function within a transaction, we queue messages here to be called
         * later by the function block_asp_command_email::send_mail()
         * It should be possible to replace this call with message_send($eventdata); if and
         * when this limitation is removed.
         */
        foreach ($email->users as $user) {
            $eventdata->userto          = $user;
            self::message_send($eventdata);
        }
    }

    /**
     * Retrieve the text for the specified email
     *
     * @param   String shortname
     * @return  stdClass The database result for the specified e-mail email
     * @throws  block_asp_invalid_command_exception If the email does not exist
     */
    public function email($shortname, &$errors) {
        global $DB;
        $email = $DB->get_record('block_asp_emails', array('shortname' => $shortname));
        if (!$email) {
            $errors[] = get_string('invalidemailemail', 'block_asp', $shortname);
            return false;
        }
        return $email;
    }

    /**
     * Substitute the standard email parameters. The following parameters are substituted:
     * - %%aspname%%   The name of the asp
     * - %%stepname%%       The name of the step
     * - %%contextname%%    The name of the context for the specified $state
     * - %%coursename%%     The name of the course for the specified $state
     * - %%usernames%%      The list of users to whom this e-mail will be sent
     * - %%currentusername%% The name of the user who caused the state transition.
     * - %%instructions%%   The set of instructions in the step
     * - %%tasks%%          A comma-separated list of todo tasks
     * - %%comment%%        If the specified state is active, then the comment for the current
     *                      state, otherwise the comment for the previous state.
     *
     * @param   stdClass &$email The incoming email
     * @param   block_asp_step_state $state    The block_asp_step_state for the message being sent
     * @return  void
     */
    private function email_params(&$email, $state) {
        global $USER;

        // Shorter accessors.
        $string   = $email->email->message;
        $subject  = $email->email->subject;
        $step     = $state->step();
        $asp = $step->asp();

        // Replace %%aspname%%.
        $subject = str_replace('%%aspname%%', $asp->name, $subject);
        $string = str_replace('%%aspname%%', format_string($asp->name), $string);

        // Replace %%stepname%%.
        $subject = str_replace('%%stepname%%', $step->name, $subject);
        $string = str_replace('%%stepname%%', format_string($step->name), $string);

        // Replace %%contextname%%.
        $contextname = $email->context->get_context_name(false, true);
        $subject = str_replace('%%contextname%%', $contextname, $subject);
        $string = str_replace('%%contextname%%', $contextname, $string);

        // Replace %%contexturl%%.
        $contexturl = $email->context->get_url();
        $subject = str_replace('%%contexturl%%', $contexturl->out(false), $subject);
        $string = str_replace('%%contexturl%%', $contexturl->out(true), $string);

        // Replace %%coursename%%.
        if ($email->context->contextlevel == CONTEXT_COURSE) {
            $coursename = $contextname;
        } else {
            $coursename = $email->context->get_parent_context()->get_context_name(false, true);
        }
        $subject = str_replace('%%coursename%%', $coursename, $subject);
        $string = str_replace('%%coursename%%', $coursename, $string);

        // Replace %%usernames%%.
        $usernames = array_map(create_function('$a', 'return fullname($a);'), $email->users);
        $usernames = implode(', ', $usernames);
        $subject = str_replace('%%usernames%%', $usernames, $subject);
        $string = str_replace('%%usernames%%', $usernames, $string);

        // Replace %%currentusername%%.
        $currentusername = fullname($USER);
        $subject = str_replace('%%currentusername%%', $currentusername, $subject);
        $string = str_replace('%%currentusername%%', $currentusername, $string);

        // Replace %%instructions%%.
        $instructions = $step->format_instructions($email->context);
        $string = str_replace('%%instructions%%', $instructions, $string);

        // Replace %%tasks%%.
        $tasks = array();
        foreach ($step->todos() as $todo) {
            $tasks[] = format_string($todo->task);
        }
        if ($tasks) {
            $tasks = '<ul><li>' . implode('</li><li>', $tasks) . '</li></ul>.';
        } else {
            $tasks = '';
        }
        $string = str_replace('%%tasks%%', $tasks, $string);

        // Replace %%comment%%.
        if ($state->state != BLOCK_ASP_STATE_ACTIVE) {
            $comment = $state->comment;
            $format = $state->commentformat;
        } else if (!empty($state->previouscomment)) {
            $comment = $state->previouscomment;
            $format = $state->previouscommentformat;
        } else {
            $comment = '';
            $format = FORMAT_HTML;
        }
        $string = str_replace('%%comment%%', format_text($comment, $format,
                array('context' => $email->context)), $string);

        // Re-assign the message.
        $email->email->message = $string;
        $email->email->subject = $subject;
    }

    /**
     * This function is provided as a workaround to a @todo in the Moodle message_send function.
     * Unfortunately, at time of writing, the message_send function cannot
     * be called from within a transaction. Doing so will throw a dml_transaction_exception.
     *
     * This workaround must be called to send the e-mail at a later point when not in a transaction
     * and will only attempt to send the messages if no transaction is currently in progress.
     *
     * It is safe to call this function multiple times
     *
     * @access  public
     * @param   object  $eventdata  The message to send
     * @return  void
     */
    public static function message_send($eventdata = null) {
        global $DB, $SITE;

        static $mailqueue = array();

        if ($eventdata) {
            $mailqueue[] = clone $eventdata;
        }

        if (count($mailqueue) > 0 && !$DB->is_transaction_started()) {
            // Only try to send if we're not in a transaction.
            while ($eventdata = array_shift($mailqueue)) {
                // Send each message in the array.
                if (!message_send($eventdata)) {
                    throw new asp_command_failed_exception(get_string('emailfailed', 'block_asp'));
                }
            }
        }
    }
}
