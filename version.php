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
 * ASP block version.
 *
 * @package block_asp
 * @copyright 2013 The Open University / Lancaster University Network Services Limited
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2017110300;
$plugin->requires  = 2015111600;
$plugin->cron      = 60;
$plugin->component = 'block_asp';
$plugin->maturity  = MATURITY_STABLE;
$plugin->release   = 'v2.0';

$plugin->outestssufficient = true;

$plugin->dependencies = array(
    'theme_uogateen'  => 2018051400,
);

