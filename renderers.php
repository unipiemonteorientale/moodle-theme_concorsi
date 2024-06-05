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
 * @package   theme_concorsi
 * @copyright 2023 UPO www.uniupo.it
 * @author    Roberto Pinna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/quiz/renderer.php');
require_once($CFG->dirroot . '/question/engine/renderer.php');

/**
 * Quiz renderable.
 *
 * @package   theme_concorsi
 * @copyright 2023 UPO www.uniupo.it
 * @author    Roberto Pinna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_concorsi_mod_quiz_renderer extends mod_quiz\output\renderer {
    /**
     * Builds the review page
     *
     * @param mod_quiz\quiz_attempt $attemptobj an instance of quiz_attempt.
     * @param array $slots an array of intgers relating to questions.
     * @param int $page the current page number
     * @param bool $showall whether to show entire attempt on one page.
     * @param bool $lastpage if true the current page is the last page.
     * @param mod_quiz\question\display_options $displayoptions instance of display_options.
     * @param array $summarydata contains all table data
     * @return $output containing html data.
     */
    public function review_page(mod_quiz\quiz_attempt $attemptobj, $slots, $page, $showall,
                                $lastpage, mod_quiz\question\display_options $displayoptions,
                                $summarydata) {
        global $USER;

        $filehash = '';
        if (($USER->id == $attemptobj->get_userid()) && !$attemptobj->is_preview()) {
            $attemptid = $attemptobj->get_attemptid();
            $quiz = $attemptobj->get_quiz();

            $context = context_module::instance($attemptobj->get_cmid());
            $component = 'quiz_concorsi';
            $filearea = 'quiz_reviews';
            $itemid = $quiz->id;

            $idnumber = str_pad($USER->idnumber, 6, '0', STR_PAD_LEFT);
            if ($quiz->attempts == 1) {
                $filename = clean_param(fullname($USER) . '-' . $idnumber . '.pdf', PARAM_FILE);
            } else {
                $filename = clean_param(fullname($USER) . '-' . $idnumber . '-' . $attemptid . '.pdf', PARAM_FILE);
            }

            $fs = get_file_storage();
            $file = $fs->get_file($context->id, $component, $filearea, $itemid, '/', $filename);
            if (!empty($file)) {
                $filehash = $file->get_contenthash();
            }
        }

        $output = '';
        $output .= $this->header();
        $output .= $this->review_summary_table($summarydata, $page);
        $output .= $this->review_form($page, $showall, $displayoptions,
                $this->questions($attemptobj, true, $slots, $page, $showall, $displayoptions),
                $attemptobj);

        if (!empty($filehash)) {
            $output .= html_writer::tag('div', get_string('filehash', 'theme_concorsi', $filehash), ['class' => 'filehash']);
        }

        $output .= $this->review_next_navigation($attemptobj, $page, $lastpage, $showall);
        $output .= $this->footer();

        return $output;
    }

    /**
     * Generates the table of data
     *
     * @param stdClass $quiz the quiz settings.
     * @param context_module $context the quiz context.
     * @param view_page $viewobj
     */
    public function view_table($quiz, $context, $viewobj) {
        global $USER;

        if (!$viewobj->attempts) {
            return '';
        }

        // Prepare table header.
        $table = new html_table();
        $table->attributes['class'] = 'generaltable quizattemptsummary';
        $table->caption = get_string('summaryofattempts', 'quiz');
        $table->captionhide = true;
        $table->head = [];
        $table->align = [];
        $table->size = [];
        if ($viewobj->attemptcolumn) {
            $table->head[] = get_string('attemptnumber', 'quiz');
            $table->align[] = 'center';
            $table->size[] = '';
        }
        $table->head[] = get_string('attemptstate', 'quiz');
        $table->align[] = 'left';
        $table->size[] = '';
        if ($viewobj->markcolumn) {
            $table->head[] = get_string('marks', 'quiz') . ' / ' .
                    quiz_format_grade($quiz, $quiz->sumgrades);
            $table->align[] = 'center';
            $table->size[] = '';
        }
        if ($viewobj->gradecolumn) {
            $table->head[] = get_string('gradenoun') . ' / ' .
                    quiz_format_grade($quiz, $quiz->grade);
            $table->align[] = 'center';
            $table->size[] = '';
        }
        if ($viewobj->canreviewmine) {
            $table->head[] = get_string('review', 'quiz');
            $table->align[] = 'center';
            $table->size[] = '';
        }
        if ($viewobj->feedbackcolumn) {
            $table->head[] = get_string('feedback', 'quiz');
            $table->align[] = 'left';
            $table->size[] = '';
        }
        $table->head[] = get_string('reporthash', 'theme_concorsi');
        $table->align[] = 'center';
        $table->size[] = '';

        // One row for each attempt.
        foreach ($viewobj->attemptobjs as $attemptobj) {
            $attemptoptions = $attemptobj->get_display_options(true);
            $row = [];

            // Add the attempt number.
            if ($viewobj->attemptcolumn) {
                if ($attemptobj->is_preview()) {
                    $row[] = get_string('preview', 'quiz');
                } else {
                    $row[] = $attemptobj->get_attempt_number();
                }
            }

            $row[] = $this->attempt_state($attemptobj);

            if ($viewobj->markcolumn) {
                if ($attemptoptions->marks >= question_display_options::MARK_AND_MAX &&
                        $attemptobj->is_finished()) {
                    $row[] = quiz_format_grade($quiz, $attemptobj->get_sum_marks());
                } else {
                    $row[] = '';
                }
            }

            // Outside the if because we may be showing feedback but not grades.
            $attemptgrade = quiz_rescale_grade($attemptobj->get_sum_marks(), $quiz, false);

            if ($viewobj->gradecolumn) {
                if ($attemptoptions->marks >= question_display_options::MARK_AND_MAX &&
                        $attemptobj->is_finished()) {

                    // Highlight the highest grade if appropriate.
                    if ($viewobj->overallstats && !$attemptobj->is_preview()
                            && $viewobj->numattempts > 1 && !is_null($viewobj->mygrade)
                            && $attemptobj->get_state() == quiz_attempt::FINISHED
                            && $attemptgrade == $viewobj->mygrade
                            && $quiz->grademethod == QUIZ_GRADEHIGHEST) {
                        $table->rowclasses[$attemptobj->get_attempt_number()] = 'bestrow';
                    }

                    $row[] = quiz_format_grade($quiz, $attemptgrade);
                } else {
                    $row[] = '';
                }
            }

            if ($viewobj->canreviewmine) {
                $row[] = $viewobj->accessmanager->make_review_link($attemptobj->get_attempt(),
                        $attemptoptions, $this);
            }

            if ($viewobj->feedbackcolumn && $attemptobj->is_finished()) {
                if ($attemptoptions->overallfeedback) {
                    $row[] = quiz_feedback_for_grade($attemptgrade, $quiz, $context);
                } else {
                    $row[] = '';
                }
            }

            if ($attemptobj->is_finished()) {
                $filehash = '';
                if (($USER->id == $attemptobj->get_userid()) && !$attemptobj->is_preview()) {
                    $attemptid = $attemptobj->get_attemptid();
                    $quiz = $attemptobj->get_quiz();

                    $context = context_module::instance($attemptobj->get_cmid());
                    $component = 'quiz_concorsi';
                    $filearea = 'quiz_reviews';
                    $itemid = $quiz->id;

                    $idnumber = str_pad($USER->idnumber, 6, '0', STR_PAD_LEFT);
                    if ($quiz->attempts == 1) {
                        $filename = clean_param(fullname($USER) . '-' . $idnumber . '.pdf', PARAM_FILE);
                    } else {
                        $filename = clean_param(fullname($USER) . '-' . $idnumber . '-' . $attemptid . '.pdf', PARAM_FILE);
                    }

                    $fs = get_file_storage();
                    $file = $fs->get_file($context->id, $component, $filearea, $itemid, '/', $filename);
                    if (!empty($file)) {
                        $filehash = $file->get_contenthash();
                    }
                }

                $row[] = $filehash;
            }

            if ($attemptobj->is_preview()) {
                $table->data['preview'] = $row;
            } else {
                $table->data[$attemptobj->get_attempt_number()] = $row;
            }
        } // End of loop over attempts.

        $output = '';
        $output .= $this->view_table_heading();
        $output .= html_writer::table($table);
        return $output;
    }

    /**
     * Filters the summarydata array and anonymize user data and times.
     *
     * @param array $summarydata contains row data for table
     * @param int $page the current page number
     * @return $summarydata containing filtered row data
     */
    public function filter_review_summary_table($summarydata, $page) {
        if (isset($summarydata['user'])) {
            $idnumber = '';
            if (isset($summarydata['user']['title'])) {
                if (isset($summarydata['user']['title']->user->idnumber)) {
                    $idnumber = $summarydata['user']['title']->user->idnumber;
                }
                $summarydata['user']['title'] = get_string('idnumber');
            }
            if (isset($summarydata['user']['content']->text)) {
                $summarydata['user']['content'] = $idnumber;
            }
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

    /**
     * Returns the same as quiz_num_attempt_summary but wrapped in a link to the quiz reports.
     *
     * @param stdClass $quiz the quiz object. Only $quiz->id is used at the moment.
     * @param stdClass $cm the cm object. Only $cm->course, $cm->groupmode and $cm->groupingid fields are used at the moment.
     * @param context $context the quiz context.
     * @param bool $returnzero if false (default), when no attempts have been made '' is returned instead of 'Attempts: 0'.
     * @param int $currentgroup if there is a concept of current group where this method is being
     *      called (e.g. a report) pass it in here. Default 0 which means no current group.
     * @return string HTML fragment for the link.
     */
    public function quiz_attempt_summary_link_to_reports($quiz, $cm, $context,
                                                          $returnzero = false, $currentgroup = 0) {
        global $CFG;

        if (!empty($quiz->timeclose) && ($quiz->timeclose < time())) {
            $summary = quiz_num_attempt_summary($quiz, $cm, $returnzero, $currentgroup);
            if (!$summary) {
                return '';
            }

            require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');
            $url = new moodle_url('/mod/quiz/report.php', ['id' => $cm->id, 'mode' => quiz_report_default_report($context)]);
            return html_writer::link($url, $summary);
        }
        return '';
    }
}

/**
 * Question renderable.
 *
 * @package   theme_concorsi
 * @copyright 2023 UPO www.uniupo.it
 * @author    Roberto Pinna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
