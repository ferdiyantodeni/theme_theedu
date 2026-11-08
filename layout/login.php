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
 * Login layout for theme_theedu.
 *
 * @package    theme_theedu
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$bodyattributes = $OUTPUT->body_attributes(['pagelayout-login']);

$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'bodyattributes' => $bodyattributes,
    'loginsitefullname' => format_string($SITE->fullname),
];

$theeducontext = theme_theedu_get_template_context($PAGE);
$templatecontext = array_merge($templatecontext, $theeducontext);

// Enqueue Google Font safely without crashing Moodle SCSS compiler.
if (!empty($theeducontext['theedu_font_url'])) {
    $PAGE->requires->css(new \moodle_url($theeducontext['theedu_font_url']));
}

// Initialize Dark Mode script on login page as well.
$PAGE->requires->js_call_amd('theme_theedu/darkmode', 'init', [$theeducontext['theedu_darkmode']['default']]);

echo $OUTPUT->render_from_template('theme_theedu/login', $templatecontext);
