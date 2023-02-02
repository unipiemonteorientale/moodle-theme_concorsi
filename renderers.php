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
 * Concorsi theme renderable overrides.
 *
 * @package    theme_concorsi
 * @copyright  2023 Roberto Pinna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/quiz/renderer.php');
require_once($CFG->dirroot . '/question/engine/renderer.php');

/**
 * Quiz renderable.
 *
 * @package    theme_concorsi
 * @copyright  2023 Roberto Pinna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_concorsi_mod_quiz_renderer extends mod_quiz_renderer {

    /**
     * Filters the summarydata array and anonymize user data and times.
     *
     * @param array $summarydata contains row data for table
     * @param int $page the current page number
     * @return $summarydata containing filtered row data
     */
    public function filter_review_summary_table($summarydata, $page) {
        if (isset($summarydata['user'])) {
            $summarydata['user']['title']->link = '';
	    $summarydata['user']['content']= $summarydata['user']['content']->text;
        }

        if (isset($summarydata['state'])) {
            unset($summarydata['state']);
        }

        if (isset($summarydata['startedon'])) {
            unset($summarydata['startedon']);
        }

        if (isset($summarydata['completedon'])) {
            unset($summarydata['completedon']);
        }

        if (isset($summarydata['timetaken'])) {
            unset($summarydata['timetaken']);
        }

        if (isset($summarydata['timestamp'])) {
            unset($summarydata['timestamp']);
        }

        if (isset($summarydata['overdue'])) {
            unset($summarydata['overdue']);
        }

        return parent::filter_review_summary_table($summarydata, $page);
    }
}

/**
 * Question renderable.
 *
 * @package    theme_concorsi
 * @copyright  2023 Roberto Pinna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_concorsi_core_question_renderer extends core_question_renderer {
    /**
     * Generate the display of the response history part of the question. This
     * is the table showing all the steps the question has been through.
     *
     * @param question_attempt $qa the question attempt to display.
     * @param qbehaviour_renderer $behaviouroutput the renderer to output the behaviour
     *      specific parts.
     * @param qtype_renderer $qtoutput the renderer to output the question type
     *      specific parts.
     * @param question_display_options $options controls what should and should not be displayed.
     * @return HTML fragment.
     */
    protected function response_history(question_attempt $qa, qbehaviour_renderer $behaviouroutput,
        qtype_renderer $qtoutput, question_display_options $options) {

        // Hide question history.
        return '';
    }
}
