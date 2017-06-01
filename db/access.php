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
 * ASP block role capability definitions.
 *
 * @package block_asp
 * @copyright 2013 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$capabilities = array(

    'block/asp:addinstance' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),

        'clonepermissionsfrom' => 'moodle/site:manageblocks'
    ),

    // Allows access to, and use of the asp definition.
    // By default given to manager.
    'block/asp:editdefinitions'    => array(
        'captype'       => 'write',
        'contextlevel'  => CONTEXT_SYSTEM,
        'archetypes'    => array(
            'manager'   => CAP_ALLOW,
        )
    ),

    // Allows users to see the asp block.
    // By default given to manager and editingteacher.
    'block/asp:view'   => array(
        'captype'       => 'read',
        'contextlevel'  => CONTEXT_COURSE,
        'archetypes'    => array(
            'manager'           => CAP_ALLOW,
            'editingteacher'    => CAP_ALLOW,
        )
    ),

    // Allows use of the 'Jump to step' button.
    // By default given to manager.
    'block/asp:manage' => array(
        'captype'       => 'write',
        'contextlevel'  => CONTEXT_COURSE,
        'archetypes'    => array(
            'manager'   => CAP_ALLOW,
        )
    ),

    // Allows use of the 'Finish step' button.
    // By default given to manager.
    'block/asp:dostep' => array(
        'captype'       => 'write',
        'contextlevel'  => CONTEXT_COURSE,
        'archetypes'    => array(
            'manager'   => CAP_ALLOW,
        )
    )
);
