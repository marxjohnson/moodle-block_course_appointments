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
 * Standard plugin version file
 *
 * Defines the block's version and cron interval
 *
 * @package block_course_appointments
 * @author      Mark Johnson <mark.johnson@tauntons.ac.uk>
 * @copyright   2010 Tauntons College, UK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$plugin->version = 2011122100;
$plugin->cron = 3600;
$plugin->requires = 2010112400;
$plugin->component = 'block_course_appointments';
$plugin->maturity = MATURITY_STABLE;
$plugin->release = '2.0 (Build: 2011122100)';
