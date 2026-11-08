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
 * Drawers layout for theme_theedu (compatible with Moodle 4.4 to 5.3+).
 *
 * @package    theme_theedu
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Add block button in editing mode.
$addblockbutton = $OUTPUT->addblockbutton();

// Support legacy user preference ajax update safely if available in earlier Moodle versions.
if (function_exists('user_preference_allow_ajax_update')) {
    user_preference_allow_ajax_update('drawer-open-nav', PARAM_BOOL);
    user_preference_allow_ajax_update('drawer-open-index', PARAM_BOOL);
    user_preference_allow_ajax_update('drawer-open-block', PARAM_BOOL);
}

$hasprimarynav = $PAGE->has_secondary_navigation();

if (isloggedin()) {
    $courseindexopen = (get_user_preferences('drawer-open-index', true) == true);
    $blockdraweropen = (get_user_preferences('drawer-open-block') == true);
} else {
    $courseindexopen = false;
    $blockdraweropen = false;
}

if (defined('BEHAT_SITE_RUNNING')) {
    $blockdraweropen = false;
    $courseindexopen = false;
}

$extraclasses = ['uses-drawers'];

$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks = (strpos($blockshtml, 'data-block=') !== false || !empty($addblockbutton));
if (!$hasblocks) {
    $blockdraweropen = false;
}

$courseindex = core_course_drawer();
if (!$courseindex) {
    $courseindexopen = false;
}

$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$forceblockdraweropen = false;
if (method_exists($OUTPUT, 'firstview_fakeblocks')) {
    $forceblockdraweropen = $OUTPUT->firstview_fakeblocks();
}
if ($forceblockdraweropen) {
    $blockdraweropen = true;
}

$secondarynavigation = false;
$overflow = '';
if ($PAGE->has_secondary_navigation()) {
    $tablistnav = method_exists($PAGE, 'has_tablist_secondary_navigation') ? $PAGE->has_tablist_secondary_navigation() : true;
    $moremenu = new \core\navigation\output\more_menu($PAGE->secondarynav, 'nav-tabs', true, $tablistnav);
    $secondarynavigation = $moremenu->export_for_template($OUTPUT);
    if (method_exists($PAGE->secondarynav, 'get_overflow_menu_data')) {
        $overflowdata = $PAGE->secondarynav->get_overflow_menu_data();
        if (!is_null($overflowdata)) {
            $overflow = $overflowdata->export_for_template($OUTPUT);
        }
    } else if (method_exists($OUTPUT, 'page_heading_menu')) {
        $overflow = $OUTPUT->page_heading_menu();
    }
}

$primary = new core\navigation\output\primary($PAGE);
$renderer = $PAGE->get_renderer('core');
$primarymenu = $primary->export_for_template($renderer);

// Optional header primary navbar suppression for guest (non-login) users.
$isloggedinuser = isloggedin() && !isguestuser();
$navbarguestvis = theme_theedu_get_setting('navbar_guest_visibility', 'show');
if (!$isloggedinuser && $navbarguestvis === 'hide') {
    $primarymenu['moremenu'] = false;
    $primarymenu['mobileprimarynav'] = false;
}
$buildregionmainsettings = !$PAGE->include_region_main_settings_in_header_actions() &&
        !$PAGE->has_secondary_navigation();
// If the settings menu will be included in the header actions, display it there.
if ($buildregionmainsettings) {
    $buildregionmainsettings = $OUTPUT->region_main_settings_menu();
}

$headercontent = false;
if ($PAGE->pagelayout === 'incourse' && !empty($PAGE->activityheader)) {
    $headercontent = $PAGE->activityheader->export_for_template($renderer);
}

$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'sidepreblocks' => $blockshtml,
    'hasblocks' => $hasblocks,
    'bodyattributes' => $bodyattributes,
    'courseindexopen' => $courseindexopen,
    'blockdraweropen' => $blockdraweropen,
    'courseindex' => $courseindex,
    'primarymoremenu' => !empty($primarymenu['moremenu']) ? $primarymenu['moremenu'] : false,
    'secondarymoremenu' => $secondarynavigation,
    'secondarynavigation' => $secondarynavigation,
    'mobileprimarynav' => $primarymenu['mobileprimarynav'],
    'usermenu' => $primarymenu['user'],
    'langmenu' => $primarymenu['lang'],
    'forceblockdraweropen' => $forceblockdraweropen,
    'regionmainsettingsmenu' => $buildregionmainsettings,
    'hasregionmainsettingsmenu' => !empty($buildregionmainsettings),
    'overflow' => $overflow,
    'headercontent' => $headercontent,
    'addblockbutton' => $addblockbutton,
];

// Enrich with TheEdu Theme custom data (4 logos, slider, pastel colors, dark mode, footer).
$theeducontext = theme_theedu_get_template_context($PAGE);
$templatecontext = array_merge($templatecontext, $theeducontext);

// Enqueue Google Font safely without crashing Moodle SCSS compiler.
if (!empty($theeducontext['theedu_font_url'])) {
    $PAGE->requires->css(new \moodle_url($theeducontext['theedu_font_url']));
}

// Initialize Dark Mode AMD script.
$PAGE->requires->js_call_amd('theme_theedu/darkmode', 'init', [$theeducontext['theedu_darkmode']['default']]);

echo $OUTPUT->render_from_template('theme_theedu/drawers', $templatecontext);
