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
 * Contains the Course Select block class.
 *
 * @package   block_courseselect
 * @author    Alex Morris <alex.morris@catalyst.net.nz
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class block_courseselect
 *
 * @package   block_courseselect
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_courseselect extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_courseselect');
    }

    public function has_config() {
        return true;
    }

    function get_required_javascript() {
        parent::get_required_javascript();
        $confidencelevels = (int) get_config('block_courseselect', 'confidencelevels');
        $this->page->requires->js_call_amd('block_courseselect/selector', 'init', array('confidencelevels' => $confidencelevels));
    }

    public function get_content() {
        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass();

        // Check if class exists required for Totara compatibility.
        if (class_exists(\totara_core_renderer::class)) {
            /** @var totara_core_renderer $renderer */
            $renderer = $this->page->get_renderer('totara_core');
        } else {
            $renderer = $this->page->get_renderer('core');
        }

        $parentid = get_config('block_courseselect', 'parentcategory');
        // Check if class exists required for Totara compatibility.
        if (class_exists(\coursecat::class)) {
            $parentcat = coursecat::get($parentid);
        } else {
            $parentcat = core_course_category::get($parentid);
        }

        $subjects = [];
        foreach ($parentcat->get_children() as $cat) {
            $subjects[] = [
                'name' => $cat->get_formatted_name(),
                'id' => $cat->id
            ];
        }

        $this->content->text = $renderer->render_from_template('block_courseselect/block', ['subjects' => $subjects]);

        return $this->content;
    }

}
