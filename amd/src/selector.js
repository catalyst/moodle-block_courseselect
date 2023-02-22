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
 * Controls the subject, level, standard selectors within the Course Select block.
 *
 * @package   block_courseselect
 * @author    Alex Morris <alex.morris@catalyst.net.nz
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/ajax', 'core/modal_factory', 'core/modal_events', 'core/templates', 'core/config', 'core/str'], function(Ajax, ModalFactory, ModalEvents, Templates, Config, Str) {
    function proceedToCourse(courseid) {
        window.location.href = Config.wwwroot + '/course/view.php?id=' + courseid;
    }

    function displayConfidenceModal() {
        var modalPromise = ModalFactory.create({type: ModalFactory.types.SAVE_CANCEL});
        modalPromise.then(function(modal) {
            var titlePromise = Str.get_string('confidencemodaltitle', 'block_courseselect')
                .fail(Notification.exception);
            modal.setTitle(titlePromise);

            var templatePromise = Templates.render('block_courseselect/confidence_modal', {});
            modal.setBody(templatePromise);

            modal.getRoot().on(ModalEvents.hidden, function() {
                // Destroy when hidden, removes modal HTML from document.
                modal.destroy();
            });

            modal.getRoot().on(ModalEvents.save, function() {
                var standards = document.getElementById('block_courseselect-standard-select');
                var standardCourseid = standards.options[standards.selectedIndex].value;

                // Set confidence level, then proceed to course page.
                var confidence = document.querySelector('input[name="block_courseselect-confidence"]:checked').value;
                Ajax.call([{
                    methodname: 'block_courseselect_set_confidence',
                    args: {
                        confidence: confidence,
                        courseid: standardCourseid,
                    }
                }], true)[0].then(function(response) {
                    proceedToCourse(standardCourseid);
                });
            });

            modal.show();

            return modal;
        }).fail(Notification.exception);
    }

    function loadStandards(levelid) {
        Ajax.call([{
            methodname: 'block_courseselect_get_courses',
            args: {
                category: levelid,
            }
        }], true)[0].then(function(response) {
            var standards = document.getElementById('block_courseselect-standard-select');
            for (var i = standards.options.length - 1; i >= 0; i--) {
                standards.remove(i);
            }
            response.forEach(function(standard) {
                standards.options[standards.options.length] = new Option(standard.fullname, standard.id);
            });

            if (standards.selectedIndex === -1) {
                document.getElementById('block_courseselect-complete-practice-assessment').disabled = true;
            }

            // Show standard column.
            document.querySelectorAll('.block_courseselect-standard-column > div').forEach(function(ele) {
                ele.style.display = 'block';
            });
        });
    }

    return {
        init: function(confidencelevels) {
            document.getElementById('block_courseselect-complete-practice-assessment').disabled = true;

            document.getElementById('block_courseselect-subject-select').addEventListener('change', function() {
                Ajax.call([{
                    methodname: 'block_courseselect_get_course_categories',
                    args: {
                        parent: this.value,
                    }
                }], true)[0].then(function(response) {
                    var levels = document.getElementById('block_courseselect-level-select');
                    for (var i = levels.options.length - 1; i >= 0; i--) {
                        levels.remove(i);
                    }
                    response.forEach(function(level) {
                        var selected = false;
                        levels.options[levels.options.length] = new Option(level.name, level.id, selected, selected);
                    });

                    // Show level column.
                    document.querySelectorAll('.block_courseselect-level-column > label, .block_courseselect-level-column > select').forEach(function(ele) {
                        ele.style.display = 'block';
                    });
                    // Hide standard column.
                    document.querySelectorAll('.block_courseselect-standard-column > div').forEach(function(ele) {
                        ele.style.display = 'none';
                    });
                    // Disable button as no standard is selected.
                    document.getElementById('block_courseselect-complete-practice-assessment').disabled = true;
                });
            });

            document.getElementById('block_courseselect-level-select').addEventListener('change', function() {
                loadStandards(this.value);
            });

            document.getElementById('block_courseselect-standard-select').addEventListener('change', function() {
                if (this.value !== null) {
                    document.getElementById('block_courseselect-complete-practice-assessment').disabled = false;
                } else {
                    document.getElementById('block_courseselect-complete-practice-assessment').disabled = true;
                }
            });

            document.getElementById('block_courseselect-standard-search').addEventListener('keyup', function(event) {
                var standards = document.getElementById('block_courseselect-standard-select');
                for (var i = standards.options.length - 1; i >= 0; i--) {
                    if (!standards.options[i].text.includes(event.target.value)) {
                        standards.options[i].style.display = 'none';
                    } else {
                        standards.options[i].style.display = 'block';
                    }
                }
            });

            document.getElementById('block_courseselect-complete-practice-assessment').addEventListener('click', function() {
                var standards = document.getElementById('block_courseselect-standard-select');
                if (standards.selectedIndex === -1) {
                    return;
                }

                var standardCourseid = standards.options[standards.selectedIndex].value;

                Ajax.call([{
                    methodname: 'block_courseselect_is_confidence_set',
                    args: {
                        courseid: standardCourseid,
                    }
                }], true)[0].then(function(response) {
                    if (response.set === true || confidencelevels === 0) {
                        proceedToCourse(standardCourseid);
                    } else {
                        displayConfidenceModal();
                    }
                });
            });
        }
    };
});
