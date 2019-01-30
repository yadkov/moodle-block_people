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
 * Block "people"
 *
 * @package     block
 * @subpackage  block_people
 * @copyright   2013 Alexander Bias, University of Ulm <alexander.bias@uni-ulm.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_people extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_people');
    }

    function applicable_formats() {
        return array('course-view' => true, 'site' => true);
    }

    function has_config() {
        return false;
    }

    function instance_allow_multiple() {
        return false;
    }

    function instance_can_be_hidden() {
        return true;
    }

    function get_content() {
        global $COURSE, $CFG, $DB, $OUTPUT, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        // Prepare output
        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        // Get context
        $currentcontext = $this->page->context;

        // Get teachers separated by roles
        $CFG->coursecontact = trim($CFG->coursecontact);
        if (!empty($CFG->coursecontact)) {
            $teacherroles = explode(',', $CFG->coursecontact);
            var_dump($teacherroles);
            foreach($teacherroles as $tr) {
                $teachers[$tr] = get_role_users($tr, $currentcontext, true, 'u.id, u.lastname, u.firstname, u.firstnamephonetic, u. lastnamephonetic, u.middlename, u.alternatename, u.picture, u.imagealt, u.email', 'u.lastname ASC, u.firstname ASC');
            }
        }

        // Get role names / aliases in course context
        $rolenames = role_get_names($currentcontext, ROLENAME_ALIAS, true);

        // Start teachers list
        $this->content->text .= html_writer::start_tag('div', array('class' => 'teachers'));

        // Check every teacherrole
        foreach ($teachers as $id => $tr) {
            if (count($tr) > 0) {
                // Write heading and open new list
                $this->content->text .= html_writer::start_tag('ul');

                // Do for every teacher with this role
                foreach ($tr as $t) {
                    // Output teacher
                    $this->content->text .= html_writer::start_tag('li');

                    // create user object for picture output
                    $user = new stdClass();
                    $user->id = $t->id;
                    $user->lastname = $t->lastname;
                    $user->firstname = $t->firstname;
                    $user->lastnamephonetic = $t->lastnamephonetic;
                    $user->firstnamephonetic = $t->firstnamephonetic;
                    $user->middlename = $t->middlename;
                    $user->alternatename = $t->alternatename;
                    $user->picture = $t->picture;
                    $user->imagealt = $t->imagealt;
                    $user->email = $t->email;

                    $this->content->text .= $OUTPUT->user_picture($user, array('size' => 30, 'link' => true, 'courseid' => $COURSE->id));
                    $this->content->text .= html_writer::start_tag('div', array('class' => 'name'));
                    $this->content->text .= fullname($t);
                    $this->content->text .= html_writer::end_tag('div');
                    $this->content->text .= html_writer::tag('div', $rolenames[$id]);

                    $this->content->text .= html_writer::end_tag('li');
                }

                // End list
                $this->content->text .= html_writer::end_tag('ul');
            }
        }

        // End teachers list
        $this->content->text .= html_writer::end_tag('div');


        return $this->content;
    }
}
