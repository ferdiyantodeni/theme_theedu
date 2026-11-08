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
 * Dedicated Frontpage layout for theme_theedu.
 * Provides a modern, spacious landing portal (slider, categories, enrolled courses, search)
 * with architecture inspired by Eduardo Kraus's Boost Magnific theme.
 *
 * @package    theme_theedu
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$renderer = $PAGE->get_renderer('core');

// Add block button in editing mode.
$addblockbutton = $OUTPUT->addblockbutton();

// Frontpage blocks from side-pre region.
$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks = (strpos($blockshtml, 'data-block=') !== false || !empty($addblockbutton));

$bodyattributes = $OUTPUT->body_attributes(['theedu-frontpage-page']);

// Navigation Menus.
$secondarynavigation = false;
$overflow = '';
if ($PAGE->has_secondary_navigation()) {
    $moremenu = new \core\navigation\output\more_menu($PAGE->secondarynav, 'nav-tabs', true, true);
    $secondarynavigation = $moremenu->export_for_template($OUTPUT);
    $overflow = $OUTPUT->page_heading_menu();
}

$primary = new core\navigation\output\primary($PAGE);
$primarymenu = $primary->export_for_template($renderer);

$buildregionmainsettings = !$PAGE->include_region_main_settings_in_header_actions() &&
        !$PAGE->has_secondary_navigation();
if ($buildregionmainsettings) {
    $buildregionmainsettings = $OUTPUT->region_main_settings_menu();
}

$headercontent = false;

// Frontpage Moodle core content control (Hide duplicate unstyled core listings when editing is off).
$hidemoodlecontent = (bool) theme_theedu_get_setting('frontpage_hide_moodle_content', 1);
$isediting = $PAGE->user_is_editing();
$showmaincontent = !$hidemoodlecontent || $isediting;

$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'sidepreblocks' => $blockshtml,
    'hasblocks' => $hasblocks,
    'show_main_content' => $showmaincontent,
    'bodyattributes' => $bodyattributes,
    'primarymoremenu' => $primarymenu['moremenu'],
    'secondarymoremenu' => $secondarynavigation,
    'mobileprimarynav' => $primarymenu['mobileprimarynav'],
    'usermenu' => $primarymenu['user'],
    'langmenu' => $primarymenu['lang'],
    'regionmainsettingsmenu' => $buildregionmainsettings,
    'hasregionmainsettingsmenu' => !empty($buildregionmainsettings),
    'overflow' => $overflow,
    'headercontent' => $headercontent,
    'addblockbutton' => $addblockbutton,
];

// Enrich with TheEdu Theme custom data (4 logos, slider, pastel colors, dark mode, footer).
$theeducontext = theme_theedu_get_template_context($PAGE, true);
$templatecontext = array_merge($templatecontext, $theeducontext);

// Enqueue Google Font safely without crashing Moodle SCSS compiler.
if (!empty($theeducontext['theedu_font_url'])) {
    $PAGE->requires->css(new \moodle_url($theeducontext['theedu_font_url']));
}

// Initialize Dark Mode AMD script.
$PAGE->requires->js_call_amd('theme_theedu/darkmode', 'init', [$theeducontext['theedu_darkmode']['default']]);

echo $OUTPUT->render_from_template('theme_theedu/frontpage', $templatecontext);
