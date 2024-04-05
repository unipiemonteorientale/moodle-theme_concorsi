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
     * @param mod_quizi\question\display_options $displayoptions instance of display_options.
     * @param array $summarydata contains all table data
     * @return $output containing html data.
     */
    public function review_page(mod_quiz\quiz_attempt $attemptobj, $slots, $page, $showall,
                                $lastpage, mod_quiz\question\display_options $displayoptions,
                                $summarydata) {
        global $CFG, $USER, $DB;

        $filehash = '';
        if (($USER->id == $attemptobj->get_userid()) && !$attemptobj->is_preview()) {
            $config = get_config('theme_concorsi');
            $attemptid = $attemptobj->get_attemptid();
            $quiz = $attemptobj->get_quiz();

            if (!isset($config->cryptkey)) {
                $digits = range(0, 9);
                shuffle($digits);
                $config->crypkey = implode(',', $digits);
                set_config('cryptkey', $config->crypkey, 'theme_concorsi');
            }

            if (isset($config->anonymizedates) && !empty($config->anonymizedates)) {
                $attempt = $DB->get_record('quiz_attempts', array('id' => $attemptid));
                if ($config->anonymizedates == 1) {
                    $attempt->timestart = 0;
                    $attempt->timefinish = 0;
                } else if ($config->anonymizedates == 2) {
                    $attempt->timestart = $quiz->timeopen;
                    $attempt->timefinish = $quiz->timeopen;
                }
                $DB->update_record('quiz_attempts', $attempt);
            }
            $context = context_module::instance($attemptobj->get_cmid());
            $component = 'quiz_concorsi';
            $filearea = 'quiz_reviews';
            $itemid = $attemptobj->get_quizid();
            $idnumber = str_pad($USER->idnumber, 6, '0', STR_PAD_LEFT);
            if ($quiz->attempts == 1) {
                $filename = clean_param(fullname($USER) . '-' . $idnumber . '.pdf', PARAM_FILE);
            } else {
                $filename = clean_param(fullname($USER) . '-' . $idnumber . '-' . $attemptid . '.pdf', PARAM_FILE);
            }

            $fs = get_file_storage();
            if (!$fs->file_exists($context->id, $component, $filearea, $itemid, '/', $filename)) {
                $slots = $attemptobj->get_slots();
                foreach ($slots as $slot) {
                    $originalslot = $attemptobj->get_original_slot($slot);
                    $number = $attemptobj->get_question_number($originalslot);

                    $qa = $attemptobj->get_question_attempt($slot);

                    $content .= html_writer::tag('h2', get_string('questionnumber', 'quiz_concorsi', $number));
                    $content .= html_writer::tag('pre', str_replace(['<', '>'], ['&lt;', '&gt;'],$qa->get_question_summary()));
                    $content .= html_writer::tag('h3', get_string('answer', 'quiz_concorsi'));
                    $content .= html_writer::tag('pre', str_replace(['<', '>'], ['&lt;', '&gt;'],$qa->get_response_summary()));
                    $content .= html_writer::empty_tag('hr', array());
                }

                $tempdir = make_temp_directory('core_plugin/quiz_concorsi') . '/';
                $filepath = $tempdir . $filename;

                require_once($CFG->libdir . '/pdflib.php');
                $doc = new pdf;
                $doc->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
                $userdata = ' - ' . get_string('idnumber') . ': ' . $USER->idnumber;
                if (isset($config->usernamehash) && !empty($config->usernamehash) && isset($config->cryptkey)) {
                    $userdata .= ' - '. sha1($config->cryptkey.$USER->username);
                }
                $doc->SetHeaderData(null, null, null, fullname($USER) . $userdata);
                $doc->SetFooterData(array(0, 0, 0), array(0, 0, 0));

                $doc->SetTopMargin(18);
                $doc->SetHeaderMargin(PDF_MARGIN_HEADER);
                $doc->SetFooterMargin(PDF_MARGIN_FOOTER);

                $doc->AddPage();
                $doc->writeHTML($content);
                $doc->lastPage();

                $doc->Output($filepath, 'F');

                $fileinfo = [
                    'contextid' => $context->id,
                    'component' => $component,
                    'filearea' => $filearea,
                    'itemid' => $itemid,
                    'filepath' => '/',
                    'filename' => $filename,
                ];

                $fs->create_file_from_pathname($fileinfo, $filepath);
            }

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
            $output .= html_writer::tag('div', get_string('filehash', 'theme_concorsi', $filehash), array('class' => 'filehash'));
        }

        $output .= $this->review_next_navigation($attemptobj, $page, $lastpage, $showall);
        $output .= $this->footer();

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
            if (isset($summarydata['user']['title']->link)) {
                $summarydata['user']['title']->link = '';
            }
            if (isset($summarydata['user']['content']->text)) {
                $summarydata['user']['content'] = $summarydata['user']['content']->text;
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
            $url = new moodle_url('/mod/quiz/report.php', array(
                    'id' => $cm->id, 'mode' => quiz_report_default_report($context)));
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
