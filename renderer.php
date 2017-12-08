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
 * ASP block libraries
 *
 * @package   block_asp
 * @copyright 2011 Lancaster University Network Services Limited
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * block_asp Renderer
 *
 * Class for rendering various block_asp objects
 *
 * @package    block
 * @subpackage asp
 * @copyright  2011 Lancaster University Network Services Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_asp_renderer extends plugin_renderer_base {
    /**
     * Render the block for the specified state
     *
     * @param   object  $state  The block_asp_step_state to render for
     * @return  string          The rendered content
     */
    public function block_display(block_asp_step_state $state, $ajax = false) {
        global $USER, $DB, $COURSE;

        $canmakechanges = block_asp_can_make_changes($state);

        $output = '';
        $externaldbtype = 'mysqli';
        $externaldbhost = 'localhost';
        $externaldbname = 'integrations';
        $externaldbencoding = 'utf-8';
        $externaldbsetupsql = '';
        $externaldbsybasequoting = '';
        $externaldbdebugdb = '';
        $externaldbuser = 'moodle01';
        $externaldbpassword = 'xxxxx';
        $sourcetable = 'usr_data_assessments';

        // Check connection and label Db/Table in cron output for debugging if required.
        if (!$externaldbtype) {
            echo 'Database not defined.<br>';
            return 0;
        } else {
//            echo 'Database: ' . $externaldbtype . '<br>';
        }
        if (!$sourcetable) {
            echo 'Table not defined.<br>';
            return 0;
        } else {
//            echo 'Table: ' . $sourcetable . '<br>';
        }
//        echo 'Starting connection...<br>';

        // Report connection error if occurs.
        if (!$extdb = $this->db_init($externaldbtype, $externaldbhost, $externaldbuser, $externaldbpassword, $externaldbname)) {
            echo 'Error while communicating with external database <br>';
            return 1;
        }

        // Get external table name.
        $course = $DB->get_record('course', array('id' => $COURSE->id));
        $assessments = array();
        if ($course->idnumber) {
            $sql = 'SELECT * FROM ' . $sourcetable . ' WHERE mav_idnumber LIKE "%' . $course->idnumber . '%"';
            if ($rs = $extdb->Execute($sql)) {
                if (!$rs->EOF) {
                    while ($assess = $rs->FetchRow()) {
                        $assess = array_change_key_case($assess, CASE_LOWER);
                        $assess = $this->db_decode($externaldbencoding, $assess);
                        $assessments[] = $assess;
                    }
                }
                $rs->Close();
            } else {
                // Report error if required.
                $extdb->Close();
                echo 'Error reading data from the external course table<br>';
                return 4;
            }
        }
        if (count($assessments) == 0 ) {
            $output .= 'There are no assessments currently recorded in SITS for this module instance.';
        }
        $output .= '<div class="assesslist">';
        foreach ($assessments as $a) {
            $output .= '<div class="assess">';
            $output .= '<h5>'.$a['assessment_name'].'</h5>';
            $output .= '<p>Number: '.$a['assessment_number'].'         : Weighting:'.$a['assessment_weight'].'%<br />';
            $output .= 'Type: '.$a['assessment_type'].'<br />';
            $output .= 'Mark Scheme: '.$a['assessment_markscheme_code'].': '.$a['assessment_markscheme_name'].'</p>';
            $output .= '<h6>Assessment Link Code: '.$a['assessment_idcode'].'</h6>';
            $output .= '</div>';
        }
        $output .='</div>';
        $output .='<div class="aspsteps">';
        // Create the title.
        $output .= html_writer::tag('h3', get_string('activetasktitle', 'block_asp'));

        $output .= html_writer::tag('p', format_string($state->step()->name));

        // Roles overview.
        if ($roles = $state->step()->roles()) {
            $context = $state->context();
            $output .= html_writer::tag('h3', get_string('tobecompletedby', 'block_asp'));

            $who = '';
            $whoelse = array();

            // Got through the list.
            foreach ($roles as $role) {
                if (user_has_role_assignment($USER->id, $role->id, $context->id)) {
                    $who = get_string('youandanyother', 'block_asp');
                }
                $whoelse[] = $role->localname;
            }

            if (empty($who)) {
                // If the current user isn't in the list, make it 'Any ...'.
                $who = get_string('any', 'block_asp');
            }

            if (count($whoelse)) {
                // If any roles are assigned, grab the last one and leave it to one side.
                $lastrole = array_pop($whoelse);

                if (count($whoelse) > 0) {
                    // If there are still other roles assigned, turn them into a list.
                    $who .= implode(', ', $whoelse);
                    $who .= get_string('youor', 'block_asp') . $lastrole;
                } else {
                    // Just add the last role.
                    $who .= $lastrole;
                }
            }

            $output .= html_writer::tag('span', $who);
            $output .= $this->get_popup_button($roles, $context);
            $this->page->requires->yui_module('moodle-block_asp-userinfo', 'M.block_asp.userinfo.init');
        }

        // Instructions.
        $output .= html_writer::tag('h3', get_string('instructions', 'block_asp'));

        $output .= html_writer::tag('div', $state->step()->format_instructions($state->context()));

        // Comments.
        $output .= html_writer::tag('h3', get_string('comments', 'block_asp'));
        $commentsblock = html_writer::start_tag('div', array('class' => 'block_asp_comments'));
        $commenttext = shorten_text(format_text($state->comment, $state->commentformat,
                array('context' => $state->context())), BLOCK_ASP_MAX_COMMENT_LENGTH);
        if ($commenttext) {
            $commentsblock .= $commenttext;
        } else {
            $commentsblock .= get_string('nocomments', 'block_asp');
        }
        $commentsblock .= html_writer::end_tag('div');
        $output .= $commentsblock;

        // To-do list overview.
        if ($todos = $state->todos()) {
            $output .= html_writer::tag('h3', get_string('todolisttitle', 'block_asp'));
            $list = html_writer::start_tag('ul', array('class' => 'block_asp_todolist'));
            foreach ($state->todos() as $todo) {
                $list .= $this->block_display_todo_item($todo, $state->id, $canmakechanges);
            }
            $list .= html_writer::end_tag('ul');
            $output .= $list;
        }

        if ($canmakechanges) {
            // Edit comments.
            $url    = new moodle_url('/blocks/asp/editcomment.php',
                    array('stateid' => $state->id));
            $editbutton = new single_button($url, get_string('editcomments', 'block_asp'), 'get');
            $editbutton->class = 'singlebutton block_asp_editcommentbutton';

            $output .= html_writer::tag('div', $this->output->render($editbutton));

            if (!$ajax) {
                // Output the contents of the edit comment dialogue, hidden.
                // Prepare editor.
                $editor = new MoodleQuickForm_editor('comment_editor', get_string('commentlabel', 'block_asp'),
                        array('id' => 'wkf-comment-editor'), block_asp_editor_options());
                $editor->setValue(array('text' => $state->comment));

                $output .= '<div class="block-asp-panel">
                                <form class="wkf-comments" action='.'>
                                    <div class="wfk-textarea">' .
                                        html_writer::label(get_string('commentlabel', 'block_asp'),
                                                'wkf-comment-editor', false, array('class' => 'accesshide')) .
                                        $editor->toHtml() . '
                                    </div>
                                    <div class="wfk-submit">
                                        <input type="button" class="submitbutton"/>
                                    </div>
                                </form>
                                <div class="loading-lightbox hidden">' .
                                    $this->pix_icon('i/loading', get_string('loading', 'admin'), 'moodle',
                                            array('class' => 'loading-icon')) . '
                                </div>
                            </div>';
            }

            // Finish step.
            $url = new moodle_url('/blocks/asp/finishstep.php',
                    array('stateid' => $state->id));
            $finishbutton = new single_button($url, get_string('finishstep', 'block_asp'), 'get');
            $finishbutton->class = 'singlebutton block_asp_finishstepbutton';

            $output .= html_writer::tag('div', $this->output->render($finishbutton));
        }

        $output .= $this->asp_overview_button($state->contextid, $state->step()->aspid);
        $output .='</div>';


        return $output;
    }

    /**
     * Display a button to go to the asp overview.
     * @param int $contextid the context to display the overiew for.
     * @param int $aspid the asp to display the overiew for.
     * @return string HTML of the button.
     */
    public function asp_overview_button($contextid, $aspid) {
        $url = new moodle_url('/blocks/asp/overview.php', array(
                'contextid' => $contextid, 'aspid' => $aspid));
        $overviewbutton = new single_button($url,
                get_string('aspoverview', 'block_asp'), 'get');
        return html_writer::tag('div', $this->output->render($overviewbutton));
    }

    /**
     * Render the given todo list item as a <li> element with appropriate links
     *
     * @param   object  $todo     The todo stdClass to render
     * @param   integer $stateid  The ID of the state to render for (used for links)
     * @param   boolean $editable Whether this user has permission to make changes to todolist items
     * @return  string            The rendered list item
     */
    public function block_display_todo_item($todo, $stateid, $editable) {
        global $CFG;
        $todoattribs = array();

        // The contents of the list item.
        $text = format_string($todo->task);

        // Determine whether the task has been completed.
        if ($todo->userid) {
            $todoattribs['class']  = ' completed';
        }

        if ($editable) {
            // Generate the URL and Link.
            $returnurl = str_replace($CFG->wwwroot, '', $this->page->url->out(false));
            $url = new moodle_url('/blocks/asp/toggletaskdone.php',
                    array('sesskey' => sesskey(), 'stateid' => $stateid, 'todoid' => $todo->id, 'returnurl' => $returnurl));
            $li  = html_writer::tag('li', html_writer::link($url, $text,
                    array('class' => 'block-asp-todotask', 'id' => 'block-asp-todoid-' . $todo->id)),
                    $todoattribs);
        } else {
            $li  = html_writer::tag('li', $text, $todoattribs);
        }

        // Return the generate list item.
        return $li;
    }

    /**
     * Render the content when there is no active asp.
     * @param $context database record containing the context data
     * @param $addableasps array The list of available asps
     * @param $previous array A list of the previous asps on this
     * context
     * @return string the HTML to output.
     */
    public function block_display_no_more_steps($parentcontextid,
            $canadd, array $addableasps, array $previous = null) {
        $output = '';

        if ($previous) {
            $p = reset($previous);
            $output .= html_writer::tag('p', get_string('nomorestepsleft', 'block_asp'));
            $output .= $this->asp_overview_button($parentcontextid, $p->id);
        }

        if (!$canadd) {
            return $output;
        }

        if (!$previous) {
            // No asp was previously assigned.
            $output .= html_writer::tag('p', get_string('noasp', 'block_asp'));
        }

        if ($addableasps) {
            $url = new moodle_url('/blocks/asp/addasp.php',
                    array('sesskey' => sesskey(), 'contextid' => $parentcontextid));

            $addoptions = array();
            foreach ($addableasps as $wf) {
                $addoptions[$wf->id] = $wf->name;
            }
            $list = new single_select($url, 'asp', $addoptions);
            if ($previous) {
                $list->set_label(get_string('addanotherasp', 'block_asp'));
            } else {
                $list->set_label(get_string('addaasp', 'block_asp'));
            }

            // And generate the output.
            $output .= html_writer::tag('div', $this->output->render($list));
        }

        return $output;
    }

    /**
     * Render the content to display when no more steps remain
     *
     * This is used by the ajax library so that users get feedback when finishing the final step
     *
     * @return  string  The text to render
     */
    public function block_display_step_complete_confirmation() {
        return html_writer::tag('p', get_string('stepfinishconfirmation', 'block_asp'));
    }

    /**
     * Display the interface to manage asps
     *
     * @param   array   $asps  The list of asps to display
     * @param   array   $emails     The list of email templates to display
     * @return  string              The text to render
     */
    public function manage_asps(array $asps, array $emails) {
        $output  = '';

        // The manage asps section.
        $output .= $this->output->heading(get_string('manageasps', 'block_asp'));
        $output .= html_writer::tag('p', get_string('managedescription', 'block_asp'));
        $output .= $this->list_asps($asps);

        // The manage asps section.
        $output .= $this->output->heading(get_string('manageemails', 'block_asp'));
        $output .= html_writer::tag('p', get_string('emaildescription', 'block_asp'));
        $output .= $this->list_emails($emails);

        return $output;
    }

    /**
     * The asp list table
     *
     * Called by manage_asps
     * @param   array   $asps  The list of asps to display
     * @return  string              The text to render
     */
    protected function list_asps($asps) {
        $output  = '';

        // Display the current asps.
        $table = new html_table();
        $table->attributes['class'] = '';
        $table->head        = array();
        $table->colclasses  = array();
        $table->data        = array();
        $table->head[]      = get_string('shortname', 'block_asp');
        $table->head[]      = get_string('name', 'block_asp');
        $table->head[]      = get_string('appliesto', 'block_asp');
        $table->head[]      = '';

        // Check whether each asp is deletable.
        foreach ($asps as $asp) {
            $asp->is_deletable = block_asp_asp::is_asp_deletable($asp->id);
            $table->data[] = $this->asp_row($asp);
        }

        // Create a new asp.
        $emptycell = new html_table_cell();
        $emptycell->colspan = 3;
        $actions = array();
        $add = html_writer::empty_tag('img', array('src'   => $this->output->pix_url('t/add'),
                                                   'class' => 'iconsmall',
                                                   'title' => get_string('createasp', 'block_asp'),
                                                   'alt'   => get_string('createasp', 'block_asp')
                                                ));
        $url = new moodle_url('/blocks/asp/editsettings.php');
        $actions[] = html_writer::link($url, $add);
        $add = html_writer::empty_tag('img', array('src'   => $this->output->pix_url('t/restore'),
                                                   'class' => 'iconsmall',
                                                   'title' => get_string('importasp', 'block_asp'),
                                                   'alt'   => get_string('importasp', 'block_asp')
                                                ));
        $url = new moodle_url('/blocks/asp/import.php');
        $actions[] = html_writer::link($url, $add);
        $addimportcell = new html_table_cell(implode(' ', $actions));
        $addimportcell->attributes['class'] = 'mdl-align';

        $row = new html_table_row(array($emptycell, $addimportcell));
        $table->data[] = $row;
        $output .= html_writer::table($table);
        return $output;
    }

    /**
     * The asp list row
     *
     * Called by list_asps
     * @param   object  $asp   The asp to display
     * @return  string              The text to render
     */
    protected function asp_row(stdClass $asp) {
        $row = new html_table_row();
        $row->attributes['class']   = 'asp';

        // Shortname.
        $cell = new html_table_cell(s($asp->shortname));
        $row->cells[] = $cell;

        // ASP name.
        $cell = new html_table_cell(format_string($asp->name));
        $row->cells[] = $cell;

        // Applies to.
        $cell = new html_table_cell(block_asp_appliesto($asp->appliesto));
        $row->cells[] = $cell;

        // View/Edit steps.
        $url = new moodle_url('/blocks/asp/editsteps.php', array('aspid' => $asp->id));
        $actions[] = html_writer::link($url, html_writer::empty_tag('img', array(
                'src'   => $this->output->pix_url('t/edit'),
                'class' => 'iconsmall',
                'title' => get_string('vieweditasp', 'block_asp'),
                'alt'   => get_string('vieweditasp', 'block_asp')
            )));

        // Export asp.
        $url = new moodle_url('/blocks/asp/export.php', array('sesskey' => sesskey(), 'aspid' => $asp->id));
        $actions[] = html_writer::link($url, html_writer::empty_tag('img', array(
                'src'   => $this->output->pix_url('t/backup'),
                'class' => 'iconsmall',
                'title' => get_string('exportasp', 'block_asp'),
                'alt'   => get_string('exportasp', 'block_asp')
            )));

        // Clone asp.
        $url = new moodle_url('/blocks/asp/clone.php', array('aspid' => $asp->id));
        $actions[] = html_writer::link($url, html_writer::empty_tag('img', array(
                'src'   => $this->output->pix_url('t/copy'),
                'class' => 'iconsmall',
                'title' => get_string('cloneasp', 'block_asp'),
                'alt'   => get_string('cloneasp', 'block_asp')
            )));

        // Disable/Enable asp.
        $cell = new html_table_cell();
        if ($asp->obsolete == BLOCK_ASP_ENABLED) {
            $url = new moodle_url('/blocks/asp/toggleaspobsolete.php',
                    array('sesskey' => sesskey(), 'aspid' => $asp->id));
            $actions[] = html_writer::link($url, html_writer::empty_tag('img', array(
                    'src'   => $this->output->pix_url('t/hide'),
                    'class' => 'iconsmall',
                    'title' => get_string('disableasp', 'block_asp'),
                    'alt'   => get_string('disableasp', 'block_asp')
                )));
        } else {
            $url = new moodle_url('/blocks/asp/toggleaspobsolete.php',
                    array('sesskey' => sesskey(), 'aspid' => $asp->id));
            $actions[] = html_writer::link($url, html_writer::empty_tag('img', array(
                    'src'   => $this->output->pix_url('t/show'),
                    'class' => 'iconsmall',
                    'title' => get_string('enableasp', 'block_asp'),
                    'alt'   => get_string('enableasp', 'block_asp')
                )));
        }

        // Remove asp.
        if ($asp->is_deletable) {
            $url = new moodle_url('/blocks/asp/delete.php', array('aspid' => $asp->id));
            $actions[] = html_writer::link($url, html_writer::empty_tag('img', array(
                    'src'   => $this->output->pix_url('t/delete'),
                    'class' => 'iconsmall',
                    'title' => get_string('removeasp', 'block_asp'),
                    'alt'   => get_string('removeasp', 'block_asp')
                )));
        } else {
            $a = block_asp_asp::in_use_by($asp->id);
            $actions[] = html_writer::empty_tag('img', array(
                    'src'   => $this->output->pix_url('t/delete'),
                    'class' => 'iconsmall',
                    'title' => get_string('cannotdeleteaspinuseby', 'block_asp', $a),
                    'alt'   => get_string('removeasp', 'block_asp')
                ));
        }

        $cell = new html_table_cell(implode(' ', $actions));
        $row->cells[] = $cell;

        return $row;
    }

    /**
     * The email list table
     *
     * Called by manage_asps
     * @param   array   $emails     The list of email templates to display
     * @return  string              The text to render
     */
    protected function list_emails(array $emails) {
        $output = '';

        // Table setup.
        $table = $this->setup_table();
        $table->attributes['class'] = '';
        $table->head[]      = get_string('shortname',       'block_asp');
        $table->head[]      = get_string('emailsubject', 'block_asp');
        $table->head[]      = '';

        // Add the individual emails.
        foreach ($emails as $email) {
            $table->data[] = $this->email_row($email);
        }

        // Create a new email.
        $emptycell  = new html_table_cell();
        $emptycell->colspan = 2;
        $add = html_writer::empty_tag('img', array('src'   => $this->output->pix_url('t/add'),
                                                   'class' => 'iconsmall',
                                                   'title' => get_string('addemail', 'block_asp'),
                                                   'alt'   => get_string('addemail', 'block_asp')
                                                ));
        $url = new moodle_url('/blocks/asp/editemail.php');
        $addnewcell = new html_table_cell(html_writer::link($url, $add));
        $addnewcell->attributes['class'] = 'mdl-align';
        $row = new html_table_row(array($emptycell, $addnewcell));
        $table->data[] = $row;

        $output .= html_writer::table($table);

        return $output;
    }

    /**
     * The e-mail template list row
     *
     * Called by list_emails
     * @param   object  $email      The e-mail template to display
     * @return  string              The text to render
     */
    protected function email_row(stdClass $email) {
        $row = new html_table_row();
        $row->attributes['class']   = 'email';

        // Shortname.
        $cell = new html_table_cell(s($email->shortname));
        $row->cells[] = $cell;

        // Subject.
        $cell = new html_table_cell(format_string($email->subject));
        $row->cells[] = $cell;

        // View/Edit steps.
        $url = new moodle_url('/blocks/asp/editemail.php', array('emailid' => $email->id));
        $actions[] = html_writer::link($url, html_writer::empty_tag('img', array(
                'src'   => $this->output->pix_url('t/edit'),
                'class' => 'iconsmall',
                'title' => get_string('vieweditemail', 'block_asp'),
                'alt'   => get_string('vieweditemail', 'block_asp'),
            )));

        // Remove email.
        if ($email->activecount || $email->completecount) {
            $actions[] = html_writer::empty_tag('img', array(
                    'src'   => $this->output->pix_url('t/delete'),
                    'class' => 'iconsmall',
                    'title' => get_string('cannotremoveemailinuse', 'block_asp'),
                    'alt'   => get_string('deleteemail', 'block_asp'),
                ));
        } else {
            $url = new moodle_url('/blocks/asp/deleteemail.php', array('emailid' => $email->id));
            $actions[] = html_writer::link($url, html_writer::empty_tag('img', array(
                    'src'   => $this->output->pix_url('t/delete'),
                    'class' => 'iconsmall',
                    'title' => get_string('deleteemail', 'block_asp'),
                    'alt'   => get_string('deleteemail', 'block_asp'),
                )));
        }

        // Add the steps.
        $cell = new html_table_cell(implode(' ', $actions));
        $row->cells[] = $cell;

        return $row;
    }

    /**
     * Render a list of steps
     *
     * Used in editsteps.php
     *
     * @param   object  $asp   The asp to display
     * @return  string              The text to render
     */
    public function list_steps($asp) {
        $output = '';

        // List of steps.
        $output .= $this->output->heading(get_string('aspsteps', 'block_asp'));

        // Set up the table and it's headers.
        $table = $this->setup_table();
        $table->attributes['class'] = '';
        $table->head[] = get_string('stepno', 'block_asp');
        $table->head[] = get_string('stepname', 'block_asp');
        $table->head[] = get_string('doerstitle', 'block_asp');
        $table->head[] = get_string('stepinstructions', 'block_asp');
        $table->head[] = get_string('finish', 'block_asp');
        $table->head[] = '';

        // Retrieve a list of steps etc.
        $steps = $asp->steps();
        $info = new stdClass();
        $info->stepcount    = count($steps);
        $info->aspid   = $asp->id;
        $info->appliesto    = $asp->appliesto;

        // The image to add a new step.
        $add = html_writer::empty_tag('img', array('src'   => $this->output->pix_url('t/add'),
                                                   'class' => 'iconsmall',
                                                   'title' => get_string('addstep', 'block_asp'),
                                                   'alt'   => get_string('addstep', 'block_asp')
                                                ));

        // Add a step to the beginning.
        $addempty = new html_table_cell();
        $addempty->colspan = 5;
        $addcell = new html_table_cell(html_writer::link(new moodle_url('/blocks/asp/editstep.php',
                array('aspid' => $asp->id, 'beforeafter' => -1)), $add));
        $addcell->attributes['class'] = 'mdl-align';
        $addrow = new html_table_row(array($addempty, $addcell));
        $table->data[] = $addrow;

        // Process the other steps.
        while ($step = array_shift($steps)) {
            if (count($steps) == 0) {
                $step->finalstep = true;
            }
            $table->data[] = $this->asp_step($step, $info);
        }

        // Add option to add a new step.
        $infocell  = new html_table_cell($this->atendgobackto($asp));
        $infocell->colspan = 5;
        $infocell->attributes['class'] = 'mdl-align';

        $url = new moodle_url('/blocks/asp/editstep.php', array('aspid' => $asp->id));
        $addnewcell = new html_table_cell(html_writer::link($url, $add));
        $addnewcell->attributes['class'] = 'mdl-align';

        $row = new html_table_row(array($infocell, $addnewcell));
        $table->data[] = $row;

        // Display the table.
        $output .= html_writer::table($table);

        return $output;
    }

    protected function asp_step($step, $info) {
        $row = new html_table_row();

        // Step number.
        $cell = new html_table_cell($step->stepno);
        $cell->attributes['class'] = 'mdl-align';
        $row->cells[] = $cell;

        // Name.
        $cell = new html_table_cell(format_string($step->name));
        $row->cells[] = $cell;

        // Roles reponsible for this step.
        $cell = new html_table_cell($this->asp_step_doers($step));
        $row->cells[] = $cell;

        // Instructions.
        $cell = new html_table_cell(format_text($step->instructions, $step->instructionsformat));
        $row->cells[] = $cell;

        // Automatically finish.
        $cell = new html_table_cell($this->asp_step_auto_finish($step, $info->appliesto));
        $row->cells[] = $cell;

        // Modification.
        $actions = array();
        $url = new moodle_url('/blocks/asp/editstep.php', array('stepid' => $step->id));
        $actions[] = html_writer::link($url, html_writer::empty_tag('img', array('src' => $this->output->pix_url('t/edit'),
                                                                           'class' => 'iconsmall',
                                                                           'title' => get_string('editstep', 'block_asp'),
                                                                           'alt'   => get_string('editstep', 'block_asp')
                                                                        )));

        // Add step after this one.
        $url = new moodle_url('/blocks/asp/editstep.php',
                array('aspid' => $info->aspid, 'beforeafter' => $step->stepno));
        $actions[] = html_writer::link($url, html_writer::empty_tag('img', array(
                'src' => $this->output->pix_url('t/add'),
                'class' => 'iconsmall',
                'title' => get_string('addstepafter', 'block_asp'),
                'alt'   => get_string('addstepafter', 'block_asp')
            )));

        // Can't be removed if this is the only step or in use.
        if ($info->stepcount != 1 && !block_asp_step::is_step_in_use($step->id)) {
            $url = new moodle_url('/blocks/asp/deletestep.php', array('stepid' => $step->id));
            $actions[] = html_writer::link($url, html_writer::empty_tag('img', array(
                    'src' => $this->output->pix_url('t/delete'),
                    'class' => 'iconsmall',
                    'title' => get_string('removestep', 'block_asp'),
                    'alt'   => get_string('removestep', 'block_asp')
                )));
        }

        // Move up if this is not the first step.
        if ($step->stepno != 1) {
            $url = new moodle_url('/blocks/asp/movestep.php',
                    array('sesskey' => sesskey(), 'id' => $step->id, 'direction' => 'up'));
            $actions[] = html_writer::link($url, html_writer::empty_tag('img', array(
                    'src'   => $this->output->pix_url('t/up'),
                    'class' => 'iconsmall',
                    'title' => get_string('moveup', 'block_asp'),
                    'alt'   => get_string('moveup', 'block_asp')
                )));
        }

        // Move down if this is not the final step.
        if (!isset($step->finalstep)) {
            $url = new moodle_url('/blocks/asp/movestep.php',
                    array('sesskey' => sesskey(), 'id' => $step->id, 'direction' => 'down'));
            $actions[] = html_writer::link($url, html_writer::empty_tag('img', array(
                    'src'   => $this->output->pix_url('t/down'),
                    'class' => 'iconsmall',
                    'title' => get_string('movedown', 'block_asp'),
                    'alt'   => get_string('movedown', 'block_asp')
                )));
        }

        $cell = new html_table_cell(implode(' ', $actions));
        $row->cells[] = $cell;

        return $row;
    }

    /**
     * Return a sting that indicates whether a given step is set to be finish automatically
     * @param object $stepdata raw data about the step, loaded from the DB.
     * @return string textual description of the settings.
     */
    protected function asp_step_auto_finish($step, $appliesto) {
        // Do not finish this step automatically.
        if (!$step->autofinish || $step->autofinish == 'donotautomaticallyfinish') {
            return get_string('donotautomaticallyfinish', 'block_asp');
        }

        list($options, $days) = block_asp_step::get_autofinish_options($appliesto);

        if ($step->autofinishoffset > 0) {
            // Days after certain condition.
            $days = $step->autofinishoffset / (24 * 60 * 60);
            if ($days == 1) {
                $daysstring = get_string('dayafter', 'block_asp', $days);
            } else {
                $daysstring = get_string('daysafter', 'block_asp', $days);
            }
        } else if ($step->autofinishoffset < 0) {
            // Days before certain condition.
            $days = abs($step->autofinishoffset) / (24 * 60 * 60);
            if ($days == 1) {
                $daysstring = get_string('daybefore', 'block_asp', $days);
            } else {
                $daysstring = get_string('daysbefore', 'block_asp', $days);
            }
        } else {
            // Same day as certain condition.
            $daysstring = get_string('dayas', 'block_asp');
        }

        list($table, $field) = explode(';', $step->autofinish);
        $key = $table . ';' . $field;
        if (array_key_exists($key, $options)) {
            return $daysstring . ' ' . $options[$key];
        }
        return '';
    }

    /**
     * Get all roles that are doers of a given step
     * @param object $stepdata raw data about the step, loaded from the DB.
     * @return string comma-separated list of role names.
     */
    protected function asp_step_doers($stepdata) {
        $step = block_asp_step::make($stepdata);
        $doernames = array();
        foreach ($step->roles() as $doer) {
            $doernames[] = $doer->localname;
        }
        return implode(', ', $doernames);
    }

    protected function asp_information($asp) {
        $output = '';

        // Header and general information.
        $output .= $this->output->heading(get_string('aspinformation', 'block_asp'), 3, 'title header');

        $table = $this->setup_table();
        // ASP name and shortname.
        $row = new html_table_row(array(get_string('name', 'block_asp')));
        $cell = new html_table_cell();
        $data = array('name' => format_string($asp->name), 'shortname' => s($asp->shortname));
        $cell->text = get_string('nameshortname', 'block_asp', $data);
        $row->cells[] = $cell;
        $table->data[] = $row;

        // Description.
        $row = new html_table_row(array(get_string('description', 'block_asp')));
        $cell = new html_table_cell();
        $cell->text = format_text($asp->description, $asp->descriptionformat);
        $row->cells[] = $cell;
        $table->data[] = $row;

        // What contexts does this block apply to.
        $row = new html_table_row(array(get_string('appliesto', 'block_asp')));
        $cell = new html_table_cell();
        $cell->text = $asp->appliesto;
        $row->cells[] = $cell;
        $table->data[] = $row;

        // Status information.
        $row = new html_table_row(array(get_string('status', 'block_asp')));
        $cell = new html_table_cell();
        if ($asp->obsolete == BLOCK_ASP_OBSOLETE) {
            $cell->text = get_string('obsoleteasp', 'block_asp');
        } else {
            $cell->text = get_string('enabledasp', 'block_asp');
        }
        $row->cells[] = $cell;
        $table->data[] = $row;

        // Other info.
        $row = new html_table_row(array(get_string('inuseby', 'block_asp')));
        $cell = new html_table_cell('This asp is active in x contexts');
        $row->cells[] = $cell;
        $table->data[] = $row;

        $output .= html_writer::table($table);
        return $output;
    }

    /**
     * Display the specified asp settings and include links to edit these settings
     *
     * @param   object  $asp   The asp to display
     * @return  object              The renderer to display
     */
    public function display_asp($asp) {
        $output = '';

        // Start the box.
        $output .= $this->output->heading(get_string('aspsettings', 'block_asp'));

        // Setup the table.
        $table = $this->setup_table();
        $table->attributes['class'] = '';

        // Shortname.
        $row = new html_table_row(array(
            get_string('shortname', 'block_asp'),
            s($asp->shortname),
        ));
        $table->data[] = $row;

        // Name.
        $row = new html_table_row(array(
            get_string('name', 'block_asp'),
            format_string($asp->name),
        ));
        $table->data[] = $row;

        // Description.
        $row = new html_table_row(array(
            get_string('description', 'block_asp'),
            format_text($asp->description, $asp->descriptionformat),
        ));
        $table->data[] = $row;

        // Applies to.
        $row = new html_table_row(array(
            get_string('thisaspappliesto', 'block_asp'),
            block_asp_appliesto($asp->appliesto),
        ));
        $table->data[] = $row;

        // Current status.
        $togglelink = new moodle_url('/blocks/asp/toggleaspobsolete.php',
                array('aspid' => $asp->id, 'returnto' => 'editsteps', 'sesskey' => sesskey()));
        if ($asp->obsolete) {
            $status = get_string('aspobsolete', 'block_asp', $togglelink->out());
        } else {
            $status = get_string('aspactive', 'block_asp', $togglelink->out());
        }
        // Count the times the asp is actively in use.
        if ($count = block_asp_asp::in_use_by($asp->id, true)) {
            $status .= get_string('inuseby', 'block_asp', $count);
        } else {
            $status .= get_string('notcurrentlyinuse', 'block_asp');
        }

        $row = new html_table_row(array(
            get_string('aspstatus', 'block_asp'),
            $status
        ));
        $table->data[] = $row;

        // ASP actions.
        $row = new html_table_row();
        $cell = new html_table_cell();
        $cell->colspan = 2;
        $cell->attributes['class'] = 'mdl-align';

        $actions = array();

        // Edit the asp.
        $url = new moodle_url('/blocks/asp/editsettings.php', array('aspid' => $asp->id));
        $actions[] = html_writer::link($url, get_string('edit', 'block_asp'));

        // Clone the asp.
        $url = new moodle_url('/blocks/asp/clone.php', array('aspid' => $asp->id));
        $actions[] = html_writer::link($url, get_string('clone', 'block_asp'));

        // Export the asp.
        $url = new moodle_url('/blocks/asp/export.php', array('sesskey' => sesskey(), 'aspid' => $asp->id));
        $actions[] = html_writer::link($url, get_string('export', 'block_asp'));

        if (block_asp_asp::is_asp_deletable($asp->id)) {
            // Delete the asp.
            $url = new moodle_url('/blocks/asp/delete.php', array('aspid' => $asp->id));
            $actions[] = html_writer::link($url, get_string('delete', 'block_asp'));
        }

        $cell->text = implode(', ', $actions);

        $row->cells[] = $cell;
        $table->data[] = $row;

        // Display the table.
        $output .= html_writer::table($table);

        return $output;
    }

    public function step_todolist($todos, $step) {
        $output = '';

        // Title area.
        $output .= $this->output->heading(get_string('todotitle', 'block_asp'), 3, 'title header');

        // The to-do list.
        $table = $this->setup_table();
        $table->head[] = get_string('todotask', 'block_asp');
        $table->head[] = '';

        foreach ($todos as $todo) {
            $todo->isremovable = true;
            $table->data[] = $this->step_todolist_item($todo);
        }

        // Add option to add a new task.
        $emptycell  = new html_table_cell();
        $add = html_writer::empty_tag('img', array('src'   => $this->output->pix_url('t/add'),
                                                   'class' => 'iconsmall',
                                                   'title' => get_string('addtask', 'block_asp'),
                                                   'alt'   => get_string('addtask', 'block_asp')
                                                ));
        $url = new moodle_url('/blocks/asp/edittask.php', array('stepid' => $step->id));
        $addnewcell = new html_table_cell(html_writer::link($url, $add));

        $row = new html_table_row(array($emptycell, $addnewcell));
        $table->data[] = $row;

        // Display the table.
        $output .= html_writer::table($table);

        return $output;
    }
    protected function step_todolist_item(stdClass $task) {
        $row    = new html_table_row();
        $name   = new html_table_cell(format_string($task->task));
        $actions = array();

        $url    = new moodle_url('/blocks/asp/edittask.php', array('id' => $task->id));
        $actions[] = html_writer::link($url, html_writer::empty_tag('img', array('src'   => $this->output->pix_url('t/edit'),
                                                                           'class' => 'iconsmall',
                                                                           'title' => get_string('edittask', 'block_asp'),
                                                                           'alt'   => get_string('edittask', 'block_asp')
                                                                        )));

        // Obsolete task.
        $url = new moodle_url('/blocks/asp/toggletaskobsolete.php', array('sesskey' => sesskey(), 'taskid' => $task->id));
        if ($task->obsolete == BLOCK_ASP_ENABLED) {
            $actions[] = html_writer::link($url, html_writer::empty_tag('img', array('src'   => $this->output->pix_url('t/hide'),
                                                                            'class' => 'iconsmall',
                                                                            'title' => get_string('hidetask', 'block_asp'),
                                                                            'alt'   => get_string('hidetask', 'block_asp')
                                                                            )));
        } else {
            $actions[] = html_writer::link($url, html_writer::empty_tag('img', array('src'   => $this->output->pix_url('t/show'),
                                                                            'class' => 'iconsmall',
                                                                            'title' => get_string('showtask', 'block_asp'),
                                                                            'alt'   => get_string('showtask', 'block_asp')
                                                                            )));
        }

        // Delete task.
        if ($task->isremovable) {
            $url    = new moodle_url('/blocks/asp/deletetask.php', array('id' => $task->id));
            $actions[] = html_writer::link($url, html_writer::empty_tag('img', array('src'   => $this->output->pix_url('t/delete'),
                                                                            'class' => 'iconsmall',
                                                                            'title' => get_string('removetask', 'block_asp'),
                                                                            'alt'   => get_string('removetask', 'block_asp')
                                                                            )));
        }

        $actions = new html_table_cell(implode(' ', $actions));

        // Put it all together into a row and return the data.
        $row    = new html_table_row(array($name, $actions));
        return $row;
    }

    public function step_doers($roles, $doers, $stepid) {
        $output = '';

        // Title area.
        $output .= $this->output->heading(get_string('doertitle', 'block_asp'), 3, 'title header');

        // The to-do list.
        $table = $this->setup_table();
        $table->head[] = get_string('roles', 'block_asp');
        $table->head[] = '';

        $activedoers = array_map(create_function('$a', 'return $a->id;'), $doers);

        foreach ($roles as $role) {
            if (in_array($role->id, $activedoers)) {
                $role->doer = true;
            } else {
                $role->doer = false;
            }
            $table->data[] = $this->step_doer($role, $stepid);
        }

        // Display the table.
        $output .= html_writer::table($table);

        return $output;
    }
    protected function step_doer($role, $stepid) {
        $row    = new html_table_row();
        $name   = new html_table_cell($role->localname);

        $url = new moodle_url('/blocks/asp/togglerole.php',
                array('sesskey' => sesskey(), 'roleid' => $role->id, 'stepid' => $stepid));
        if ($role->doer) {
            $actions = html_writer::link($url, html_writer::empty_tag('img', array(
                    'src'   => $this->output->pix_url('t/delete'),
                    'class' => 'iconsmall',
                    'title' => get_string('removerolefromstep', 'block_asp'),
                    'alt'   => get_string('removerolefromstep', 'block_asp')
                )));
        } else {
            $actions = html_writer::link($url, html_writer::empty_tag('img', array(
                    'src'   => $this->output->pix_url('t/add'),
                    'class' => 'iconsmall',
                    'title' => get_string('addroletostep', 'block_asp'),
                    'alt'   => get_string('addroletostep', 'block_asp')
                )));
        }

        // Put it all together into a row and return the data.
        $row    = new html_table_row(array($name, $actions));
        return $row;
    }

    /**
     * Show the instructions for creating or editing an e-mail template
     *
     * @param   object  $data   The e-mail data
     * @return  string          The content to render
     */
    public function email_template_instructions($email) {
        $output = '';
        if ($email->id) {
            $output .= $this->output->heading(get_string('editemail', 'block_asp', $email->shortname), 1, 'title');
        } else {
            $output .= $this->output->heading(get_string('createemail', 'block_asp'), 1, 'title');
        }
        $output .= $this->output->container(get_string('edittemplateinstructions', 'block_asp'));
        return $output;
    }

    /**
     * Show the instructions for cloning a asp
     *
     * @param   object  $asp   The asp to clone
     * @return  string              The content to render
     */
    public function clone_asp_instructions($asp) {
        $output = '';
        $output .= $this->output->heading(
                get_string('cloneaspname', 'block_asp', s($asp->shortname)), 1, 'title');
        $output .= $this->output->container(get_string('cloneaspinstructions', 'block_asp'));
        return $output;
    }

    /**
     * Show the instructions for editing a asp
     * @param   stdClass    $asp The asp being editted
     * @return  string      The content to render
     */
    public function edit_asp_instructions(stdClass $data) {
        $output = '';
        if (isset($data->aspid)) {
            $output .= $this->output->heading(get_string('editasp', 'block_asp', $data->shortname), 1, 'title');
        } else {
            $output .= $this->output->heading(get_string('createasp', 'block_asp'), 1, 'title');
        }
        $output .= $this->output->container(get_string('editaspinstructions', 'block_asp'));
        return $output;
    }

    // The following group of functions relate to managing a asp step.
    /**
     * Show the instructions for editing a step
     * @param block_asp_step $step The step being editted
     * @return String the content to render
     */
    public function edit_step_instructions(block_asp_step $step) {
        $output = '';
        $output .= $this->output->heading(get_string('editstepname', 'block_asp', $step->name), 1, 'title');
        $output .= $this->output->container(get_string('editstepinstructions', 'block_asp'));
        return $output;
    }

    /**
     * Show the instructions for creating a new step
     * @param block_asp_asp $asp The asp that this step
     * will belong to
     * @return String the content to render
     */
    public function create_step_instructions(block_asp_asp $asp) {
        $output = '';
        $output .= $this->output->heading(get_string('createstepname', 'block_asp', $asp->name), 1, 'title');
        $output .= $this->output->container(get_string('createstepinstructions', 'block_asp'));
        return $output;
    }

    /**
     * The head work to create a standard table for the asp block
     * @return html_table object with standard settings applied
     */
    protected function setup_table() {
        $table = new html_table();
        $table->head        = array();
        $table->colclasses  = array();
        $table->data        = array();
        return $table;
    }

    public function asp_overview($asp, array $states, $context) {
        $output = '';

        // Add the box, title and description.
        $output .= $this->box_start('generalbox boxwidthwide boxaligncenter', 'block-asp-overview');
        $output .= $this->output->heading(get_string('overview', 'block_asp'));

        $table = $this->setup_table();
        $table->attributes['class'] = 'boxaligncenter';
        $table->head[] = get_string('stepno', 'block_asp');
        $table->head[] = get_string('stepname', 'block_asp');
        $table->head[] = get_string('roles', 'block_asp');
        $table->head[] = get_string('comments', 'block_asp');
        $table->head[] = get_string('state', 'block_asp');
        $table->head[] = get_string('lastmodified', 'block_asp');
        $table->head[] = '';

        // Add each step.
        foreach ($states as $state) {
            $table->data[] = $this->asp_overview_step($state, $context);
        }
        $this->page->requires->yui_module('moodle-block_asp-userinfo', 'M.block_asp.userinfo.init');

        // Add information as to what happens at the end of the asp.
        $table->data[] = $this->asp_overview_step_atend($asp);

        if (has_capability('block/asp:manage', $context)) {
            $url = new moodle_url('/blocks/asp/removeasp.php',
                    array('contextid' => $context->id, 'aspid' => $asp->id));
            $cell = new html_table_cell(html_writer::tag('div',
                    $this->output->render(new single_button($url, get_string('removeasp', 'block_asp')))));
            $cell->attributes['class'] = 'mdl-align';
            $cell->colspan = 6;
            $table->data[] = new html_table_row(array($cell));
        }

        // Put everything together and return.
        $output .= html_writer::table($table);
        $output .= $this->box_end();
        return $output;
    }

    private function asp_overview_step($stepstate, $context) {
        $row = new html_table_row();
        $classes = array('step');

        // Add some CSS classes to help colour-code the states.
        if ($stepstate->state == BLOCK_ASP_STATE_ACTIVE) {
            $classes[] = 'active';
            $state = get_string('state_active', 'block_asp', sprintf('%d', $stepstate->complete));
        } else if ($stepstate->state == BLOCK_ASP_STATE_COMPLETED) {
            $classes[] = 'completed';
            $state = get_string('state_completed', 'block_asp');
        } else if ($stepstate->state == BLOCK_ASP_STATE_ABORTED) {
            $classes[] = 'aborted';
            $state = get_string('state_aborted', 'block_asp', sprintf('%d', $stepstate->complete));
        } else {
            $state = get_string('state_notstarted', 'block_asp');
        }

        if (!is_null($stepstate->complete)) {
            $complete = html_writer::tag('span',
                    get_string('percentcomplete', 'block_asp',
                            format_float($stepstate->complete, 0)),
                    array('class' => 'completeinfo'));
        } else {
            $complete = '';
        }

        // Add all of the classes.
        $row->attributes['class'] = implode(' ', $classes);

        // Step Number.
        $cell = new html_table_cell($stepstate->stepno);
        $cell->attributes['class'] = 'mdl-align';
        $row->cells[] = $cell;

        // Step Name.
        $cell = new html_table_cell(format_string($stepstate->name));
        $cell->attributes['class'] = 'mdl-align';
        $row->cells[] = $cell;

        // Roles reponsible for this step.
        $stateobj = new block_asp_step_state($stepstate->stateid);
        $roles = $stateobj->step()->roles();
        $step = $stateobj->step();
        $cell = new html_table_cell($this->asp_step_doers($step));

        // Add the "Show names(N)" button to the role column.
        $cell->text .= $this->get_popup_button($roles, $context, $stepstate->stepno);

        $row->cells[] = $cell;

        // Comments.
        $cell = new html_table_cell();
        $cell->text = format_text($stepstate->comment, $stepstate->commentformat, array('context' => $context));
        if (!$cell->text) {
            $cell->text  = get_string('nocomment', 'block_asp');
        }
        if ($history = $this->asp_overview_step_history($stepstate->stateid)) {
            $cell->text .= print_collapsible_region($history, 'historyinfo',
                    'history-' . $stepstate->id, get_string('state_history', 'block_asp'),
                    '', true, true);
        }
        $row->cells[] = $cell;

        // Step state.
        $cell = new html_table_cell($state . $complete);
        $cell->attributes['class'] = 'mdl-align';
        $row->cells[] = $cell;

        // Last modified.
        $cell = new html_table_cell();
        if ($stepstate->timemodified) {
            $cell->text = $stepstate->modifieduser . html_writer::tag('span',
                    userdate($stepstate->timemodified), array('class' => 'dateinfo'));
        }
        $cell->attributes['class'] = 'mdl-align';
        $row->cells[] = $cell;

        // Add finish step/jump to step buttons.
        $cell = new html_table_cell();
        if ($stepstate->state == BLOCK_ASP_STATE_ACTIVE) {
            $state = new block_asp_step_state();
            $state->id               = $stepstate->stateid;
            $state->stepid           = $stepstate->id;
            $state->contextid        = $stepstate->contextid;
            $state->state            = $stepstate->state;
            $state->timemodified     = $stepstate->timemodified;
            $state->comment          = $stepstate->comment;
            $state->commentformat    = $stepstate->commentformat;
            if (block_asp_can_make_changes($state)) {
                $cell->text = html_writer::tag('div', $this->finish_step($stepstate->stateid));
            }
        } else {
            if (has_capability('block/asp:manage', $context)) {
                $cell->text = html_writer::tag('div', $this->jump_to_step($stepstate->id, $context->id));
            }
        }
        $row->cells[] = $cell;

        return $row;
    }

    private function asp_overview_step_history($stateid) {
        $history = array();
        foreach (block_asp_step_state::state_changes($stateid) as $change) {
            $a = array();
            $a['newstate']  = get_string('state_history_' . $change->newstate, 'block_asp');
            $a['time']      = userdate($change->timestamp);
            $a['user']      = $change->username;
            $history[]      = html_writer::tag('p', get_string('state_history_detail', 'block_asp', $a));
        }
        return implode("\n", $history);
    }

    /**
     * Render a table row with details on what happens at the end of the asp
     *
     * @param   object  $asp The asp to give information for
     * @return  object  The table row for the atendgobacktostep information
     */
    private function asp_overview_step_atend($asp) {
        $row = new html_table_row();
        $row->attributes['class']        = 'step';

        // Step Number.
        $emptycell = new html_table_cell();
        $row->cells[] = $emptycell;

        // Step Name.
        $cell = new html_table_cell($this->atendgobackto($asp));
        $cell->colspan = 2;

        $row->cells[] = $cell;

        // Time modified.
        $row->cells[] = $emptycell;

        // Add finish step/jump to step buttons.
        $row->cells[] = $emptycell;

        // Return the cell.
        return $row;
    }

    /**
     * What to do at the end of the worklow
     *
     * @param   object  $asp   The asp to return the string for
     * @return  string  The formatted string
     */
    protected function atendgobackto($asp) {
        // At end go back to ...
        $a = array();
        // ... count the steps.
        $a['stepcount'] = count($asp->steps());

        if ($asp->atendgobacktostep) {
            $a['atendgobacktostep'] = $asp->atendgobacktostep;
            return get_string('atendgobacktostepinfo', 'block_asp', $a);
        } else {
            return get_string('atendstop', 'block_asp', $a);
        }
    }

    /**
     * Render a 'Finish step' button
     *
     * @param   integer $stateid The stateid to finish
     * @return  String  The rendered button to take the user to the finishstep form
     */
    protected function finish_step($stateid) {
        $url = new moodle_url('/blocks/asp/finishstep.php', array('stateid' => $stateid));
        return $this->output->render(new single_button($url, get_string('finishstep', 'block_asp'), 'get'));
    }

    /**
     * Render a 'Jump to step' button
     *
     * @param   integer $stateid The stateid to jump to
     * @return  String  The rendered button to take the user to the jumptostep form
     */
    protected function jump_to_step($stepid, $contextid) {
        $url = new moodle_url('/blocks/asp/jumptostep.php', array('stepid' => $stepid, 'contextid' => $contextid));
        return $this->output->render(new single_button($url, get_string('jumptostep', 'block_asp'), 'get'));
    }

    /**
     * Show the instructions for finishing a asp step
     *
     * @return  string      The content to render
     */
    public function finish_step_instructions() {
        $output = '';
        $output .= $this->output->heading(get_string('finishstep', 'block_asp'), 1, 'title');
        $output .= $this->output->container(get_string('finishstepinstructions', 'block_asp'));
        return $output;
    }

    /**
     * Return user infor button
     * @param object $options, array of options passing to userinfo.js
     * @param int $numberofusers, number of users which
     * @return NULL|string
     */
    protected function get_userinfo_button($options, $numberofusers) {
        $stepno = $options['stepno'];
        $disabled = '';
        if ($numberofusers == 0) {
            $disabled = 'disabled="disabled"';
        }
        $userinfobutton = '<input id="userinfo' . $stepno . '"'. $disabled . '" type="submit" name="userinfo' . $stepno . '"
                            value="'. get_string('shownamesx', 'block_asp', $numberofusers) . '"/>';
        $userinfobutton = html_writer::tag('span', $userinfobutton, $options);
        return $userinfobutton;
    }

    /**
     * Returns popup with a header and body where the body an html table
     * @param object $users, array of users who have roles
     */
    protected function get_popup_table($users, $stepno) {
        global $CFG, $DB;

        if (!$users) {
            return null;
        }

        // Get extra user information from the user policies settings.
        $extrafields = array();
        if ($CFG->showuseridentity) {
            $extrafields = explode(',', $CFG->showuseridentity);
        }
        // Set up the table header.
        $tableheader = array();
        $tableheader[] = get_string('name');
        foreach ($extrafields as $field) {
            if ($field === 'phone1') {
                $field = 'phone';
            }
            $tableheader[] = get_string($field);
        }
        $tableheader[] = get_string('roles');

        $data = array();
        foreach ($users as $key => $user) {
            $row = array();
            $row[0] = html_writer::tag('b', fullname($user));
            $extraindex = 1;
            if ($extrafields) {
                foreach ($extrafields as $field) {
                    if ($field == 'email') {
                        $row[$extraindex] = html_writer::link('mailto:' . $user->$field, $user->$field);
                    } else {
                        $row[$extraindex] = $user->$field;
                    }
                    $extraindex++;
                }
            }
            $row[$extraindex] = implode(', ', $user->roles);
            $data[] = $row;
        }
        if (!$data) {
            return null;
        }

        // Create an html table and collect header and the data.
        $table = new html_table();
        $table->head  = $tableheader;
        $table->data  = $data;

        $popupheader = get_string('showpeoplecandotask', 'block_asp');
        if ($stepno > 0) {
            $popupheader .= " (Step $stepno)";
        }
        // Return header and body of the popup.
        return array($popupheader, html_writer::table($table));
    }

    /**
     * Return the button if there are roles
     * @param object $state, block_asp_step_state object
     * @param object $roles
     * @param object $context
     */
    protected function get_popup_button($roles, $context, $stepno = 0) {
        $steptate = new block_asp_step_state();
        $users = $steptate->get_all_users_and_their_roles($roles, $context);
        $numberofusers = count($users);
        list ($header, $body) = $this->get_popup_table($users, $stepno);
        $options = array('class' => 'userinfoclass', 'header' => $header, 'body' => $body, 'stepno' => $stepno);

        if (!$roles) {
            return null;
        }
        return  html_writer::tag('span', ' ' . $this->get_userinfo_button($options, $numberofusers));
    }

    /* Db functions cloned from enrol/db plugin.
     * ========================================= */

    /**
     * Tries to make connection to the external database.
     *
     * @return null|ADONewConnection
     */
    public function db_init($externaldbtype, $externaldbhost, $externaldbuser, $externaldbpassword, $externaldbname) {
        global $CFG;

        require_once($CFG->libdir.'/adodb/adodb.inc.php');

        // Connect to the external database (forcing new connection).
        $extdb = ADONewConnection($externaldbtype);
        if ($externaldbdebugdb) {
            $extdb->debug = true;
            ob_start(); // Start output buffer to allow later use of the page headers.
        }
        // The dbtype my contain the new connection URL, so make sure we are not connected yet.
        if (!$extdb->IsConnected()) {
            $result = $extdb->Connect($externaldbhost,
                $externaldbuser,
                $externaldbpassword,
                $externaldbname, true);
            if (!$result) {
                return null;
            }
        }

        $extdb->SetFetchMode(ADODB_FETCH_ASSOC);
        if ($externaldbsetupsql) {
            $extdb->Execute($externaldbsetupsql);
        }
        return $extdb;
    }

    public function db_addslashes($externaldbsybasequoting, $text) {
        // Use custom made function for now - it is better to not rely on adodb or php defaults.
        if ($externaldbsybasequoting) {
            $text = str_replace('\\', '\\\\', $text);
            $text = str_replace(array('\'', '"', "\0"), array('\\\'', '\\"', '\\0'), $text);
        } else {
            $text = str_replace("'", "''", $text);
        }
        return $text;
    }

    public function db_encode($externaldbencoding, $text) {
        $dbenc = $externaldbencoding;
        if (empty($dbenc) or $dbenc == 'utf-8') {
            return $text;
        }
        if (is_array($text)) {
            foreach ($text as $k => $value) {
                $text[$k] = $this->db_encode($externaldbencoding, $value);
            }
            return $text;
        } else {
            return core_text::convert($text, 'utf-8', $dbenc);
        }
    }

    public function db_decode($externaldbencoding, $text) {
        $dbenc = $externaldbencoding;
        if (empty($dbenc) or $dbenc == 'utf-8') {
            return $text;
        }
        if (is_array($text)) {
            foreach ($text as $k => $value) {
                $text[$k] = $this->db_decode($externaldbencoding, $value);
            }
            return $text;
        } else {
            return core_text::convert($text, $dbenc, 'utf-8');
        }
    }

    public function db_get_sql($table, array $conditions, array $fields, $distinct = false, $sort = "") {
        $fields = $fields ? implode(',', $fields) : "*";
        $where = array();
        if ($conditions) {
            foreach ($conditions as $key => $value) {
                $value = $this->db_encode($this->db_addslashes($externaldbsybasequoting, $value));

                $where[] = "$key = '$value'";
            }
        }
        $where = $where ? "WHERE ".implode(" AND ", $where) : "";
        $sort = $sort ? "ORDER BY $sort" : "";
        $distinct = $distinct ? "DISTINCT" : "";
        $sql = "SELECT $distinct $fields
                  FROM $table
                 $where
                  $sort";
        return $sql;
    }

    public function db_get_sql_like($table2, array $conditions, array $fields, $distinct = false, $sort = "") {
        $fields = $fields ? implode(',', $fields) : "*";
        $where = array();
        if ($conditions) {
            foreach ($conditions as $key => $value) {
                $value = $this->db_encode($this->db_addslashes($externaldbsybasequoting, $value));

                $where[] = "$key LIKE '%$value%'";
            }
        }
        $where = $where ? "WHERE ".implode(" AND ", $where) : "";
        $sort = $sort ? "ORDER BY $sort" : "";
        $distinct = $distinct ? "DISTINCT" : "";
        $sql2 = "SELECT $distinct $fields
                  FROM $table2
                 $where
                  $sort";
        return $sql2;
    }


    /**
     * Returns plugin config value
     * @param  string $name
     * @param  string $default value if config does not exist yet
     * @return string value or default
     */
    public function get_config($name, $default = null) {
        $this->load_config();
        return isset($this->config->$name) ? $this->config->$name : $default;
    }

    /**
     * Sets plugin config value
     * @param  string $name name of config
     * @param  string $value string config value, null means delete
     * @return string value
     */
    public function set_config($name, $value) {
        $pluginname = $this->get_name();
        $this->load_config();
        if ($value === null) {
            unset($this->config->$name);
        } else {
            $this->config->$name = $value;
        }
        set_config($name, $value, "local_$pluginname");
    }

    /**
     * Makes sure config is loaded and cached.
     * @return void
     */
    public function load_config() {
        if (!isset($this->config)) {
            $name = $this->get_name();
            $this->config = get_config("local_$name");
        }
    }

}
