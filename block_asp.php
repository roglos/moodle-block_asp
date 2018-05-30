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
 * ASP block
 *
 * @package   block_asp
 * @copyright 2011 Lancaster University Network Services Limited
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG;
require_once($CFG->dirroot . '/lib/form/editor.php');

class block_asp extends block_base {
    public function init() {
        global $CFG;
        $this->title = get_string('asp', 'block_asp');
        require_once($CFG->dirroot . '/blocks/asp/locallib.php');
    }

    /**
     * Retrieve the contents of the block
     *
     * If the current context has a asp assigned to it, then the
     * current state of the asp, with current comments, instructions,
     * and other informative data is displayed.
     *
     * If no asp is currently assigned to this context, and the user
     * has permission to manage asps, then the option to select a
     * asp valid for the context is displayed. If no asps are
     * available for the context, then the block is not displayed.
     *
     * If no asp is currently assinged to this context, and the user
     * does not have permission to manage asps, then the block is not
     * displayed.
     *
     * @return  stdClass    containing the block's content
     */
    public function get_content() {
        global $PAGE;

        // Save loops if we have generated the content already.
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content  = new stdClass();

        if (!has_capability('block/asp:view', $this->context)) {
            // We require the asp:view capability at the very least.
            return $this->content;
        }

        $renderer = $this->page->get_renderer('block_asp');

        $state = new block_asp_step_state();
        // Retrieve the active state for this contextid.
        if ($state->load_active_state($this->instance->parentcontextid)) {

            // Update block title.
            $this->title = $state->step()->asp()->name;

            // Include the javascript libraries:
            // Add language strings.
            $PAGE->requires->strings_for_js(array('editcomments', 'nocomments', 'finishstep'), 'block_asp');
            $PAGE->requires->strings_for_js(array('savechanges'), 'moodle');

            // Initialise the YUI module.
            $arguments = array(
                'stateid'    => $state->id,
                'editorid'   => 'wkf-comment-editor',
                'editorname' => 'comment_editor',
            );
            $PAGE->requires->yui_module('moodle-block_asp-comments', 'M.blocks_asp.init_comments',
                array($arguments));
            $PAGE->requires->yui_module('moodle-block_asp-todolist', 'M.blocks_asp.init_todolist',
                array(array('stateid' => $state->id)));

            // Display the block for this state.
            $this->content->text = $renderer->block_display($state);
        } else {
            $asps = new block_asp_asp();
            $previous = $asps->load_context_asps($this->instance->parentcontextid);
            $canadd = has_capability('block/asp:manage', $this->context);

            // If this is a module, retrieve it's name, otherwise try the pagelayout to confirm
            // that this is a course.
            if ($PAGE->cm) {
                $appliesto = $PAGE->cm->modname;
            } else {
                $appliesto = 'course';
            }

            // Retrieve the list of asps and display.
            $addableasps = block_asp_asp::available_asps($appliesto);

            $this->content->text = $renderer->block_display_no_more_steps(
                    $this->instance->parentcontextid, $canadd, $addableasps, $previous);
        }

        return $this->content;
    }

    /**
     * Whether to allow multiple instance of the block (we do not)
     *
     * @return  boolean     We do not allow multiple instances of the block in the same context
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * The applicable formats for the block
     *
     * @return  array       An array of the applicable formats for the block
     */
    public function applicable_formats() {
        return array('all' => true, 'course' => true, 'mod' => true);
    }

    /**
     * Whether the block has configuration (it does)
     *
     * @return  boolean     We do have configuration
     */
    public function has_config() {
        return true;
    }
}
