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
 * Configuration for theme_theedu.
 *
 * @package    theme_theedu
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$THEME->name = 'theedu';
$THEME->sheets = [];
$THEME->editor_sheets = [];
$THEME->parents = ['boost'];
$THEME->enable_dock = false;
$THEME->yuicssmodules = [];

$THEME->rendererfactory = 'theme_overridden_renderer_factory';
$THEME->iconsystem = \core\output\icon_system::FONTAWESOME;
$THEME->page_init = 'theme_theedu_page_init';

// SCSS callbacks.
$THEME->scss = function($theme) {
    return theme_theedu_get_main_scss_content($theme);
};
$THEME->prescsscallback = 'theme_theedu_get_pre_scss';
$THEME->extrascsscallback = 'theme_theedu_get_extra_scss';

// Layout definitions compatible with Moodle 4.4 through 5.3.
$THEME->layouts = [
    // Most general pages use the primary drawers layout.
    'base' => [
        'file' => 'drawers.php',
        'regions' => [],
        'defaultregion' => '',
    ],
    'standard' => [
        'file' => 'drawers.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'course' => [
        'file' => 'drawers.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'coursecategory' => [
        'file' => 'drawers.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'incourse' => [
        'file' => 'drawers.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'frontpage' => [
        'file' => 'frontpage.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre',
        'options' => ['nonavbar' => false],
    ],
    'admin' => [
        'file' => 'drawers.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'login' => [
        'file' => 'login.php',
        'regions' => [],
        'defaultregion' => '',
        'options' => ['langmenu' => true],
    ],
    'mydashboard' => [
        'file' => 'drawers.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'mypublic' => [
        'file' => 'drawers.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'embedded' => [
        'theme' => 'boost',
        'file' => 'embedded.php',
        'regions' => [],
        'defaultregion' => '',
    ],
    'maintenance' => [
        'theme' => 'boost',
        'file' => 'maintenance.php',
        'regions' => [],
        'defaultregion' => '',
    ],
    'popup' => [
        'theme' => 'boost',
        'file' => 'popup.php',
        'regions' => [],
        'defaultregion' => '',
    ],
    'print' => [
        'theme' => 'boost',
        'file' => 'print.php',
        'regions' => [],
        'defaultregion' => '',
    ],
    'redirect' => [
        'theme' => 'boost',
        'file' => 'redirect.php',
        'regions' => [],
        'defaultregion' => '',
    ],
    'report' => [
        'file' => 'drawers.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'secure' => [
        'file' => 'secure.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
];

// Presets available.
$THEME->presettourfiles = ['theme_boost/welcome'];
