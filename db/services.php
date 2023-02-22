<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Web service definitions
 *
 * @package   block_courseselect
 * @author    Alex Morris <alex.morris@catalyst.net.nz
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
    'block_courseselect_get_course_categories' => array(
        'classname' => 'courseselect_services',
        'methodname' => 'get_course_categories',
        'classpath' => 'blocks/courseselect/classes/external/courseselect_services.php',
        'description' => 'Get course categories by parent ID',
        'type' => 'read',
        'capabilities' => 'moodle/course:view',
        'ajax' => true,
        'services' => array('block_courseselect_webservice'),
    ),
    'block_courseselect_get_courses' => array(
        'classname' => 'courseselect_services',
        'methodname' => 'get_courses',
        'classpath' => 'blocks/courseselect/classes/external/courseselect_services.php',
        'description' => 'Get courses by category ID',
        'type' => 'read',
        'capabilities' => 'moodle/course:view',
        'ajax' => true,
        'services' => array('block_courseselect_webservice'),
    ),
    'block_courseselect_is_confidence_set' => array(
        'classname' => 'courseselect_services',
        'methodname' => 'is_confidence_set',
        'classpath' => 'blocks/courseselect/classes/external/courseselect_services.php',
        'description' => 'Is confidence set',
        'type' => 'read',
        'ajax' => true,
        'services' => array('block_courseselect_webservice'),
    ),
    'block_courseselect_set_confidence' => array(
        'classname' => 'courseselect_services',
        'methodname' => 'set_confidence',
        'classpath' => 'blocks/courseselect/classes/external/courseselect_services.php',
        'description' => 'Set confidence level for a standard',
        'capabilities' => '',
        'type' => 'write',
        'ajax' => true,
        'services' => array('block_courseselect_webservice'),
    ),
);

$services = array(
    'Course Select block Webservice' => array(
        'functions' => array(),
        'enabled' => 1,
        'restrictedusers' => 0,
        'shortname' => 'block_courseselect_webservice',
    ),
);