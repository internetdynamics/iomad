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
 * @package   block_mycourses
 * @copyright 2021 Derick Turner
 * @author    Derick Turner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_mycourses\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;
use core_completion\progress;
use core_course_renderer;
use moodle_url;

require_once($CFG->dirroot . '/blocks/mycourses/locallib.php');
require_once($CFG->libdir . '/completionlib.php');

/**
 * Class containing data for my overview block.
 *
 * @copyright  2017 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main implements renderable, templatable {

    /**
     * @var string The tab to display.
     */
    public $tab;

    /**
     * Constructor.
     *
     * @param string $tab The tab to display.
     */
    public function __construct($tab) {
        $this->tab = $tab;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $CFG, $USER, $PAGE;

        // Get the sorting params.
        // $sort = optional_param('sort', 'coursefullname', PARAM_CLEAN);

        $sort_name = optional_param('sort', '', PARAM_CLEAN);

        $dir = "ASC";
        $sort = "coursefullname";

        if ($sort_name == "coursefullname-ASC") {
          $sort = "coursefullname";
          $dir = "ASC";
        }
        else if ($sort_name == "coursefullname-DESC") {
          $sort = "coursefullname";
          $dir = "DESC";
        }
        else if ($sort_name == "timestarted-ASC") {
          $sort = "timestarted";
          $dir = "ASC";
        }
        else if ($sort_name == "timestarted-DESC") {
          $sort = "timestarted";
          $dir = "DESC";
        }

        // $dir = optional_param('dir', 'ASC', PARAM_CLEAN);
        $tab = optional_param('tab', 'inprogress#mycourses_inprogress_view', PARAM_CLEAN);
        $view = optional_param('view', $CFG->mycourses_defaultview, PARAM_CLEAN);
        $shown = optional_param('shown', 'own', PARAM_CLEAN); // SEB

        // SEB
        $shownBtnNames = array(
          'own'  => 'All Own Courses',
          'private-shared' => 'Private & Shared',
          'private-shared-public'  => 'Private, Shared, Pubic',
          'private'  => 'Private Only',
          'shared'  => 'Shared Only',
          'public'  => 'Public Only',
          'hidden'  => 'Hidden',
        );

        $shownBtnName = $shownBtnNames[$shown];

        // Get the completion info.
        $mycompletion = mycourses_get_my_completion($sort, $dir, $shown); // SEB
        $myarchive = mycourses_get_my_archive($sort, $dir);

        $availableview = new available_view($mycompletion);
        $inprogressview = new inprogress_view($mycompletion);
        $completedview = new completed_view($myarchive);

        // Now, set the tab we are going to be viewing.
        $viewingavailable = false;
        $viewinginprogress = false;
        $viewingcompleted = false;
        if ($this->tab == 'available') {
            $viewingavailable = true;
        } else if ($this->tab == 'completed') {
            $viewingcompleted = true;
        } else {
            $viewinginprogress = true;
        }

        // SEB
        $nocoursesurl = $output->image_url('courses', 'block_mycourses')->out();
        $sortnameurlasc = new moodle_url($PAGE->url->out(false), ['sort' => 'coursefullname-ASC', 'dir' => 'ASC', 'tab' => $this->tab, 'view' => $view, 'shown' => $shown]);
        $sortnameurldesc = new moodle_url($PAGE->url->out(false), ['sort' => 'coursefullname-DESC', 'dir' => 'DESC', 'tab' => $this->tab, 'view' => $view, 'shown' => $shown]);
        $sortdateurlasc = new moodle_url($PAGE->url->out(false), ['sort' => 'timestarted-ASC', 'dir' => 'ASC', 'tab' => $this->tab, 'view' => $view, 'shown' => $shown]);
        $sortdateurldesc = new moodle_url($PAGE->url->out(false), ['sort' => 'timestarted-DESC', 'dir' => 'DESC', 'tab' => $this->tab, 'view' => $view, 'shown' => $shown]);
        // $sortascurl = new moodle_url($PAGE->url->out(false), ['sort' => $sort, 'dir' => 'ASC', 'tab' => $this->tab, 'view' => $view, 'shown' => $shown]);
        // $sortdescurl = new moodle_url($PAGE->url->out(false), ['sort' => $sort, 'dir' => 'DESC', 'tab' => $this->tab, 'view' => $view, 'shown' => $shown]);
        $listviewurl = new moodle_url($PAGE->url->out(false), ['sort' => $sort, 'dir' => $dir, 'tab' => $this->tab, 'view' => 'list', 'shown' => $shown]);
        $cardviewurl = new moodle_url($PAGE->url->out(false), ['sort' => $sort, 'dir' => $dir, 'tab' => $this->tab, 'view' => 'card', 'shown' => $shown]);

        // SEB
        $shownOwnUrl = new moodle_url($PAGE->url->out(false), ['sort' => $sort, 'dir' => $dir,
          'tab' => $this->tab, 'view' => $view, 'shown' => 'own']);

        $shownprivatesharedurl = new moodle_url($PAGE->url->out(false), ['sort' => $sort, 'dir' => $dir,
          'tab' => $this->tab, 'view' => $view, 'shown' => 'private-shared']);

        $shownPrivateSharedPublicUrl = new moodle_url($PAGE->url->out(false), ['sort' => $sort, 'dir' => $dir,
          'tab' => $this->tab, 'view' => $view, 'shown' => 'private-shared-public']);

        $shownPrivateUrl = new moodle_url($PAGE->url->out(false), ['sort' => $sort, 'dir' => $dir,
          'tab' => $this->tab, 'view' => $view, 'shown' => 'private']);

        $shownSharedUrl = new moodle_url($PAGE->url->out(false), ['sort' => $sort, 'dir' => $dir,
          'tab' => $this->tab, 'view' => $view, 'shown' => 'shared']);

        $shownPublicUrl = new moodle_url($PAGE->url->out(false), ['sort' => $sort, 'dir' => $dir,
          'tab' => $this->tab, 'view' => $view, 'shown' => 'public']);

        $viewlist = false;
        $viewcard = false;
        if ($view == 'list') {
            $viewlist = true;
        }
        if ($view == 'card') {
            $viewcard = true;
        }

        return [
            'midnight' => usergetmidnight(time()),
            'nocourses' => $nocoursesurl,
            'availableview' => $availableview->export_for_template($output),
            'inprogressview' => $inprogressview->export_for_template($output),
            'completedview' => $completedview->export_for_template($output),
            'viewingavailable' => $viewingavailable,
            'viewinginprogress' => $viewinginprogress,
            'viewingcompleted' => $viewingcompleted,
            'sortnameurlasc' => $sortnameurlasc->out(false),
            'sortnameurldesc' => $sortnameurldesc->out(false),
            'sortdateurlasc' => $sortdateurlasc->out(false),
            'sortdateurldesc' => $sortdateurldesc->out(false),
            // 'sortascurl' => $sortascurl->out(false),
            // 'sortdescurl' => $sortdescurl->out(false),
            'listviewurl' => $listviewurl->out(false),
            'cardviewurl' => $cardviewurl->out(false),
            'viewlist' => $viewlist,
            'viewcard' => $viewcard,
            'shownBtnName' => $shownBtnName, // SEB
            'shownOwnUrl' => $shownOwnUrl->out(false),
            'shownprivatesharedurl' => $shownprivatesharedurl->out(false),
            'shownPrivateSharedPublicUrl' => $shownPrivateSharedPublicUrl->out(false),
            'shownPrivateUrl' => $shownPrivateUrl->out(false),
            'shownSharedUrl' => $shownSharedUrl->out(false),
            'shownPublicUrl' => $shownPublicUrl->out(false),
        ];
    }
}
