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
 * Webservice implementations.
 *
 * @package   block_courseselect
 * @author    Alex Morris <alex.morris@catalyst.net.nz
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class courseselect_services
 *
 * @package   block_courseselect
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class courseselect_services extends external_api {

    public static function get_course_categories_parameters() {
        return new external_function_parameters(
            array(
                'parent' => new external_value(PARAM_INT, 'ID of parent category')
            )
        );
    }

    public static function get_course_categories($parent) {
        global $DB;

        // Validate parameters.
        $params = self::validate_parameters(self::get_course_categories_parameters(),
            array('parent' => $parent));

        // Check if class exists required for Totara compatibility.
        if (class_exists(\coursecat::class)) {
            $coursecat = coursecat::get($params['parent']);
        } else {
            $coursecat = core_course_category::get($params['parent']);
        }

        if ($coursecat === null) {
            return array();
        }

        $categories = [];
        foreach ($coursecat->get_children(['sort' => ['name' => 1]]) as $coursecat) {
            $categories[] = (object) [
                'id' => $coursecat->id,
                'name' => $coursecat->name,
                'parent' => $coursecat->parent,
            ];
        }

        return $categories;
    }

    public static function get_course_categories_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'category id'),
                    'name' => new external_value(PARAM_TEXT, 'category name'),
                    'parent' => new external_value(PARAM_INT, 'parent category id'),
                ), 'List of categories'
            )
        );
    }

    public static function get_courses_parameters() {
        return new external_function_parameters(
            array(
                'category' => new external_value(PARAM_INT, 'ID of category')
            )
        );
    }

    public static function get_courses($category) {
        // Validate parameters.
        $params = self::validate_parameters(self::get_courses_parameters(),
            array('category' => $category));

        return get_courses($params['category'], "c.sortorder ASC", "c.id, c.shortname, c.fullname");
    }

    public static function get_courses_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'course id'),
                    'shortname' => new external_value(PARAM_TEXT, 'Shortname'),
                    'fullname' => new external_value(PARAM_TEXT, 'Full name'),
                ), 'List of courses'
            )
        );
    }

    public static function is_confidence_set_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'Course ID'),
            )
        );
    }

    public static function is_confidence_set($courseid) {
        global $DB, $USER;

        // Validate parameters.
        $params = self::validate_parameters(self::is_confidence_set_parameters(),
            array('courseid' => $courseid));

        $set = $DB->record_exists('block_courseselect_conflevel', ['courseid' => $params['courseid'], 'userid' => $USER->id]);

        return ['set' => $set];
    }

    public static function is_confidence_set_returns() {
        return new external_single_structure(
            array(
                'set' => new external_value(PARAM_BOOL, 'Is confidence set')
            ), 'Is confidence for this standard & user set'
        );
    }

    public static function set_confidence_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'Course ID'),
                'confidence' => new external_value(PARAM_INT, 'Confidence level'),
            )
        );
    }

    public static function set_confidence($courseid, $confidence) {
        global $DB, $USER;

        // Validate parameters.
        $params = self::validate_parameters(self::set_confidence_parameters(),
            array('courseid' => $courseid, 'confidence' => $confidence));

        $DB->execute('UPDATE {block_courseselect_conflevel} SET superseded = 1 WHERE superseded = 0 AND userid = :userid AND courseid = :courseid',
            ['courseid' => $params['courseid'], 'userid' => $USER->id]);

        $confidenceLevel = new stdClass();
        $confidenceLevel->userid = $USER->id;
        $confidenceLevel->courseid = $params['courseid'];
        $confidenceLevel->level = $params['confidence'];
        $confidenceLevel->timecreated = time();
        $DB->insert_record('block_courseselect_conflevel', $confidenceLevel);

        return ['confidence' => get_string('indicatedconfidence', 'block_courseselect',
            get_string('confidence:' . $confidenceLevel->level, 'block_courseselect'))];
    }

    public static function set_confidence_returns() {
        return new external_single_structure(
            array(
                'confidence' => new external_value(PARAM_TEXT, 'Updated confidence lang string')
            )
        );
    }

}
