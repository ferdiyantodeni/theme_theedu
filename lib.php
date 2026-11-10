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
 * Library functions for theme_theedu.
 *
 * @package    theme_theedu
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Early page initialization hook for theme_theedu.
 * Handles dynamic language switching across sessions and user preferences.
 *
 * @param \moodle_page $page
 */
function theme_theedu_page_init(\moodle_page $page) {
    global $SESSION, $USER;

    $switchlang = optional_param('lang', '', PARAM_SAFEDIR);
    if (!empty($switchlang) && in_array($switchlang, ['en', 'id', 'id_id', 'en_us'])) {
        $normalizedlang = (strpos($switchlang, 'id') === 0) ? 'id' : 'en';
        $SESSION->lang = $normalizedlang;

        if (isloggedin() && !isguestuser()) {
            $USER->lang = $normalizedlang;
            if (function_exists('set_user_preference')) {
                set_user_preference('current_language', $normalizedlang);
            }
        }
    }
}

/**
 * Serves theme files (logos, slider images, etc.) from theme settings.
 *
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param context $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just exits
 */
function theme_theedu_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }

    $validfileareas = [
        'logo_top_left',
        'logo_top_right',
        'logo_bottom_left',
        'logo_bottom_right',
        'favicon',
        'login_bg_image',
        'slide_image_1',
        'slide_image_2',
        'slide_image_3',
        'slide_image_4',
        'slide_image_5',
    ];

    if (!in_array($filearea, $validfileareas)) {
        return false;
    }

    // Safely extract itemid and filename from $args.
    if (!empty($args) && is_numeric($args[0])) {
        $itemid = (int)array_shift($args);
    } else {
        $itemid = 0;
    }
    $filename = array_pop($args);
    $filepath = $args ? '/' . implode('/', $args) . '/' : '/';

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'theme_theedu', $filearea, 0, '/', $filename);
    if (!$file) {
        $file = $fs->get_file($context->id, 'theme_theedu', $filearea, $itemid, $filepath, $filename);
    }

    if (!$file || $file->is_directory()) {
        return false;
    }

    \core\session\manager::write_close();
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}

/**
 * Get setting value with fallback.
 *
 * @param string $name
 * @param mixed $default
 * @return mixed
 */
function theme_theedu_get_setting($name, $default = '') {
    $val = get_config('theme_theedu', $name);
    return ($val !== false && $val !== null && $val !== '') ? $val : $default;
}

/**
 * Get URL of stored file in theme setting with automatic fallback to bundled assets or site logo.
 *
 * @param string $filearea
 * @return string|null
 */
function theme_theedu_get_file_url($filearea) {
    global $CFG, $OUTPUT;
    $syscontext = \context_system::instance();
    $fs = get_file_storage();
    $files = $fs->get_area_files($syscontext->id, 'theme_theedu', $filearea, false, 'itemid, filepath, filename', false);

    if ($files) {
        $file = reset($files);
        return \moodle_url::make_pluginfile_url(
            $syscontext->id,
            'theme_theedu',
            $filearea,
            $file->get_itemid(),
            '/',
            $file->get_filename()
        )->out(false);
    }

    // Direct URL setting fallback for login background.
    if ($filearea === 'login_bg_image') {
        $bgurl = theme_theedu_get_setting('login_bg_url', '');
        if (!empty($bgurl)) {
            return $bgurl;
        }
    }

    // Reliable fallback for logo: use Moodle core logo or bundled theme pix/logo.png
    if ($filearea === 'logo_top_left' || $filearea === 'logo_bottom_left') {
        if (!empty($OUTPUT)) {
            if (method_exists($OUTPUT, 'should_display_navbar_logo') && $OUTPUT->should_display_navbar_logo()) {
                $compact = $OUTPUT->get_compact_logo_url();
                if (!empty($compact)) {
                    return $compact->out(false);
                }
                $logo = $OUTPUT->get_logo_url();
                if (!empty($logo)) {
                    return $logo->out(false);
                }
            }
            if (method_exists($OUTPUT, 'image_url')) {
                return $OUTPUT->image_url('logo', 'theme_theedu')->out(false);
            }
        }
        return $CFG->wwwroot . '/theme/theedu/pix/logo.png';
    }

    return null;
}

/**
 * Get main SCSS content.
 *
 * @param theme_config $theme
 * @return string
 */
function theme_theedu_get_main_scss_content($theme) {
    global $CFG;
    $scss = '';

    // 1. Load Boost's full core SCSS preset (Bootstrap 5 + FontAwesome + Core Moodle)
    if (function_exists('theme_boost_get_main_scss_content')) {
        $scss .= theme_boost_get_main_scss_content($theme) . "\n";
    } else {
        $boostpreset = $CFG->dirroot . '/theme/boost/scss/preset/default.scss';
        if (file_exists($boostpreset)) {
            $scss .= file_get_contents($boostpreset) . "\n";
        }
    }

    $themedir = $CFG->dirroot . '/theme/theedu';

    // 2. Load bundled offline fonts
    $fontsfile = $themedir . '/scss/_fonts.scss';
    if (file_exists($fontsfile)) {
        $scss .= file_get_contents($fontsfile) . "\n";
    }

    // 3. Load TheEdu custom SCSS components
    $subfiles = [
        $themedir . '/scss/_variables.scss',
        $themedir . '/scss/_darkmode.scss',
        $themedir . '/scss/_header.scss',
        $themedir . '/scss/_course.scss',
        $themedir . '/scss/_slider.scss',
        $themedir . '/scss/_footer.scss',
        $themedir . '/scss/post.scss',
    ];

    foreach ($subfiles as $file) {
        if (file_exists($file)) {
            $scss .= file_get_contents($file) . "\n";
        }
    }

    return $scss;
}

/**
 * Returns all predefined pastel brand presets.
 * Supports soft, harmonious, accessible pastel palettes for 7 themes:
 * - default (Lavender & Teal)
 * - red (Crimson & Rose - matches corporate brand like Daihatsu)
 * - blue (Ocean & Sky Blue)
 * - green (Mint & Emerald)
 * - white (Clean Minimalist & Slate)
 * - grey (Sophisticated Cool Slate)
 * - yellow (Warm Amber & Sunflower)
 *
 * @return array
 */
function theme_theedu_get_presets_palette() {
    return [
        'default' => [
            'primary' => '#6C5CE7',
            'secondary' => '#00CEC9',
            'accent' => '#FF7675',
            'navbar_bg' => '#FFFFFF',
            'navbar_text' => '#2D3436',
            'body_bg' => '#F7F9FC',
            'card_bg' => '#FFFFFF',
            'footer_bg' => '#2D3436',
            'footer_text' => '#DFE6E9',
            'dark_primary' => '#8E7CFF',
        ],
        'red' => [
            'primary' => '#E63946',
            'secondary' => '#FF7675',
            'accent' => '#FD79A8',
            'navbar_bg' => '#FFFFFF',
            'navbar_text' => '#2C3E50',
            'body_bg' => '#FFF5F5',
            'card_bg' => '#FFFFFF',
            'footer_bg' => '#2B2326',
            'footer_text' => '#FCEBED',
            'dark_primary' => '#FF6B6B',
        ],
        'blue' => [
            'primary' => '#2E86DE',
            'secondary' => '#54A0FF',
            'accent' => '#00D2D3',
            'navbar_bg' => '#FFFFFF',
            'navbar_text' => '#1E272E',
            'body_bg' => '#F0F5FA',
            'card_bg' => '#FFFFFF',
            'footer_bg' => '#1E293B',
            'footer_text' => '#E2E8F0',
            'dark_primary' => '#48DBFB',
        ],
        'green' => [
            'primary' => '#10AC84',
            'secondary' => '#1DD1A1',
            'accent' => '#10B981',
            'navbar_bg' => '#FFFFFF',
            'navbar_text' => '#222F3E',
            'body_bg' => '#F0FDF4',
            'card_bg' => '#FFFFFF',
            'footer_bg' => '#1A2F2B',
            'footer_text' => '#E6F4EA',
            'dark_primary' => '#2ED573',
        ],
        'white' => [
            'primary' => '#3B82F6',
            'secondary' => '#60A5FA',
            'accent' => '#93C5FD',
            'navbar_bg' => '#FFFFFF',
            'navbar_text' => '#0F172A',
            'body_bg' => '#FAFAFA',
            'card_bg' => '#FFFFFF',
            'footer_bg' => '#334155',
            'footer_text' => '#F8FAFC',
            'dark_primary' => '#60A5FA',
        ],
        'grey' => [
            'primary' => '#475569',
            'secondary' => '#64748B',
            'accent' => '#94A3B8',
            'navbar_bg' => '#FFFFFF',
            'navbar_text' => '#1E293B',
            'body_bg' => '#F8FAFC',
            'card_bg' => '#FFFFFF',
            'footer_bg' => '#1E293B',
            'footer_text' => '#F1F5F9',
            'dark_primary' => '#94A3B8',
        ],
        'yellow' => [
            'primary' => '#D97706',
            'secondary' => '#F59E0B',
            'accent' => '#FBBF24',
            'navbar_bg' => '#FFFFFF',
            'navbar_text' => '#2D3436',
            'body_bg' => '#FFFDF5',
            'card_bg' => '#FFFFFF',
            'footer_bg' => '#2D271E',
            'footer_text' => '#FEF3C7',
            'dark_primary' => '#FBBF24',
        ],
    ];
}

/**
 * Returns effective color for a given key, resolving brand preset or custom color picker.
 *
 * @param string $key e.g. 'primary', 'secondary', 'navbar_bg'
 * @param string $default Fallback hex code
 * @return string
 */
function theme_theedu_get_color($key, $default = '') {
    $custom = theme_theedu_get_setting('color_' . $key, '');
    $preset = theme_theedu_get_setting('brand_preset', 'default');
    $palettes = theme_theedu_get_presets_palette();

    // 1. If brand_preset is explicitly set to 'custom', always use the setting value.
    if ($preset === 'custom') {
        return !empty($custom) ? $custom : $default;
    }

    // 2. Resolve default value from the active preset palette.
    $presetcolor = isset($palettes[$preset][$key]) ? $palettes[$preset][$key] : $default;

    // 3. For any color setting (e.g. color_navbar_bg, color_navbar_text, color_body_bg, etc.):
    // If the administrator has explicitly customized this color in Site Administration
    // (differing from the preset palette default), immediately honor their custom choice.
    if (!empty($custom) && strcasecmp($custom, $presetcolor) !== 0) {
        return $custom;
    }

    return $presetcolor;
}

/**
 * Returns available design presets for Secondary Navigation & Tabs.
 *
 * @return array
 */
function theme_theedu_get_secnav_presets_list() {
    return [
        'default' => [
            'name'            => 'Modern Pill (TheEdu Purple Gradient)',
            'radius'          => '2rem',
            'padding'         => '0.45rem 1.15rem',
            'fontsize'        => '0.9rem',
            'gap'             => '0.4rem',
            'border_width'    => '1px',
            'btn_bg'          => '#FFFFFF',
            'btn_text'        => '#2D3436',
            'btn_border'      => 'rgba(108, 92, 231, 0.15)',
            'hover_bg'        => 'rgba(108, 92, 231, 0.08)',
            'hover_text'      => '#6C5CE7',
            'hover_border'    => '#6C5CE7',
            'active_bg'       => 'linear-gradient(135deg, #6C5CE7 0%, #7F71EB 100%)',
            'active_text'     => '#FFFFFF',
            'active_border'   => '#6C5CE7',
            'active_shadow'   => '0 4px 14px rgba(108, 92, 231, 0.35)',
            'bar_bg'          => 'transparent',
            'bar_padding'     => '0',
            'bar_radius'      => '0px',
            'align'           => 'flex-start',
            'dark_btn_bg'     => '#1E2638',
            'dark_btn_text'   => '#CBD5E1',
            'dark_btn_border' => 'rgba(255, 255, 255, 0.1)',
            'dark_hover_bg'   => 'rgba(255, 255, 255, 0.08)',
            'dark_hover_text' => '#FFFFFF',
            'dark_active_bg'  => 'linear-gradient(135deg, #8E7CFF 0%, #A29BFE 100%)',
            'dark_active_text'=> '#121824',
        ],
        'soft_rounded' => [
            'name'            => 'Soft Rounded (Indigo 8px)',
            'radius'          => '8px',
            'padding'         => '0.45rem 1.15rem',
            'fontsize'        => '0.9rem',
            'gap'             => '0.4rem',
            'border_width'    => '1px',
            'btn_bg'          => '#FFFFFF',
            'btn_text'        => '#334155',
            'btn_border'      => '#E2E8F0',
            'hover_bg'        => '#F1F5F9',
            'hover_text'      => '#1E293B',
            'hover_border'    => '#CBD5E1',
            'active_bg'       => 'linear-gradient(135deg, #4F46E5 0%, #6366F1 100%)',
            'active_text'     => '#FFFFFF',
            'active_border'   => '#4F46E5',
            'active_shadow'   => '0 4px 12px rgba(79, 70, 229, 0.3)',
            'bar_bg'          => 'transparent',
            'bar_padding'     => '0',
            'bar_radius'      => '0px',
            'align'           => 'flex-start',
            'dark_btn_bg'     => '#1E293B',
            'dark_btn_text'   => '#E2E8F0',
            'dark_btn_border' => '#334155',
            'dark_hover_bg'   => '#334155',
            'dark_hover_text' => '#FFFFFF',
            'dark_active_bg'  => '#6366F1',
            'dark_active_text'=> '#FFFFFF',
        ],
        'minimal_flat' => [
            'name'            => 'Minimal Clean (Dark Slate 6px)',
            'radius'          => '6px',
            'padding'         => '0.45rem 1rem',
            'fontsize'        => '0.9rem',
            'gap'             => '0.35rem',
            'border_width'    => '1px',
            'btn_bg'          => 'transparent',
            'btn_text'        => '#64748B',
            'btn_border'      => 'transparent',
            'hover_bg'        => '#F1F5F9',
            'hover_text'      => '#0F172A',
            'hover_border'    => 'transparent',
            'active_bg'       => '#0F172A',
            'active_text'     => '#FFFFFF',
            'active_border'   => '#0F172A',
            'active_shadow'   => 'none',
            'bar_bg'          => 'transparent',
            'bar_padding'     => '0',
            'bar_radius'      => '0px',
            'align'           => 'flex-start',
            'dark_btn_bg'     => 'transparent',
            'dark_btn_text'   => '#94A3B8',
            'dark_btn_border' => 'transparent',
            'dark_hover_bg'   => '#1E293B',
            'dark_hover_text' => '#FFFFFF',
            'dark_active_bg'  => '#F8FAFC',
            'dark_active_text'=> '#0F172A',
        ],
        'outline_ghost' => [
            'name'            => 'Ghost Outline (Bordered Pill)',
            'radius'          => '2rem',
            'padding'         => '0.45rem 1.15rem',
            'fontsize'        => '0.9rem',
            'gap'             => '0.4rem',
            'border_width'    => '1.5px',
            'btn_bg'          => 'transparent',
            'btn_text'        => '#475569',
            'btn_border'      => '#CBD5E1',
            'hover_bg'        => 'rgba(108, 92, 231, 0.06)',
            'hover_text'      => '#6C5CE7',
            'hover_border'    => '#6C5CE7',
            'active_bg'       => '#6C5CE7',
            'active_text'     => '#FFFFFF',
            'active_border'   => '#6C5CE7',
            'active_shadow'   => '0 3px 10px rgba(108, 92, 231, 0.25)',
            'bar_bg'          => 'transparent',
            'bar_padding'     => '0',
            'bar_radius'      => '0px',
            'align'           => 'flex-start',
            'dark_btn_bg'     => 'transparent',
            'dark_btn_text'   => '#CBD5E1',
            'dark_btn_border' => '#475569',
            'dark_hover_bg'   => 'rgba(142, 124, 255, 0.12)',
            'dark_hover_text' => '#8E7CFF',
            'dark_active_bg'  => '#8E7CFF',
            'dark_active_text'=> '#121824',
        ],
        'gradient_emerald' => [
            'name'            => 'Fresh Emerald (Teal Gradient)',
            'radius'          => '2rem',
            'padding'         => '0.45rem 1.15rem',
            'fontsize'        => '0.9rem',
            'gap'             => '0.4rem',
            'border_width'    => '1px',
            'btn_bg'          => '#FFFFFF',
            'btn_text'        => '#1E293B',
            'btn_border'      => 'rgba(16, 185, 129, 0.25)',
            'hover_bg'        => 'rgba(16, 185, 129, 0.1)',
            'hover_text'      => '#059669',
            'hover_border'    => '#10B981',
            'active_bg'       => 'linear-gradient(135deg, #059669 0%, #10B981 100%)',
            'active_text'     => '#FFFFFF',
            'active_border'   => '#059669',
            'active_shadow'   => '0 4px 14px rgba(16, 185, 129, 0.35)',
            'bar_bg'          => 'transparent',
            'bar_padding'     => '0',
            'bar_radius'      => '0px',
            'align'           => 'flex-start',
            'dark_btn_bg'     => '#1E2638',
            'dark_btn_text'   => '#A7F3D0',
            'dark_btn_border' => 'rgba(16, 185, 129, 0.3)',
            'dark_hover_bg'   => 'rgba(16, 185, 129, 0.15)',
            'dark_hover_text' => '#34D399',
            'dark_active_bg'  => '#10B981',
            'dark_active_text'=> '#064E3B',
        ],
        'corporate_blue' => [
            'name'            => 'Executive Blue (Ocean Gradient 8px)',
            'radius'          => '8px',
            'padding'         => '0.45rem 1.15rem',
            'fontsize'        => '0.9rem',
            'gap'             => '0.4rem',
            'border_width'    => '1px',
            'btn_bg'          => '#FFFFFF',
            'btn_text'        => '#1E3A8A',
            'btn_border'      => '#DBEAFE',
            'hover_bg'        => '#EFF6FF',
            'hover_text'      => '#1D4ED8',
            'hover_border'    => '#3B82F6',
            'active_bg'       => 'linear-gradient(135deg, #1E40AF 0%, #3B82F6 100%)',
            'active_text'     => '#FFFFFF',
            'active_border'   => '#1E40AF',
            'active_shadow'   => '0 4px 12px rgba(30, 64, 175, 0.3)',
            'bar_bg'          => 'transparent',
            'bar_padding'     => '0',
            'bar_radius'      => '0px',
            'align'           => 'flex-start',
            'dark_btn_bg'     => '#1E293B',
            'dark_btn_text'   => '#BFDBFE',
            'dark_btn_border' => '#2563EB',
            'dark_hover_bg'   => 'rgba(59, 130, 246, 0.15)',
            'dark_hover_text' => '#60A5FA',
            'dark_active_bg'  => '#3B82F6',
            'dark_active_text'=> '#FFFFFF',
        ],
        'card_container' => [
            'name'            => 'Enclosed Bar (Pill on Card Background)',
            'radius'          => '2rem',
            'padding'         => '0.4rem 1.1rem',
            'fontsize'        => '0.9rem',
            'gap'             => '0.35rem',
            'border_width'    => '1px',
            'btn_bg'          => '#FFFFFF',
            'btn_text'        => '#334155',
            'btn_border'      => 'transparent',
            'hover_bg'        => 'rgba(108, 92, 231, 0.1)',
            'hover_text'      => '#6C5CE7',
            'hover_border'    => 'transparent',
            'active_bg'       => 'linear-gradient(135deg, #6C5CE7 0%, #7F71EB 100%)',
            'active_text'     => '#FFFFFF',
            'active_border'   => 'transparent',
            'active_shadow'   => '0 3px 10px rgba(108, 92, 231, 0.3)',
            'bar_bg'          => '#F1F5F9',
            'bar_padding'     => '0.4rem 0.6rem',
            'bar_radius'      => '2.5rem',
            'align'           => 'flex-start',
            'dark_btn_bg'     => '#1E2638',
            'dark_btn_text'   => '#CBD5E1',
            'dark_btn_border' => 'transparent',
            'dark_hover_bg'   => 'rgba(142, 124, 255, 0.2)',
            'dark_hover_text' => '#8E7CFF',
            'dark_active_bg'  => '#8E7CFF',
            'dark_active_text'=> '#121824',
        ],
        'custom' => [
            'name'            => 'Custom (Use Individual Settings Below)',
            'radius'          => '2rem',
            'padding'         => '0.45rem 1.15rem',
            'fontsize'        => '0.9rem',
            'gap'             => '0.4rem',
            'border_width'    => '1px',
            'btn_bg'          => '#FFFFFF',
            'btn_text'        => '#2D3436',
            'btn_border'      => 'rgba(108, 92, 231, 0.15)',
            'hover_bg'        => 'rgba(108, 92, 231, 0.08)',
            'hover_text'      => '#6C5CE7',
            'hover_border'    => '#6C5CE7',
            'active_bg'       => 'linear-gradient(135deg, #6C5CE7 0%, #7F71EB 100%)',
            'active_text'     => '#FFFFFF',
            'active_border'   => '#6C5CE7',
            'active_shadow'   => '0 4px 14px rgba(108, 92, 231, 0.35)',
            'bar_bg'          => 'transparent',
            'bar_padding'     => '0',
            'bar_radius'      => '0px',
            'align'           => 'flex-start',
            'dark_btn_bg'     => '#1E2638',
            'dark_btn_text'   => '#CBD5E1',
            'dark_btn_border' => 'rgba(255, 255, 255, 0.1)',
            'dark_hover_bg'   => 'rgba(255, 255, 255, 0.08)',
            'dark_hover_text' => '#FFFFFF',
            'dark_active_bg'  => '#8E7CFF',
            'dark_active_text'=> '#121824',
        ],
    ];
}

/**
 * Resolves the active secondary navigation palette based on preset and user settings.
 *
 * @return array
 */
function theme_theedu_get_secnav_palette() {
    $preset = theme_theedu_get_setting('secnav_preset', 'default');
    $presets = theme_theedu_get_secnav_presets_list();

    $base = isset($presets[$preset]) ? $presets[$preset] : $presets['default'];

    if ($preset === 'custom') {
        $radius = theme_theedu_get_setting('secnav_btn_radius', '2rem');
        $padding = theme_theedu_get_setting('secnav_btn_padding', '0.45rem 1.15rem');
        $fontsize = theme_theedu_get_setting('secnav_btn_fontsize', '0.9rem');
        $gap = theme_theedu_get_setting('secnav_btn_gap', '0.4rem');
        $borderwidth = theme_theedu_get_setting('secnav_btn_border_width', '1px');

        $btnbg = theme_theedu_get_setting('secnav_btn_bg', '#FFFFFF');
        $btntext = theme_theedu_get_setting('secnav_btn_text', '#2D3436');
        $btnborder = theme_theedu_get_setting('secnav_btn_border', 'rgba(108, 92, 231, 0.15)');

        $hoverbg = theme_theedu_get_setting('secnav_btn_hover_bg', 'rgba(108, 92, 231, 0.08)');
        $hovertext = theme_theedu_get_setting('secnav_btn_hover_text', '#6C5CE7');
        $hoverborder = theme_theedu_get_setting('secnav_btn_hover_border', '#6C5CE7');

        $activebg = theme_theedu_get_setting('secnav_btn_active_bg', '#6C5CE7');
        $activegradient = theme_theedu_get_setting('secnav_btn_active_gradient', '#7F71EB');
        $activetext = theme_theedu_get_setting('secnav_btn_active_text', '#FFFFFF');
        $activeborder = theme_theedu_get_setting('secnav_btn_active_border', '#6C5CE7');
        $activeshadow = (bool) theme_theedu_get_setting('secnav_btn_active_shadow', 1);

        $barbg = theme_theedu_get_setting('secnav_bar_bg', '');
        $barpadding = theme_theedu_get_setting('secnav_bar_padding', '0');
        $barradius = theme_theedu_get_setting('secnav_bar_radius', '0px');
        $align = theme_theedu_get_setting('secnav_align', 'flex-start');

        $darkbtnbg = theme_theedu_get_setting('dark_secnav_btn_bg', '#1E2638');
        $darkbtntext = theme_theedu_get_setting('dark_secnav_btn_text', '#CBD5E1');
        $darkbtnborder = theme_theedu_get_setting('dark_secnav_btn_border', 'rgba(255, 255, 255, 0.1)');
        $darkhoverbg = theme_theedu_get_setting('dark_secnav_btn_hover_bg', 'rgba(255, 255, 255, 0.08)');
        $darkhovertext = theme_theedu_get_setting('dark_secnav_btn_hover_text', '#FFFFFF');
        $darkactivebg = theme_theedu_get_setting('dark_secnav_btn_active_bg', '#8E7CFF');
        $darkactivetext = theme_theedu_get_setting('dark_secnav_btn_active_text', '#121824');

        $activebgvalue = $activebg;
        if (!empty($activegradient) && ($activegradient !== $activebg)) {
            $activebgvalue = "linear-gradient(135deg, {$activebg} 0%, {$activegradient} 100%)";
        }

        return [
            'radius'          => !empty($radius) ? $radius : '2rem',
            'padding'         => !empty($padding) ? $padding : '0.45rem 1.15rem',
            'fontsize'        => !empty($fontsize) ? $fontsize : '0.9rem',
            'gap'             => !empty($gap) ? $gap : '0.4rem',
            'border_width'    => !empty($borderwidth) ? $borderwidth : '1px',
            'btn_bg'          => !empty($btnbg) ? $btnbg : '#FFFFFF',
            'btn_text'        => !empty($btntext) ? $btntext : '#2D3436',
            'btn_border'      => !empty($btnborder) ? $btnborder : 'rgba(108, 92, 231, 0.15)',
            'hover_bg'        => !empty($hoverbg) ? $hoverbg : 'rgba(108, 92, 231, 0.08)',
            'hover_text'      => !empty($hovertext) ? $hovertext : '#6C5CE7',
            'hover_border'    => !empty($hoverborder) ? $hoverborder : '#6C5CE7',
            'active_bg'       => $activebgvalue,
            'active_text'     => !empty($activetext) ? $activetext : '#FFFFFF',
            'active_border'   => !empty($activeborder) ? $activeborder : '#6C5CE7',
            'active_shadow'   => $activeshadow ? '0 4px 14px rgba(108, 92, 231, 0.35)' : 'none',
            'bar_bg'          => !empty($barbg) ? $barbg : 'transparent',
            'bar_padding'     => !empty($barpadding) ? $barpadding : '0',
            'bar_radius'      => !empty($barradius) ? $barradius : '0px',
            'align'           => !empty($align) ? $align : 'flex-start',
            'dark_btn_bg'     => !empty($darkbtnbg) ? $darkbtnbg : '#1E2638',
            'dark_btn_text'   => !empty($darkbtntext) ? $darkbtntext : '#CBD5E1',
            'dark_btn_border' => !empty($darkbtnborder) ? $darkbtnborder : 'rgba(255, 255, 255, 0.1)',
            'dark_hover_bg'   => !empty($darkhoverbg) ? $darkhoverbg : 'rgba(255, 255, 255, 0.08)',
            'dark_hover_text' => !empty($darkhovertext) ? $darkhovertext : '#FFFFFF',
            'dark_active_bg'  => !empty($darkactivebg) ? $darkactivebg : '#8E7CFF',
            'dark_active_text'=> !empty($darkactivetext) ? $darkactivetext : '#121824',
        ];
    }

    // For named presets, allow individual custom overrides if explicitly set by admin.
    $customradius = theme_theedu_get_setting('secnav_btn_radius', '');
    if (!empty($customradius)) {
        $base['radius'] = $customradius;
    }
    $custompadding = theme_theedu_get_setting('secnav_btn_padding', '');
    if (!empty($custompadding)) {
        $base['padding'] = $custompadding;
    }
    $customalign = theme_theedu_get_setting('secnav_align', '');
    if (!empty($customalign)) {
        $base['align'] = $customalign;
    }

    return $base;
}

/**
 * Injects SCSS variables before compiling.
 *
 * @param theme_config $theme
 * @return string
 */
function theme_theedu_get_pre_scss($theme) {
    $scss = '';

    // Preset / Pastel color configuration.
    $primary = theme_theedu_get_color('primary', '#6C5CE7');
    $secondary = theme_theedu_get_color('secondary', '#00CEC9');
    $accent = theme_theedu_get_color('accent', '#FF7675');
    $bodybg = theme_theedu_get_color('body_bg', '#F7F9FC');
    $cardbg = theme_theedu_get_color('card_bg', '#FFFFFF');

    $scss .= "\$primary: {$primary};\n";
    $scss .= "\$secondary: {$secondary};\n";
    $scss .= "\$info: {$secondary};\n";
    $scss .= "\$danger: {$accent};\n";
    $scss .= "\$body-bg: {$bodybg};\n";
    $scss .= "\$card-bg: {$cardbg};\n";

    // Rounded corners for modern pastel look.
    $scss .= "\$border-radius: 0.75rem !default;\n";
    $scss .= "\$border-radius-lg: 1.25rem !default;\n";
    $scss .= "\$border-radius-sm: 0.5rem !default;\n";

    return $scss;
}

/**
 * Injects extra SCSS (CSS variables & custom CSS) after main compile.
 *
 * @param theme_config $theme
 * @return string
 */
function theme_theedu_get_extra_scss($theme) {
    $primary = theme_theedu_get_color('primary', '#6C5CE7');
    $secondary = theme_theedu_get_color('secondary', '#00CEC9');
    $accent = theme_theedu_get_color('accent', '#FF7675');
    $navbarbg = theme_theedu_get_color('navbar_bg', '#FFFFFF');
    $navbartext = theme_theedu_get_color('navbar_text', '#2D3436');
    $bodybg = theme_theedu_get_color('body_bg', '#F7F9FC');
    $cardbg = theme_theedu_get_color('card_bg', '#FFFFFF');
    $footerbg = theme_theedu_get_color('footer_bg', '#2D3436');
    $footertext = theme_theedu_get_color('footer_text', '#DFE6E9');

    // Dark mode overrides.
    $darkbodybg = theme_theedu_get_setting('dark_color_body_bg', '#121824');
    $darkcardbg = theme_theedu_get_setting('dark_color_card_bg', '#1E2638');
    $darknavbarbg = theme_theedu_get_setting('dark_color_navbar_bg', '#1A2130');
    $darkprimary = theme_theedu_get_setting('dark_color_primary', '#8E7CFF');

    // Category presentation settings.
    $catthumbheight = (int) theme_theedu_get_setting('category_thumbnail_height', 140);
    if ($catthumbheight < 60 || $catthumbheight > 400) {
        $catthumbheight = 140;
    }
    $enablecatthumbs = (bool) theme_theedu_get_setting('enable_category_thumbnails', 1);
    $catcardstyle = theme_theedu_get_setting('category_card_style', 'banner');
    $catthumbsactive = ($enablecatthumbs && ($catcardstyle === 'banner')) ? '1' : '0';

    // Font Configuration.
    $themefont = theme_theedu_get_setting('theme_font', 'plus_jakarta_sans');
    $fontfamily = "'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif";

    switch ($themefont) {
        case 'inter':
            $fontfamily = "'Inter', sans-serif";
            break;
        case 'poppins':
            $fontfamily = "'Poppins', sans-serif";
            break;
        case 'nunito':
            $fontfamily = "'Nunito', sans-serif";
            break;
        case 'outfit':
            $fontfamily = "'Outfit', sans-serif";
            break;
        case 'roboto':
            $fontfamily = "'Roboto', sans-serif";
            break;
        case 'custom':
            $customfontname = theme_theedu_get_setting('custom_font_name', '');
            if (!empty($customfontname)) {
                $fontfamily = "'{$customfontname}', sans-serif";
            }
            break;
    }

    $extra = "
:root {
    --theedu-font-family: {$fontfamily};
    --theedu-primary: {$primary};
    --theedu-secondary: {$secondary};
    --theedu-accent: {$accent};
    --theedu-navbar-bg: {$navbarbg};
    --theedu-navbar-text: {$navbartext};
    --theedu-body-bg: {$bodybg};
    --theedu-card-bg: {$cardbg};
    --theedu-footer-bg: {$footerbg};
    --theedu-footer-text: {$footertext};
    --theedu-radius: 1rem;
    --theedu-shadow: 0 10px 25px -5px rgba(108, 92, 231, 0.08), 0 8px 10px -6px rgba(108, 92, 231, 0.04);
    --theedu-cat-thumb-height: {$catthumbheight}px;
    --theedu-cat-thumbs-enabled: {$catthumbsactive};
}

body, .navbar, .card, h1, h2, h3, h4, h5, h6, .btn, input, select, textarea {
    font-family: var(--theedu-font-family) !important;
}

[data-theme=\"dark\"] {
    --theedu-primary: {$darkprimary};
    --theedu-body-bg: {$darkbodybg};
    --theedu-card-bg: {$darkcardbg};
    --theedu-navbar-bg: {$darknavbarbg};
    --theedu-navbar-text: #E4E7EB;
    --theedu-footer-bg: #0D121B;
    --theedu-footer-text: #A0AEC0;
    --theedu-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.35);
}
";

    // Append user custom CSS if provided.
    $customcss = theme_theedu_get_setting('customcss', '');
    if (!empty($customcss)) {
        $extra .= "\n/* Custom CSS from Theme Administration */\n" . $customcss . "\n";
    }

    return $extra;
}

/**
 * Returns Google Font stylesheet URL based on theme settings.
 *
 * @return string
 */
function theme_theedu_get_font_url() {
    $themefont = theme_theedu_get_setting('theme_font', 'plus_jakarta_sans');
    switch ($themefont) {
        case 'inter':
            return 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap';
        case 'poppins':
            return 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap';
        case 'nunito':
            return 'https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800&display=swap';
        case 'outfit':
            return 'https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap';
        case 'roboto':
            return 'https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap';
        case 'custom':
            $url = theme_theedu_get_setting('custom_font_url', '');
            return !empty($url) ? $url : 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap';
        default:
            return 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap';
    }
}

/**
 * Generates dynamic inline CSS based on theme settings.
 * Ensures custom colors apply immediately on save without requiring SCSS recompile or cache purging.
 *
 * @return string
 */
function theme_theedu_get_inline_css() {
    $primary = theme_theedu_get_color('primary', '#6C5CE7');
    $secondary = theme_theedu_get_color('secondary', '#00CEC9');
    $accent = theme_theedu_get_color('accent', '#FF7675');
    $navbarbg = theme_theedu_get_color('navbar_bg', '#FFFFFF');
    $navbartext = theme_theedu_get_color('navbar_text', '#2D3436');
    $bodybg = theme_theedu_get_color('body_bg', '#F7F9FC');
    $cardbg = theme_theedu_get_color('card_bg', '#FFFFFF');
    $footerbg = theme_theedu_get_color('footer_bg', '#2D3436');
    $footertext = theme_theedu_get_color('footer_text', '#DFE6E9');
    $activepreset = theme_theedu_get_setting('brand_preset', 'default');

    // Dark mode overrides.
    $darkbodybg = theme_theedu_get_setting('dark_color_body_bg', '#121824');
    $darkcardbg = theme_theedu_get_setting('dark_color_card_bg', '#1E2638');
    $darknavbarbg = theme_theedu_get_setting('dark_color_navbar_bg', '#1A2130');
    $darkprimary = theme_theedu_get_color('dark_primary', '#8E7CFF');

    // Margin & layout dimensions settings.
    $contentmargintop = (int) theme_theedu_get_setting('content_margin_top', 20);
    $contentmaxwidth = (int) theme_theedu_get_setting('content_max_width', 1440);
    $contentsidepadding = (int) theme_theedu_get_setting('content_side_padding', 20);
    $contentmarginbottom = (int) theme_theedu_get_setting('content_margin_bottom', 20);
    $contentcardpadding = (int) theme_theedu_get_setting('content_card_padding', 24);
    $enablefullwidth = (bool) theme_theedu_get_setting('enable_full_width_content', 1);

    // Category & subcategory presentation settings.
    $catthumbheight = (int) theme_theedu_get_setting('category_thumbnail_height', 140);
    if ($catthumbheight < 60 || $catthumbheight > 400) {
        $catthumbheight = 140;
    }
    $enablecatthumbs = (bool) theme_theedu_get_setting('enable_category_thumbnails', 1);
    $catcardstyle = theme_theedu_get_setting('category_card_style', 'banner');
    $catthumbsactive = ($enablecatthumbs && ($catcardstyle === 'banner')) ? '1' : '0';

    // Secondary Navigation & Tabs (Course tabs, Admin tabs, etc.) Palette & Presets.
    $secnav = theme_theedu_get_secnav_palette();

    return "<style id=\"theedu-dynamic-colors\" data-brand-preset=\"{$activepreset}\">
:root {
    --theedu-primary: {$primary};
    --theedu-secondary: {$secondary};
    --theedu-accent: {$accent};
    --theedu-navbar-bg: {$navbarbg};
    --theedu-navbar-text: {$navbartext};
    --theedu-body-bg: {$bodybg};
    --theedu-card-bg: {$cardbg};
    --theedu-footer-bg: {$footerbg};
    --theedu-footer-text: {$footertext};
    --theedu-content-margin-top: {$contentmargintop}px;
    --theedu-content-max-width: {$contentmaxwidth}px;
    --theedu-content-side-padding: {$contentsidepadding}px;
    --theedu-content-margin-bottom: {$contentmarginbottom}px;
    --theedu-content-card-padding: {$contentcardpadding}px;
    --theedu-cat-thumb-height: {$catthumbheight}px;
    --theedu-cat-thumbs-enabled: {$catthumbsactive};
    --theedu-secnav-btn-radius: {$secnav['radius']};
    --theedu-secnav-btn-padding: {$secnav['padding']};
    --theedu-secnav-btn-fontsize: {$secnav['fontsize']};
    --theedu-secnav-btn-gap: {$secnav['gap']};
    --theedu-secnav-btn-border-width: {$secnav['border_width']};
    --theedu-secnav-btn-bg: {$secnav['btn_bg']};
    --theedu-secnav-btn-text: {$secnav['btn_text']};
    --theedu-secnav-btn-border: {$secnav['btn_border']};
    --theedu-secnav-btn-hover-bg: {$secnav['hover_bg']};
    --theedu-secnav-btn-hover-text: {$secnav['hover_text']};
    --theedu-secnav-btn-hover-border: {$secnav['hover_border']};
    --theedu-secnav-btn-active-bg: {$secnav['active_bg']};
    --theedu-secnav-btn-active-text: {$secnav['active_text']};
    --theedu-secnav-btn-active-border: {$secnav['active_border']};
    --theedu-secnav-btn-active-shadow: {$secnav['active_shadow']};
    --theedu-secnav-bar-bg: {$secnav['bar_bg']};
    --theedu-secnav-bar-padding: {$secnav['bar_padding']};
    --theedu-secnav-bar-radius: {$secnav['bar_radius']};
    --theedu-secnav-align: {$secnav['align']};
}
#page-wrapper.theedu-floating-header .navbar.fixed-top,
.theedu-floating-header .navbar.fixed-top,
.theedu-floating-header .navbar,
.navbar.fixed-top,
.navbar,
nav.fixed-top,
header.navbar,
nav.navbar,
.theedu-navbar {
    background-color: var(--theedu-navbar-bg) !important;
    color: var(--theedu-navbar-text) !important;
}
.navbar.fixed-top .navbar-nav .nav-link,
.navbar.fixed-top .theedu-site-title,
.navbar.fixed-top .header-shortcut-btn,
.navbar.fixed-top .header-shortcut-btn i,
.navbar.fixed-top .popover-region-toggle i,
.navbar.fixed-top .popover-region-toggle .icon,
.navbar.fixed-top .theedu-darkmode-toggle,
.navbar.fixed-top .theedu-darkmode-toggle i,
.navbar.fixed-top .theedu-darkmode-toggle svg,
.navbar.fixed-top .usermenu-wrapper,
.navbar.fixed-top .theedu-lang-wrapper,
.navbar.fixed-top .theedu-user-pill,
.navbar.fixed-top .theedu-user-pill .user-name,
.navbar.fixed-top .theedu-user-pill .user-role-label,
.navbar.fixed-top .theedu-user-pill i,
.navbar.fixed-top .navbar-toggler,
.navbar.fixed-top .navbar-toggler i {
    color: var(--theedu-navbar-text) !important;
}
.navbar.fixed-top .navbar-nav .nav-link:hover,
.navbar.fixed-top .navbar-nav .nav-link.active,
.navbar.fixed-top .navbar-nav .nav-link[aria-current=\"true\"] {
    color: var(--theedu-primary) !important;
    background: rgba(108, 92, 231, 0.08) !important;
}
body:not(.pagelayout-login),
body:not(.pagelayout-login) #page-wrapper,
#page,
#page.drawers,
.theedu-frontpage-container,
#page-content {
    background-color: var(--theedu-body-bg) !important;
}
body.pagelayout-login #page-wrapper {
    background: transparent !important;
    background-color: transparent !important;
}
.card,
.theedu-card,
.dashboard-card,
.activity-item,
.section.main,
.course-section,
.block,
.theedu-stat-box,
.theedu-course-card {
    background-color: var(--theedu-card-bg) !important;
}
.btn-primary {
    background: linear-gradient(135deg, var(--theedu-primary), var(--theedu-secondary)) !important;
    border-color: var(--theedu-primary) !important;
    color: #FFFFFF !important;
}
.btn-primary:hover,
.btn-primary:focus {
    background: linear-gradient(135deg, var(--theedu-primary), var(--theedu-primary)) !important;
    border-color: var(--theedu-primary) !important;
    filter: brightness(0.92);
}
a:not(.btn):not(.nav-link):not(.dropdown-item) {
    color: var(--theedu-primary);
}
.theedu-logo-badge {
    background: linear-gradient(135deg, var(--theedu-primary), var(--theedu-secondary)) !important;
}
.theedu-site-title {
    background: linear-gradient(135deg, var(--theedu-primary), var(--theedu-secondary)) !important;
    -webkit-background-clip: text !important;
    -webkit-text-fill-color: transparent !important;
}
.badge-primary, .slide-badge, .theedu-badge {
    background: linear-gradient(135deg, var(--theedu-primary), var(--theedu-secondary)) !important;
    color: #FFFFFF !important;
}
.form-check-input:checked {
    background-color: var(--theedu-primary) !important;
    border-color: var(--theedu-primary) !important;
}
.theedu-floating-editmode .form-check-input:checked {
    background-color: var(--theedu-primary) !important;
    border-color: var(--theedu-primary) !important;
}
#page-footer {
    background-color: var(--theedu-footer-bg) !important;
    color: var(--theedu-footer-text) !important;
}
#page-footer p,
#page-footer .footer-desc,
#page-footer a:not(:hover),
#page-footer .theedu-footer-bottom {
    color: var(--theedu-footer-text) !important;
}
#page-footer a:hover {
    color: var(--theedu-primary) !important;
}
[data-theme=\"dark\"],
[data-bs-theme=\"dark\"],
body.theedu-dark-mode {
    --theedu-primary: {$darkprimary};
    --theedu-body-bg: {$darkbodybg};
    --theedu-card-bg: {$darkcardbg};
    --theedu-navbar-bg: {$darknavbarbg};
    --theedu-navbar-text: #E4E7EB;
    --theedu-footer-bg: #0D121B;
    --theedu-footer-text: #CBD5E1;
    --theedu-secnav-btn-bg: {$secnav['dark_btn_bg']};
    --theedu-secnav-btn-text: {$secnav['dark_btn_text']};
    --theedu-secnav-btn-border: {$secnav['dark_btn_border']};
    --theedu-secnav-btn-hover-bg: {$secnav['dark_hover_bg']};
    --theedu-secnav-btn-hover-text: {$secnav['dark_hover_text']};
    --theedu-secnav-btn-active-bg: {$secnav['dark_active_bg']};
    --theedu-secnav-btn-active-text: {$secnav['dark_active_text']};
}
/* Secondary Navigation & Tabs Dynamic Customization */
.secondary-navigation,
#page .secondary-navigation {
    background: var(--theedu-secnav-bar-bg) !important;
    padding: var(--theedu-secnav-bar-padding) !important;
    border-radius: var(--theedu-secnav-bar-radius) !important;
    border-bottom: none !important;
}
.secondary-navigation .navigation,
.secondary-navigation .more-nav,
.secondary-navigation .nav-tabs {
    justify-content: var(--theedu-secnav-align) !important;
    gap: var(--theedu-secnav-btn-gap) !important;
    border-bottom: none !important;
}
.secondary-navigation .nav-tabs .nav-link,
.secondary-navigation .more-nav .nav-link,
.secondary-navigation .navigation .nav-link,
.secondary-navigation .moremenu .nav-link {
    border-radius: var(--theedu-secnav-btn-radius) !important;
    padding: var(--theedu-secnav-btn-padding) !important;
    font-size: var(--theedu-secnav-btn-fontsize) !important;
    font-weight: 600 !important;
    border: var(--theedu-secnav-btn-border-width) solid var(--theedu-secnav-btn-border) !important;
    color: var(--theedu-secnav-btn-text) !important;
    background-color: var(--theedu-secnav-btn-bg) !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    transition: all 0.2s ease !important;
    text-decoration: none !important;
}
.secondary-navigation .nav-tabs .nav-link:hover,
.secondary-navigation .more-nav .nav-link:hover,
.secondary-navigation .navigation .nav-link:hover,
.secondary-navigation .moremenu .nav-link:hover {
    background-color: var(--theedu-secnav-btn-hover-bg) !important;
    border-color: var(--theedu-secnav-btn-hover-border) !important;
    color: var(--theedu-secnav-btn-hover-text) !important;
    transform: translateY(-1px) !important;
    text-decoration: none !important;
}
.secondary-navigation .nav-tabs .nav-link.active,
.secondary-navigation .more-nav .nav-link.active,
.secondary-navigation .navigation .nav-link.active,
.secondary-navigation .moremenu .nav-link.active {
    background: var(--theedu-secnav-btn-active-bg) !important;
    border-color: var(--theedu-secnav-btn-active-border) !important;
    color: var(--theedu-secnav-btn-active-text) !important;
    box-shadow: var(--theedu-secnav-btn-active-shadow) !important;
    text-decoration: none !important;
}
@media (max-width: 768px) {
    .navbar.fixed-top .primary-navigation {
        display: none !important;
    }
    .navbar.fixed-top .theedu-logo-top-left {
        max-width: 130px !important;
    }
    .navbar.fixed-top .theedu-logo-top-left img {
        max-height: 32px !important;
        max-width: 110px !important;
    }
    .navbar.fixed-top .theedu-logo-top-left .theedu-site-title {
        display: none !important;
    }
    .theedu-header-actions {
        gap: 4px !important;
        flex-wrap: nowrap !important;
    }
    .theedu-header-actions .theedu-darkmode-toggle {
        width: 32px !important;
        height: 32px !important;
        margin: 0 2px !important;
    }
    .theedu-header-actions .usermenu .usertext,
    .theedu-header-actions .usermenu .userbutton .usertext {
        display: none !important;
    }
    .theedu-header-actions .theedu-user-pill .user-role-label,
    .theedu-header-actions .theedu-user-pill i.fa-chevron-down {
        display: none !important;
    }
}
/* Frontpage Layout Container Protection against Moodle core #maincontent clipping */
.theedu-frontpage-container,
.theedu-frontpage-container #theedu-main-portal,
.theedu-frontpage-container main,
main.theedu-main-content {
    display: block !important;
    height: auto !important;
    min-height: auto !important;
    overflow: visible !important;
}
/* Fail-Safe Hero Banner Slider Dimensions */
.theedu-slider-wrapper {
    margin: 1.5rem auto 2.5rem !important;
    max-width: 1440px !important;
    border-radius: 1.5rem !important;
    overflow: hidden !important;
    box-shadow: 0 16px 40px rgba(108, 92, 231, 0.12) !important;
    display: block !important;
    position: relative !important;
    background-color: var(--theedu-card-bg, #FFFFFF) !important;
}
.theedu-carousel,
.theedu-carousel .carousel-inner {
    width: 100% !important;
    height: 480px !important;
    min-height: 480px !important;
    border-radius: 1.5rem !important;
    position: relative !important;
    overflow: hidden !important;
    display: block !important;
}
.theedu-carousel .carousel-item {
    width: 100% !important;
    height: 480px !important;
    min-height: 480px !important;
    position: relative !important;
    background-size: cover !important;
    background-position: center !important;
    background-repeat: no-repeat !important;
    border-radius: 1.5rem !important;
    overflow: hidden !important;
    float: none !important;
    margin-right: 0 !important;
}
.theedu-carousel .carousel-item:not(.active) {
    display: none !important;
}
.theedu-carousel .carousel-item.active {
    display: block !important;
}
.theedu-carousel .slide-overlay {
    position: absolute !important;
    inset: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background: linear-gradient(to right, rgba(15, 23, 42, 0.85) 0%, rgba(15, 23, 42, 0.50) 60%, rgba(15, 23, 42, 0.15) 100%) !important;
    display: flex !important;
    align-items: center !important;
    padding-left: 5% !important;
    padding-right: 5% !important;
    border-radius: 1.5rem !important;
    z-index: 5 !important;
}
.theedu-carousel .theedu-caption-card {
    background: rgba(255, 255, 255, 0.14) !important;
    backdrop-filter: blur(14px) !important;
    -webkit-backdrop-filter: blur(14px) !important;
    border: 1px solid rgba(255, 255, 255, 0.25) !important;
    border-radius: 1.25rem !important;
    padding: 2.25rem !important;
    max-width: 600px !important;
    color: #FFFFFF !important;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.25) !important;
    text-align: left !important;
    display: block !important;
    position: relative !important;
    margin: 0 !important;
}
.theedu-carousel .theedu-caption-card .slide-title {
    font-size: 2.25rem !important;
    font-weight: 800 !important;
    color: #FFFFFF !important;
    line-height: 1.25 !important;
    margin-bottom: 0.85rem !important;
    text-shadow: 0 2px 6px rgba(0, 0, 0, 0.35) !important;
}
.theedu-carousel .theedu-caption-card .slide-caption-text {
    font-size: 1rem !important;
    color: rgba(255, 255, 255, 0.92) !important;
    line-height: 1.5 !important;
    margin-bottom: 1.5rem !important;
}
.theedu-carousel .theedu-caption-card .slide-btn {
    display: inline-flex !important;
    align-items: center !important;
    gap: 0.5rem !important;
    padding: 0.7rem 1.6rem !important;
    font-size: 0.95rem !important;
    font-weight: 600 !important;
    border-radius: 2rem !important;
    background: linear-gradient(135deg, var(--theedu-primary), var(--theedu-secondary)) !important;
    color: #FFFFFF !important;
    border: none !important;
    text-decoration: none !important;
    box-shadow: 0 8px 20px rgba(108, 92, 231, 0.35) !important;
}
.theedu-carousel .carousel-indicators {
    bottom: 1.25rem !important;
    margin-bottom: 0 !important;
    z-index: 15 !important;
}
.theedu-carousel .carousel-indicators button {
    width: 10px !important;
    height: 10px !important;
    border-radius: 50% !important;
    background-color: rgba(255, 255, 255, 0.5) !important;
    border: 1px solid rgba(0, 0, 0, 0.1) !important;
    margin: 0 5px !important;
}
.theedu-carousel .carousel-indicators button.active {
    width: 28px !important;
    border-radius: 8px !important;
    background: var(--theedu-primary) !important;
}
@media (max-width: 768px) {
    .theedu-carousel,
    .theedu-carousel .carousel-inner,
    .theedu-carousel .carousel-item {
        height: 340px !important;
        min-height: 340px !important;
    }
    .theedu-carousel .theedu-caption-card {
        padding: 1.5rem !important;
        max-width: 100% !important;
    }
    .theedu-carousel .theedu-caption-card .slide-title {
        font-size: 1.35rem !important;
    }
}
/* Modern Course Search Section */
.theedu-course-search-section {
    position: relative;
    z-index: 10;
}
.theedu-search-card {
    background: linear-gradient(135deg, rgba(108, 92, 231, 0.05) 0%, rgba(0, 206, 201, 0.04) 100%) !important;
    border-radius: 1.5rem !important;
    border: 1px solid rgba(108, 92, 231, 0.12) !important;
    backdrop-filter: blur(8px);
}
.theedu-search-form {
    display: flex !important;
    align-items: center !important;
    position: relative !important;
    width: 100% !important;
    max-width: 680px !important;
    margin: 0 auto !important;
    background: var(--theedu-card-bg, #FFFFFF) !important;
    border: 2px solid rgba(108, 92, 231, 0.18) !important;
    border-radius: 50rem !important;
    padding: 6px 8px 6px 18px !important;
    box-shadow: 0 8px 24px rgba(108, 92, 231, 0.08) !important;
    transition: all 0.3s ease !important;
}
.theedu-search-form:focus-within {
    border-color: var(--theedu-primary, #6C5CE7) !important;
    box-shadow: 0 10px 30px rgba(108, 92, 231, 0.18) !important;
}
.theedu-search-icon-wrap {
    color: var(--theedu-primary, #6C5CE7) !important;
    font-size: 1.15rem !important;
    display: flex !important;
    align-items: center !important;
    flex-shrink: 0 !important;
}
.theedu-search-input {
    flex: 1 1 auto !important;
    min-width: 0 !important;
    width: auto !important;
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    background: transparent !important;
    color: var(--theedu-navbar-text, #2D3436) !important;
    font-size: 1rem !important;
    padding: 0.55rem 0.75rem !important;
}
.theedu-search-input::placeholder {
    color: #94A3B8 !important;
    opacity: 1 !important;
}
.theedu-search-submit {
    flex-shrink: 0 !important;
    white-space: nowrap !important;
    border-radius: 50rem !important;
    padding: 0.65rem 1.6rem !important;
    font-weight: 600 !important;
    font-size: 0.95rem !important;
    background: linear-gradient(135deg, var(--theedu-primary, #6C5CE7), var(--theedu-secondary, #00CEC9)) !important;
    border: none !important;
    color: #FFFFFF !important;
    box-shadow: 0 4px 14px rgba(108, 92, 231, 0.25) !important;
    transition: transform 0.2s ease, box-shadow 0.2s ease !important;
}
.theedu-search-submit:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 6px 20px rgba(108, 92, 231, 0.35) !important;
}
.theedu-search-tag-pill {
    display: inline-flex !important;
    align-items: center !important;
    padding: 5px 14px !important;
    font-size: 0.8rem !important;
    font-weight: 600 !important;
    border-radius: 20px !important;
    background: rgba(108, 92, 231, 0.08) !important;
    color: var(--theedu-primary, #6C5CE7) !important;
    border: 1px solid rgba(108, 92, 231, 0.15) !important;
    text-decoration: none !important;
    transition: all 0.2s ease !important;
}
.theedu-search-tag-pill:hover {
    background: var(--theedu-primary, #6C5CE7) !important;
    color: #FFFFFF !important;
    transform: translateY(-1px) !important;
}

/* Course Card Teacher & Arrow Button Styles */
.theedu-course-card .course-teacher-info {
    min-width: 0;
}
.theedu-course-card .teacher-avatar-placeholder {
    transition: transform 0.25s ease;
}
.theedu-course-card:hover .teacher-avatar-placeholder {
    transform: scale(1.08);
}
.theedu-course-card .teacher-role-label {
    display: block;
    font-size: 0.72rem;
    color: #64748B;
    line-height: 1.1;
    margin-top: 1px;
}
.theedu-course-card .course-card-arrow-btn {
    width: 34px !important;
    height: 34px !important;
    border-radius: 50% !important;
    background: rgba(108, 92, 231, 0.08) !important;
    color: var(--theedu-primary, #6C5CE7) !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 0.85rem !important;
    text-decoration: none !important;
    flex-shrink: 0 !important;
    transition: all 0.25s ease !important;
}
.theedu-course-card:hover .course-card-arrow-btn {
    background: var(--theedu-primary, #6C5CE7) !important;
    color: #FFFFFF !important;
    transform: translateX(4px) !important;
    box-shadow: 0 4px 12px rgba(108, 92, 231, 0.3) !important;
}

/* Dark mode overrides for search & card footer */
[data-theme=\"dark\"] .theedu-search-card {
    background: rgba(30, 38, 56, 0.6) !important;
    border-color: rgba(255, 255, 255, 0.08) !important;
}
[data-theme=\"dark\"] .theedu-search-form {
    background: #1A2130 !important;
    border-color: rgba(255, 255, 255, 0.12) !important;
}
[data-theme=\"dark\"] .theedu-search-tag-pill {
    background: rgba(142, 124, 255, 0.15) !important;
    color: #C4B5FD !important;
    border-color: rgba(142, 124, 255, 0.25) !important;
}
[data-theme=\"dark\"] .theedu-course-card .teacher-avatar-placeholder {
    background: rgba(142, 124, 255, 0.2) !important;
    color: #C4B5FD !important;
}
[data-theme=\"dark\"] .theedu-course-card .course-card-arrow-btn {
    background: rgba(142, 124, 255, 0.15) !important;
    color: #C4B5FD !important;
}
[data-theme=\"dark\"] .theedu-course-card:hover .course-card-arrow-btn {
    background: var(--theedu-primary, #8E7CFF) !important;
    color: #121824 !important;
}

/* Frontpage duplicate Moodle core content control */
.theedu-core-frontpage-content.theedu-hide-core-content {
    display: none !important;
}
body.editing .theedu-core-frontpage-content.theedu-hide-core-content {
    display: block !important;
}

/* ==========================================================================
   THEEDU DYNAMIC CATEGORY & COURSE CATALOG CARDS GRID (/course/index.php)
   ========================================================================== */
#page-course-index-category .page-context-header,
#page-course-index-category .header-actions-container {
    background: var(--theedu-card-bg, #FFFFFF) !important;
    border: 1px solid rgba(108, 92, 231, 0.12) !important;
    border-radius: 1.25rem !important;
    padding: 1.25rem 1.5rem !important;
    box-shadow: 0 4px 16px rgba(108, 92, 231, 0.04) !important;
    margin-bottom: 1.5rem !important;
}
#page-course-index-category #action_bar {
    background: var(--theedu-card-bg, #FFFFFF) !important;
    border: 1px solid rgba(108, 92, 231, 0.12) !important;
    border-radius: 1rem !important;
    padding: 0.85rem 1.25rem !important;
    margin-bottom: 1.5rem !important;
    box-shadow: 0 4px 16px rgba(108, 92, 231, 0.04) !important;
}
#page-course-index-category #action_bar .urlselect select {
    border-radius: 2rem !important;
    border: 1.5px solid rgba(108, 92, 231, 0.2) !important;
    padding: 0.5rem 1.25rem !important;
    font-weight: 500 !important;
}
#page-course-index-category #action_bar .simplesearchform .form-control {
    border-radius: 2rem 0 0 2rem !important;
    border: 1.5px solid rgba(108, 92, 231, 0.2) !important;
    border-right: none !important;
    padding: 0.5rem 1.25rem !important;
}
#page-course-index-category #action_bar .simplesearchform .btn-primary {
    border-radius: 0 2rem 2rem 0 !important;
    background: var(--theedu-primary, #6C5CE7) !important;
    border-color: var(--theedu-primary, #6C5CE7) !important;
    padding: 0.5rem 1.25rem !important;
}
#page-course-index-category .page-context-header .page-header-headings h1,
#page-course-index-category h2.categoryname {
    font-size: 1.75rem !important;
    font-weight: 800 !important;
    letter-spacing: -0.02em !important;
    color: var(--theedu-navbar-text, #2D3436) !important;
    margin: 0 !important;
}
.theedu-category-grid,
.course_category_tree .subcategories {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)) !important;
    gap: 1.25rem !important;
    margin: 1.5rem 0 2rem !important;
    padding: 0 !important;
    list-style: none !important;
}
.theedu-category-card,
.course_category_tree .subcategories .category {
    background: var(--theedu-card-bg, #FFFFFF) !important;
    border: 1px solid rgba(108, 92, 231, 0.12) !important;
    border-radius: 1.25rem !important;
    padding: 0 !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: space-between !important;
    box-shadow: 0 4px 20px rgba(108, 92, 231, 0.06) !important;
    overflow: hidden !important;
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease !important;
    height: 100% !important;
    position: relative !important;
    cursor: pointer !important;
    text-decoration: none !important;
}
.theedu-category-card:hover,
.course_category_tree .subcategories .category:hover {
    transform: translateY(-5px) !important;
    box-shadow: 0 14px 32px rgba(108, 92, 231, 0.15) !important;
    border-color: var(--theedu-primary, #6C5CE7) !important;
}

/* Category Card Thumbnail Banner Area (Toggleable) */
.category-thumb-wrapper {
    height: var(--theedu-cat-thumb-height, 140px) !important;
    width: 100% !important;
    position: relative !important;
    overflow: hidden !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    flex-shrink: 0 !important;
}
.category-thumb-img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    transition: transform 0.4s ease !important;
}
.theedu-category-card:hover .category-thumb-img,
.course_category_tree .subcategories .category:hover .category-thumb-img {
    transform: scale(1.05) !important;
}
.category-thumb-fallback-icon {
    font-size: 2.75rem !important;
    opacity: 0.95 !important;
}

/* Overlaid Badges */
.category-badge-overlay {
    position: absolute !important;
    top: 12px !important;
    left: 12px !important;
    background: rgba(255, 255, 255, 0.92) !important;
    backdrop-filter: blur(8px) !important;
    -webkit-backdrop-filter: blur(8px) !important;
    color: var(--theedu-primary, #6C5CE7) !important;
    font-size: 0.72rem !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
    padding: 3px 10px !important;
    border-radius: 20px !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
    z-index: 2 !important;
}
.category-count-overlay {
    position: absolute !important;
    top: 12px !important;
    right: 12px !important;
    background: rgba(18, 24, 36, 0.65) !important;
    backdrop-filter: blur(8px) !important;
    -webkit-backdrop-filter: blur(8px) !important;
    color: #FFFFFF !important;
    font-size: 0.72rem !important;
    font-weight: 600 !important;
    padding: 3px 10px !important;
    border-radius: 20px !important;
    z-index: 2 !important;
}

/* Compact Icon Header (When thumbnail is disabled) */
.category-header-compact {
    padding: 1.25rem 1.25rem 0.5rem !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
}
.theedu-cat-icon-box {
    width: 44px !important;
    height: 44px !important;
    border-radius: 12px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 1.2rem !important;
    flex-shrink: 0 !important;
}

/* Card Body */
.category-card-body {
    padding: 1.25rem !important;
    display: flex !important;
    flex-direction: column !important;
    flex-grow: 1 !important;
    justify-content: space-between !important;
}
.category-card-title,
.course_category_tree .subcategories .category .categoryname {
    font-size: 1.15rem !important;
    font-weight: 700 !important;
    margin: 0 0 0.4rem 0 !important;
    color: var(--theedu-navbar-text, #2D3436) !important;
    line-height: 1.35 !important;
}
.category-card-title a,
.course_category_tree .subcategories .category .categoryname a {
    color: var(--theedu-navbar-text, #2D3436) !important;
    text-decoration: none !important;
    display: inline-block !important;
}
.category-card-title a::before,
.course_category_tree .subcategories .category .categoryname a::before {
    content: none !important; /* Eliminate folder icon! */
    display: none !important;
}
.category-card-title a:hover,
.course_category_tree .subcategories .category .categoryname a:hover {
    color: var(--theedu-primary, #6C5CE7) !important;
}
.category-card-desc {
    color: #64748B !important;
    font-size: 0.85rem !important;
    line-height: 1.5 !important;
    margin-bottom: 1rem !important;
    display: -webkit-box !important;
    -webkit-line-clamp: 2 !important;
    -webkit-box-orient: vertical !important;
    overflow: hidden !important;
}

/* Card Footer */
.category-card-footer {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    padding-top: 0.85rem !important;
    border-top: 1px solid rgba(108, 92, 231, 0.08) !important;
    margin-top: auto !important;
}
.category-pill-badge {
    font-size: 0.75rem !important;
    font-weight: 600 !important;
    padding: 3px 10px !important;
    border-radius: 20px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
}
.pill-courses {
    background: rgba(108, 92, 231, 0.1) !important;
    color: var(--theedu-primary, #6C5CE7) !important;
}
.pill-subcats {
    background: rgba(0, 206, 201, 0.12) !important;
    color: #00A8A4 !important;
}

/* Circular Arrow Action Button (Matches Course Card Arrow) */
.category-card-arrow-btn {
    width: 32px !important;
    height: 32px !important;
    border-radius: 50% !important;
    background: rgba(108, 92, 231, 0.08) !important;
    color: var(--theedu-primary, #6C5CE7) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 0.85rem !important;
    transition: all 0.2s ease !important;
    flex-shrink: 0 !important;
}
.theedu-category-card:hover .category-card-arrow-btn,
.course_category_tree .subcategories .category:hover .category-card-arrow-btn {
    background: var(--theedu-primary, #6C5CE7) !important;
    color: #FFFFFF !important;
    transform: translateX(3px) !important;
}
.course_category_tree .subcategories .category .numberofcourses {
    display: inline-flex !important;
    align-items: center !important;
    font-size: 0.8rem !important;
    font-weight: 600 !important;
    color: var(--theedu-primary, #6C5CE7) !important;
    background: rgba(108, 92, 231, 0.1) !important;
    border-radius: 20px !important;
    padding: 4px 12px !important;
    width: fit-content !important;
    margin-top: 0.75rem !important;
}
.courses,
.course_category_tree .courses,
.frontpage-course-list-all,
#frontpage-available-course-list .courses,
.category-browse {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)) !important;
    gap: 1.5rem !important;
    list-style: none !important;
    padding: 0 !important;
    margin: 1.5rem 0 3rem !important;
}
.coursebox {
    display: flex !important;
    flex-direction: column !important;
    position: relative !important;
    border-radius: 1.25rem !important;
    overflow: hidden !important;
    background: var(--theedu-card-bg, #FFFFFF) !important;
    border: 1px solid rgba(108, 92, 231, 0.12) !important;
    box-shadow: 0 4px 20px rgba(108, 92, 231, 0.05) !important;
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease !important;
    padding: 0 !important;
    margin: 0 !important;
    height: 100% !important;
    cursor: pointer !important;
}
.coursebox:hover {
    transform: translateY(-5px) !important;
    box-shadow: 0 14px 32px rgba(108, 92, 231, 0.15) !important;
    border-color: var(--theedu-primary, #6C5CE7) !important;
}

/* Flatten .content and .d-flex so .courseimage sits at order 1 (TOP BANNER) */
.coursebox > .content {
    display: contents !important;
}
.coursebox > .content > .d-flex {
    display: contents !important;
}

/* Category Tag Pill on Thumbnail */
.coursebox .course-tag {
    position: absolute !important;
    top: 1rem !important;
    left: 1rem !important;
    background: rgba(255, 255, 255, 0.92) !important;
    backdrop-filter: blur(8px) !important;
    -webkit-backdrop-filter: blur(8px) !important;
    color: var(--theedu-navbar-text, #2D3436) !important;
    font-size: 0.78rem !important;
    font-weight: 700 !important;
    padding: 5px 14px !important;
    border-radius: 20px !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
    z-index: 3 !important;
    pointer-events: none !important;
    border: 1px solid rgba(0, 0, 0, 0.04) !important;
}

/* 1. TOP BANNER: Course Image (Fix max-width: 100px Moodle Boost bug!) */
.coursebox .courseimage,
.coursebox .content .courseimage {
    order: 1 !important;
    width: 100% !important;
    height: 180px !important;
    max-width: 100% !important;
    max-height: 180px !important;
    min-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow: hidden !important;
    background: linear-gradient(135deg, rgba(108, 92, 231, 0.12) 0%, rgba(0, 206, 201, 0.1) 100%) !important;
    display: block !important;
    float: none !important;
    border-top-left-radius: 1.25rem !important;
    border-top-right-radius: 1.25rem !important;
}
.coursebox .courseimage img,
.coursebox .content .courseimage img,
#page-course-index-category .coursebox .courseimage img,
.category-browse .coursebox .courseimage img {
    width: 100% !important;
    height: 100% !important;
    max-width: 100% !important;
    max-height: none !important;
    min-width: 100% !important;
    min-height: 100% !important;
    object-fit: cover !important;
    display: block !important;
    transition: transform 0.3s ease !important;
}
.coursebox:hover .courseimage img {
    transform: scale(1.05) !important;
}

/* 2. TOP BANNER: Toga Icon + Pastel Gradient (when NO image uploaded) */
.coursebox:not(:has(.courseimage))::before {
    content: \"\" !important;
    order: 1 !important;
    display: block !important;
    width: 100% !important;
    height: 180px !important;
    background: linear-gradient(135deg, rgba(108, 92, 231, 0.18) 0%, rgba(0, 206, 201, 0.14) 100%) !important;
    border-top-left-radius: 1.25rem !important;
    border-top-right-radius: 1.25rem !important;
}
.coursebox:not(:has(.courseimage))::after {
    content: \"\f19d\" !important;
    font-family: \"Font Awesome 6 Free\" !important;
    font-weight: 900 !important;
    position: absolute !important;
    top: 58px !important;
    left: 50% !important;
    transform: translateX(-50%) !important;
    width: 64px !important;
    height: 64px !important;
    border-radius: 1.25rem !important;
    background: rgba(255, 255, 255, 0.9) !important;
    backdrop-filter: blur(8px) !important;
    -webkit-backdrop-filter: blur(8px) !important;
    color: var(--theedu-primary, #6C5CE7) !important;
    font-size: 1.75rem !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    box-shadow: 0 8px 20px rgba(108, 92, 231, 0.15) !important;
    pointer-events: none !important;
    transition: transform 0.25s ease !important;
    z-index: 2 !important;
}
.coursebox:not(:has(.courseimage)):hover::after {
    transform: translateX(-50%) scale(1.1) !important;
}

/* 3. MIDDLE: Course Title */
.coursebox .info {
    order: 2 !important;
    padding: 1.25rem 1.25rem 0.5rem !important;
    margin: 0 !important;
    float: none !important;
}
.coursebox .info .coursename {
    font-size: 1.15rem !important;
    font-weight: 700 !important;
    line-height: 1.4 !important;
    margin: 0 !important;
}
.coursebox .info .coursename a {
    color: var(--theedu-navbar-text, #2D3436) !important;
    text-decoration: none !important;
}
.coursebox .info .coursename a:hover {
    color: var(--theedu-primary, #6C5CE7) !important;
}
.coursebox .info .moreinfo {
    display: none !important;
}

/* 4. BODY & TEACHER */
.coursebox .flex-grow-1 {
    order: 3 !important;
    padding: 0 1.25rem 1.25rem !important;
    display: flex !important;
    flex-direction: column !important;
    flex-grow: 1 !important;
}
.coursebox .summary {
    font-size: 0.88rem !important;
    color: #64748B !important;
    line-height: 1.5 !important;
    margin-bottom: 1rem !important;
    flex-grow: 1 !important;
}
.coursebox .teachers {
    list-style: none !important;
    padding: 0.85rem 0 0 !important;
    margin: auto 0 0 !important;
    border-top: 1px solid rgba(0, 0, 0, 0.06) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    width: 100% !important;
}
.coursebox .teachers li {
    font-size: 0.85rem !important;
    font-weight: 600 !important;
    color: #64748B !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 0.75rem !important;
    margin: 0 !important;
}
.coursebox .teachers li::before {
    content: \"\f51b\" !important;
    font-family: \"Font Awesome 6 Free\" !important;
    font-weight: 900 !important;
    width: 36px !important;
    height: 36px !important;
    border-radius: 50% !important;
    background: rgba(108, 92, 231, 0.12) !important;
    color: var(--theedu-primary, #6C5CE7) !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 0.9rem !important;
    flex-shrink: 0 !important;
}
.coursebox .teachers li .font-weight-bold {
    display: none !important;
}
.coursebox .teachers li a {
    color: var(--theedu-navbar-text, #2D3436) !important;
    text-decoration: none !important;
    font-weight: 600 !important;
    font-size: 0.92rem !important;
    display: flex !important;
    flex-direction: column !important;
    line-height: 1.2 !important;
}
.coursebox .teachers li a::after {
    content: \"Teacher\" !important;
    font-size: 0.75rem !important;
    font-weight: 400 !important;
    color: #64748B !important;
    margin-top: 2px !important;
}
.coursebox .teachers li a:hover {
    color: var(--theedu-primary, #6C5CE7) !important;
}

/* Arrow button on the right */
.coursebox .teachers::after {
    content: \"\f061\" !important;
    font-family: \"Font Awesome 6 Free\" !important;
    font-weight: 900 !important;
    width: 36px !important;
    height: 36px !important;
    border-radius: 50% !important;
    background: rgba(108, 92, 231, 0.08) !important;
    color: var(--theedu-primary, #6C5CE7) !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 0.85rem !important;
    flex-shrink: 0 !important;
    margin-left: auto !important;
    transition: all 0.25s ease !important;
}
.coursebox:hover .teachers::after {
    background: var(--theedu-primary, #6C5CE7) !important;
    color: #FFFFFF !important;
    transform: translateX(4px) !important;
    box-shadow: 0 4px 12px rgba(108, 92, 231, 0.3) !important;
}

[data-theme=\"dark\"] .coursebox .course-tag {
    background: rgba(30, 38, 56, 0.92) !important;
    color: #C4B5FD !important;
    border-color: rgba(255, 255, 255, 0.1) !important;
}
[data-theme=\"dark\"] .coursebox .teachers::after {
    background: rgba(142, 124, 255, 0.15) !important;
    color: #C4B5FD !important;
}
[data-theme=\"dark\"] .coursebox:hover .teachers::after {
    background: var(--theedu-primary, #8E7CFF) !important;
    color: #121824 !important;
}
[data-theme=\"dark\"] .coursebox .teachers li a {
    color: #CBD5E1 !important;
}
[data-theme=\"dark\"] .coursebox .teachers li a::after {
    color: #94A3B8 !important;
}
[data-theme=\"dark\"] .theedu-category-card,
[data-theme=\"dark\"] .course_category_tree .subcategories .category {
    background: #1E2638 !important;
    border-color: rgba(255, 255, 255, 0.08) !important;
}
[data-theme=\"dark\"] .category-card-title,
[data-theme=\"dark\"] .category-card-title a,
[data-theme=\"dark\"] .course_category_tree .subcategories .category .categoryname,
[data-theme=\"dark\"] .course_category_tree .subcategories .category .categoryname a {
    color: #E4E7EB !important;
}
[data-theme=\"dark\"] .category-card-desc {
    color: #94A3B8 !important;
}
[data-theme=\"dark\"] .category-badge-overlay {
    background: rgba(30, 38, 56, 0.92) !important;
    color: #a29bfe !important;
}
[data-theme=\"dark\"] .category-card-footer {
    border-top-color: rgba(255, 255, 255, 0.08) !important;
}
[data-theme=\"dark\"] .category-card-arrow-btn {
    background: rgba(255, 255, 255, 0.08) !important;
    color: #E4E7EB !important;
}
[data-theme=\"dark\"] .category-card-arrow-btn:hover {
    background: var(--theedu-primary, #6C5CE7) !important;
    color: #FFFFFF !important;
}
[data-theme=\"dark\"] .coursebox {
    background: #1E2638 !important;
    border-color: rgba(255, 255, 255, 0.08) !important;
}
[data-theme=\"dark\"] .coursebox .info .coursename a {
    color: #E4E7EB !important;
}
[data-theme=\"dark\"] .coursebox .summary {
    color: #94A3B8 !important;
}
[data-theme=\"dark\"] .coursebox .teachers {
    border-top-color: rgba(255, 255, 255, 0.08) !important;
}
[data-theme=\"dark\"] .coursebox .teachers li a {
    color: #CBD5E1 !important;
}
[data-theme=\"dark\"] .coursebox:not(:has(.courseimage))::after {
    background: rgba(30, 38, 56, 0.9) !important;
    color: #8E7CFF !important;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3) !important;
}
[data-theme=\"dark\"] #page-course-index-category .page-context-header {
    background: #1E2638 !important;
    border-color: rgba(255, 255, 255, 0.08) !important;
}
[data-theme=\"dark\"] #page-course-index-category .page-context-header .page-header-headings h1 {
    color: #E4E7EB !important;
}
[data-theme=\"dark\"] #page-course-index-category #action_bar {
    background: #1E2638 !important;
    border-color: rgba(255, 255, 255, 0.08) !important;
}
[data-theme=\"dark\"] #page-course-index-category #action_bar .urlselect select,
[data-theme=\"dark\"] #page-course-index-category #action_bar .simplesearchform .form-control {
    background: #121824 !important;
    color: #E4E7EB !important;
    border-color: rgba(255, 255, 255, 0.15) !important;
}

/* 1. TOP SPACING: Exactly ~20px gap under navbar across ALL pages */
.theedu-floating-header #page.drawers,
.theedu-floating-header #page.theedu-frontpage-container,
.theedu-floating-header #page,
.theedu-floating-header .theedu-frontpage-container {
    margin-top: 70px !important;
    padding-top: var(--theedu-content-margin-top, 20px) !important;
}
.theedu-floating-header #page.drawers .main-inner,
.theedu-floating-header .theedu-frontpage-container .theedu-main-content,
.theedu-floating-header .theedu-slider-wrapper {
    margin-top: 0 !important;
}
.theedu-floating-header .theedu-main-content,
.theedu-floating-header #theedu-main-portal {
    padding-top: 0 !important;
    margin-top: 0 !important;
}
#page.drawers,
#page.theedu-frontpage-container,
#page {
    margin-top: 60px !important;
    padding-top: var(--theedu-content-margin-top, 20px) !important;
}
#page.drawers .main-inner {
    margin-top: 0 !important;
}

/* 2. SIDES: Match Content Width to Navbar & Shrink when Drawers Open */
#page.drawers,
#page.theedu-frontpage-container,
#page {
    padding-left: var(--theedu-content-side-padding, 20px) !important;
    padding-right: var(--theedu-content-side-padding, 20px) !important;
}
#page.drawers .main-inner,
.pagelayout-standard #page.drawers .main-inner,
.pagelayout-course #page.drawers .main-inner,
.pagelayout-incourse #page.drawers .main-inner,
.pagelayout-mydashboard #page.drawers .main-inner,
body.limitedwidth #page.drawers .main-inner,
body.mediumwidth #page.drawers .main-inner,
.theedu-main-content,
#theedu-main-portal {
    max-width: var(--theedu-content-max-width, 1440px) !important;
    width: 100% !important;
    margin-left: auto !important;
    margin-right: auto !important;
}
#page.drawers .main-inner {
    padding: var(--theedu-content-card-padding, 24px) !important;
}

/* 3. BOTTOM GAP TO FOOTER: Exactly ~20px gap to footer across ALL pages */
#page.drawers,
#page.drawers .main-inner,
#page.drawers #page-content,
.theedu-frontpage-container,
.theedu-frontpage-container .theedu-main-content,
.theedu-frontpage-container #theedu-main-portal {
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
}
.theedu-frontpage-container .theedu-main-content > div:last-child,
.theedu-frontpage-container .theedu-main-content > section:last-child,
.theedu-frontpage-container .theedu-courses-section:last-of-type,
.theedu-frontpage-container .theedu-category-section:last-of-type,
.theedu-frontpage-region-main {
    margin-bottom: 0 !important;
}
#page-footer {
    margin-top: var(--theedu-content-margin-bottom, 20px) !important;
    margin-bottom: 0 !important;
}
@media (max-width: 768px) {
    .theedu-floating-header #page.drawers,
    .theedu-floating-header #page.theedu-frontpage-container,
    .theedu-floating-header #page {
        margin-top: 58px !important;
        padding-top: var(--theedu-content-margin-top, 15px) !important;
        padding-left: 10px !important;
        padding-right: 10px !important;
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
    }
    .navbar.fixed-top {
        overflow: visible !important;
    }
    .navbar.fixed-top .theedu-header-actions {
        overflow: visible !important;
    }
    .navbar.fixed-top .popover-region {
        position: relative !important;
        overflow: visible !important;
    }
    .navbar.fixed-top .popover-region .popover-region-container {
        position: absolute !important;
        top: calc(100% + 10px) !important;
        right: -15px !important;
        width: calc(100vw - 32px) !important;
        max-width: 360px !important;
        z-index: 1060 !important;
        border-radius: 1rem !important;
        box-shadow: 0 12px 36px rgba(0, 0, 0, 0.18) !important;
    }
    .navbar.fixed-top .usermenu {
        position: relative !important;
        overflow: visible !important;
    }
    .navbar.fixed-top .usermenu .dropdown-menu {
        position: absolute !important;
        top: calc(100% + 10px) !important;
        right: 0 !important;
        left: auto !important;
        max-width: calc(100vw - 32px) !important;
        min-width: 210px !important;
        z-index: 1060 !important;
        border-radius: 1rem !important;
        box-shadow: 0 12px 36px rgba(0, 0, 0, 0.18) !important;
    }
}

/* Course Activity Bottom Navigation Bar */
.theedu-activity-navigation .theedu-activity-nav-card {
    background: var(--theedu-card-bg, #FFFFFF) !important;
    border: 1px solid rgba(108, 92, 231, 0.12) !important;
    box-shadow: 0 8px 24px rgba(108, 92, 231, 0.06) !important;
    border-radius: 1.25rem !important;
}
.theedu-activity-navigation .theedu-jump-select {
    border-radius: 0 50rem 50rem 0 !important;
    font-weight: 500 !important;
    font-size: 0.88rem !important;
    cursor: pointer !important;
    border-color: rgba(108, 92, 231, 0.2) !important;
}
.theedu-activity-navigation .theedu-jump-select:focus {
    border-color: var(--theedu-primary, #6C5CE7) !important;
    box-shadow: 0 0 0 0.2rem rgba(108, 92, 231, 0.15) !important;
}
.theedu-activity-navigation .input-group-text {
    border-radius: 50rem 0 0 50rem !important;
    border-color: rgba(108, 92, 231, 0.2) !important;
    color: var(--theedu-primary, #6C5CE7) !important;
}
.theedu-activity-navigation .theedu-nav-btn {
    font-weight: 600 !important;
    font-size: 0.88rem !important;
}
.theedu-activity-navigation .theedu-nav-btn.prev-btn {
    border-color: rgba(108, 92, 231, 0.3) !important;
    color: var(--theedu-primary, #6C5CE7) !important;
}
.theedu-activity-navigation .theedu-nav-btn.prev-btn:hover {
    background: var(--theedu-primary, #6C5CE7) !important;
    color: #FFFFFF !important;
}
.theedu-activity-navigation .theedu-nav-btn.next-btn {
    background: linear-gradient(135deg, var(--theedu-primary, #6C5CE7), var(--theedu-secondary, #00CEC9)) !important;
    border: none !important;
    color: #FFFFFF !important;
}
[data-theme=\"dark\"] .theedu-activity-navigation .theedu-activity-nav-card {
    background: #1E2638 !important;
    border-color: rgba(255, 255, 255, 0.08) !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3) !important;
}
[data-theme=\"dark\"] .theedu-activity-navigation .theedu-jump-select {
    background-color: #121824 !important;
    color: #E4E7EB !important;
    border-color: rgba(255, 255, 255, 0.15) !important;
}
[data-theme=\"dark\"] .theedu-activity-navigation .input-group-text {
    background-color: #121824 !important;
    border-color: rgba(255, 255, 255, 0.15) !important;
    color: #8E7CFF !important;
}
[data-theme=\"dark\"] .theedu-activity-navigation .prev-btn {
    border-color: rgba(142, 124, 255, 0.3) !important;
    color: #C4B5FD !important;
}
[data-theme=\"dark\"] .theedu-activity-navigation .prev-btn:hover {
    background: #8E7CFF !important;
    color: #121824 !important;
}

</style>";
}

/**
 * Helper to retrieve instructor/teacher information for a course.
 *
 * First checks course contacts configured via $CFG->coursecontact.
 * If empty, automatically falls back to database role assignments in the course context
 * for editingteacher, teacher, manager, or coursecreator archetypes.
 * Maps role to appropriate FontAwesome icon and localized role title.
 *
 * @param int|stdClass $courseorid
 * @param \core_course_list_element|null $courseobj
 * @param \moodle_page|null $page
 * @return array
 */
function theme_theedu_get_course_instructor_details($courseorid, $courseobj = null, $page = null) {
    global $DB, $CFG, $PAGE;

    if ($page === null) {
        $page = $PAGE;
    }

    $courseid = is_object($courseorid) ? (int)$courseorid->id : (int)$courseorid;
    $defaultteachericon = theme_theedu_get_setting('course_teacher_default_icon', 'fa-chalkboard-user');
    if (empty(trim($defaultteachericon))) {
        $defaultteachericon = 'fa-chalkboard-user';
    }

    $instructors = [];

    // 1. Try get_course_contacts from core_course_list_element if available
    if ($courseobj && method_exists($courseobj, 'get_course_contacts')) {
        $contacts = $courseobj->get_course_contacts();
        if (!empty($contacts)) {
            foreach ($contacts as $contact) {
                if (!empty($contact['user'])) {
                    $instructors[] = [
                        'user' => $contact['user'],
                        'role' => isset($contact['role']) ? $contact['role'] : null,
                        'rolename' => isset($contact['rolename']) ? $contact['rolename'] : '',
                    ];
                }
            }
        }
    }

    // 2. Direct DB Query fallback if course contacts are empty (very common when $CFG->coursecontact is not customized)
    if (empty($instructors) && $courseid > 0) {
        $coursecontext = \context_course::instance($courseid, IGNORE_MISSING);
        if ($coursecontext) {
            $sql = "SELECT u.id, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                           u.middlename, u.alternatename, u.imagealt, u.picture, u.email,
                           r.id as roleid, r.shortname as roleshortname, r.archetype as rolearchetype, r.name as customrolename
                      FROM {role_assignments} ra
                      JOIN {role} r ON r.id = ra.roleid
                      JOIN {user} u ON u.id = ra.userid
                     WHERE ra.contextid = :contextid
                       AND u.deleted = 0
                       AND (
                           r.archetype IN ('editingteacher', 'teacher', 'manager', 'coursecreator')
                           OR r.shortname IN ('editingteacher', 'teacher', 'manager', 'coursecreator', 'trainer', 'fasilitator', 'pengajar', 'instruktur')
                       )
                  ORDER BY CASE
                      WHEN r.archetype = 'editingteacher' OR r.shortname = 'editingteacher' THEN 1
                      WHEN r.archetype = 'teacher' OR r.shortname = 'teacher' THEN 2
                      WHEN r.archetype = 'manager' OR r.shortname = 'manager' THEN 3
                      WHEN r.archetype = 'coursecreator' OR r.shortname = 'coursecreator' THEN 4
                      ELSE 5
                  END, ra.id ASC";

            $records = $DB->get_records_sql($sql, ['contextid' => $coursecontext->id], 0, 3);
            if (!empty($records)) {
                foreach ($records as $rec) {
                    $u = (object)[
                        'id' => $rec->id,
                        'firstname' => $rec->firstname,
                        'lastname' => $rec->lastname,
                        'firstnamephonetic' => $rec->firstnamephonetic,
                        'lastnamephonetic' => $rec->lastnamephonetic,
                        'middlename' => $rec->middlename,
                        'alternatename' => $rec->alternatename,
                        'imagealt' => $rec->imagealt,
                        'picture' => $rec->picture,
                        'email' => $rec->email,
                    ];
                    $r = (object)[
                        'id' => $rec->roleid,
                        'shortname' => $rec->roleshortname,
                        'archetype' => $rec->rolearchetype,
                        'name' => $rec->customrolename,
                    ];
                    $instructors[] = [
                        'user' => $u,
                        'role' => $r,
                        'rolename' => !empty($rec->customrolename) ? $rec->customrolename : '',
                    ];
                }
            }
        }
    }

    // 3. Process the first instructor found
    if (!empty($instructors)) {
        $first = reset($instructors);
        $user = $first['user'];
        $role = $first['role'];

        $name = fullname($user);
        $archetype = ($role && !empty($role->archetype)) ? strtolower($role->archetype) : '';
        $shortname = ($role && !empty($role->shortname)) ? strtolower($role->shortname) : '';

        // Role to Icon Mapping
        // Primary Teacher / Editing Teacher -> fa-chalkboard-user
        // Non-editing Teacher / Tutor / Trainer -> fa-user-pen
        // Manager / Administrator -> fa-user-tie
        // Course Creator -> fa-graduation-cap
        $icon = $defaultteachericon;
        if ($archetype === 'editingteacher' || $shortname === 'editingteacher') {
            $icon = 'fa-chalkboard-user';
        } else if ($archetype === 'teacher' || $shortname === 'teacher' || $shortname === 'trainer' || $shortname === 'tutor' || $shortname === 'fasilitator') {
            $icon = 'fa-user-pen';
        } else if ($archetype === 'manager' || $shortname === 'manager' || $shortname === 'admin') {
            $icon = 'fa-user-tie';
        } else if ($archetype === 'coursecreator' || $shortname === 'coursecreator') {
            $icon = 'fa-graduation-cap';
        }

        // Role title resolution
        $rolename = '';
        if (!empty($first['rolename'])) {
            $rolename = $first['rolename'];
        } else if ($role) {
            $coursecontext = \context_course::instance($courseid, IGNORE_MISSING);
            if ($coursecontext && function_exists('role_get_name')) {
                $rolename = role_get_name($role, $coursecontext);
            } else if (!empty($role->name)) {
                $rolename = $role->name;
            }
        }
        if (empty($rolename)) {
            if ($archetype === 'editingteacher' || $shortname === 'editingteacher') {
                $rolename = get_string('role_teacher', 'theme_theedu');
            } else if ($archetype === 'teacher' || $shortname === 'teacher') {
                $rolename = get_string('role_noneditingteacher', 'theme_theedu');
            } else if ($archetype === 'manager' || $shortname === 'manager') {
                $rolename = get_string('role_manager', 'theme_theedu');
            }
        }

        // Avatar check
        $hasavatar = false;
        $avatarurl = '';
        if (!empty($user->picture)) {
            $userpic = new \user_picture($user);
            $userpic->size = 64;
            $avatarurl = $userpic->get_url($page)->out(false);
            $hasavatar = true;
        }

        return [
            'has_teacher' => true,
            'teacher_name' => $name,
            'teacher_role' => $rolename,
            'teacher_icon' => $icon,
            'teacher_avatar' => $avatarurl,
            'has_avatar' => $hasavatar,
        ];
    }

    // 4. Fallback if no instructor assigned to course
    return [
        'has_teacher' => false,
        'teacher_name' => get_string('selfpaced', 'theme_theedu'),
        'teacher_role' => '',
        'teacher_icon' => $defaultteachericon,
        'teacher_avatar' => '',
        'has_avatar' => false,
    ];
}

/**
 * Helper to evaluate visibility of frontpage components based on user status and role.
 *
 * @param string $visibility 'all', 'guest', 'loggedin', 'registered_only', 'admin', 'none'
 * @param bool $isloggedin
 * @return bool
 */
function theme_theedu_is_visible_to_user($visibility, $isloggedin) {
    if ($visibility === 'none') {
        return false;
    }
    if ($visibility === 'admin') {
        return is_siteadmin();
    }
    if ($visibility === 'registered_only') {
        return $isloggedin && !is_siteadmin();
    }
    if ($visibility === 'loggedin' && !$isloggedin) {
        return false;
    }
    if ($visibility === 'guest' && $isloggedin) {
        return false;
    }
    return true;
}

/**
 * Resolves thumbnail image URL for a course category.
 * Extracts embedded image from category description or retrieves uploaded image from file storage.
 *
 * @param int $categoryid Category ID
 * @param string $description Category description HTML/text
 * @return string Image URL or empty string if not found
 */
function theme_theedu_get_category_thumbnail_url($categoryid, $description = '') {
    global $CFG;

    // 1. Try to extract first img src from category description.
    if (!empty($description) && preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $description, $matches)) {
        return $matches[1];
    }

    // 2. Check Moodle File Storage for category description files.
    if (!empty($categoryid) && class_exists('\context_coursecat')) {
        try {
            $context = \context_coursecat::instance($categoryid, IGNORE_MISSING);
            if ($context) {
                $fs = get_file_storage();
                $files = $fs->get_area_files($context->id, 'coursecat', 'description', 0, 'itemid, filepath, filename', false);
                foreach ($files as $file) {
                    if ($file->is_valid_image()) {
                        return \moodle_url::make_pluginfile_url(
                            $file->get_contextid(),
                            $file->get_component(),
                            $file->get_filearea(),
                            $file->get_itemid(),
                            $file->get_filepath(),
                            $file->get_filename()
                        )->out(false);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silently ignore context or storage exceptions.
        }
    }

    return '';
}

/**
 * Returns dynamic fallback colorful gradient and icon for categories without images.
 *
 * @param int $index Category index or ID
 * @return array ['gradient' => string, 'icon' => string]
 */
function theme_theedu_get_category_fallback_gradient($index = 0) {
    $gradients = [
        0 => ['gradient' => 'linear-gradient(135deg, #4338CA 0%, #6366F1 50%, #818CF8 100%)', 'icon' => 'fa-graduation-cap'],
        1 => ['gradient' => 'linear-gradient(135deg, #0F766E 0%, #0D9488 50%, #14B8A6 100%)', 'icon' => 'fa-laptop-code'],
        2 => ['gradient' => 'linear-gradient(135deg, #6C5CE7 0%, #8075e8 50%, #a29bfe 100%)', 'icon' => 'fa-certificate'],
        3 => ['gradient' => 'linear-gradient(135deg, #D97706 0%, #F59E0B 50%, #FBBF24 100%)', 'icon' => 'fa-cogs'],
        4 => ['gradient' => 'linear-gradient(135deg, #0284C7 0%, #0EA5E9 50%, #38BDF8 100%)', 'icon' => 'fa-brain'],
        5 => ['gradient' => 'linear-gradient(135deg, #BE185D 0%, #E11D48 50%, #F43F5E 100%)', 'icon' => 'fa-award'],
        6 => ['gradient' => 'linear-gradient(135deg, #1E293B 0%, #334155 50%, #475569 100%)', 'icon' => 'fa-chart-pie'],
        7 => ['gradient' => 'linear-gradient(135deg, #B91C1C 0%, #DC2626 50%, #EF4444 100%)', 'icon' => 'fa-users'],
    ];
    $idx = abs((int) $index) % count($gradients);
    return $gradients[$idx];
}

/**
 * Generate a standalone crisp vector SVG flag or globe icon for a language code.
 * Eliminates missing emoji flag glyphs on Windows and guarantees crisp rendering across all browsers.
 *
 * @param string $lcode Language code (e.g. 'id', 'en', 'en_us', 'ja', 'ja_wp', etc.)
 * @return string Safe inline SVG HTML
 */
function theme_theedu_get_lang_flag_svg($lcode) {
    $code = strtolower(str_replace('-', '_', trim($lcode)));

    // 1. Indonesia (Red & White)
    if ($code === 'id' || strpos($code, 'id_') === 0) {
        return '<svg class="theedu-flag-svg" width="20" height="14" viewBox="0 0 20 14" style="border-radius:2px;overflow:hidden;" aria-hidden="true"><rect width="20" height="7" fill="#E70011"/><rect y="7" width="20" height="7" fill="#FFFFFF"/></svg>';
    }

    // 2. English (UK / US)
    if ($code === 'en_us') {
        return '<svg class="theedu-flag-svg" width="20" height="14" viewBox="0 0 38 20" style="border-radius:2px;overflow:hidden;" aria-hidden="true"><rect width="38" height="20" fill="#B22234"/><path d="M0,3h38 M0,6h38 M0,9h38 M0,12h38 M0,15h38 M0,18h38" stroke="#FFFFFF" stroke-width="1.5"/><rect width="16" height="11" fill="#3C3B6E"/></svg>';
    }
    if ($code === 'en' || strpos($code, 'en_') === 0) {
        return '<svg class="theedu-flag-svg" width="20" height="14" viewBox="0 0 60 30" style="border-radius:2px;overflow:hidden;" aria-hidden="true"><rect width="60" height="30" fill="#012169"/><path d="M0,0 L60,30 M60,0 L0,30" stroke="#FFFFFF" stroke-width="6"/><path d="M0,0 L60,30 M60,0 L0,30" stroke="#C8102E" stroke-width="2"/><path d="M30,0 v30 M0,15 h60" stroke="#FFFFFF" stroke-width="10"/><path d="M30,0 v30 M0,15 h60" stroke="#C8102E" stroke-width="6"/></svg>';
    }

    // 3. Japan (White background + Red circle) - includes ja, ja_wp (Workplace Japanese)
    if ($code === 'ja' || strpos($code, 'ja_') === 0) {
        return '<svg class="theedu-flag-svg" width="20" height="14" viewBox="0 0 20 14" style="border-radius:2px;overflow:hidden;" aria-hidden="true"><rect width="20" height="14" fill="#FFFFFF"/><circle cx="10" cy="7" r="4.2" fill="#BC002D"/></svg>';
    }

    // 4. Germany (Black, Red, Gold)
    if ($code === 'de' || strpos($code, 'de_') === 0) {
        return '<svg class="theedu-flag-svg" width="20" height="14" viewBox="0 0 20 14" style="border-radius:2px;overflow:hidden;" aria-hidden="true"><rect width="20" height="4.66" fill="#000000"/><rect y="4.66" width="20" height="4.66" fill="#DD0000"/><rect y="9.33" width="20" height="4.67" fill="#FFCE00"/></svg>';
    }

    // 5. France (Blue, White, Red)
    if ($code === 'fr' || strpos($code, 'fr_') === 0) {
        return '<svg class="theedu-flag-svg" width="20" height="14" viewBox="0 0 20 14" style="border-radius:2px;overflow:hidden;" aria-hidden="true"><rect width="6.66" height="14" fill="#002395"/><rect x="6.66" width="6.66" height="14" fill="#FFFFFF"/><rect x="13.33" width="6.67" height="14" fill="#ED2939"/></svg>';
    }

    // 6. Spain (Red, Yellow, Red)
    if ($code === 'es' || strpos($code, 'es_') === 0) {
        return '<svg class="theedu-flag-svg" width="20" height="14" viewBox="0 0 20 14" style="border-radius:2px;overflow:hidden;" aria-hidden="true"><rect width="20" height="3.5" fill="#AA151B"/><rect y="3.5" width="20" height="7" fill="#F1BF00"/><rect y="10.5" width="20" height="3.5" fill="#AA151B"/></svg>';
    }

    // 7. Italy (Green, White, Red)
    if ($code === 'it' || strpos($code, 'it_') === 0) {
        return '<svg class="theedu-flag-svg" width="20" height="14" viewBox="0 0 20 14" style="border-radius:2px;overflow:hidden;" aria-hidden="true"><rect width="6.66" height="14" fill="#009246"/><rect x="6.66" width="6.66" height="14" fill="#FFFFFF"/><rect x="13.33" width="6.67" height="14" fill="#CE2B37"/></svg>';
    }

    // 8. Netherlands (Red, White, Blue)
    if ($code === 'nl' || strpos($code, 'nl_') === 0) {
        return '<svg class="theedu-flag-svg" width="20" height="14" viewBox="0 0 20 14" style="border-radius:2px;overflow:hidden;" aria-hidden="true"><rect width="20" height="4.66" fill="#AE1C28"/><rect y="4.66" width="20" height="4.66" fill="#FFFFFF"/><rect y="9.33" width="20" height="4.67" fill="#21468B"/></svg>';
    }

    // 9. China (Red)
    if ($code === 'zh_cn' || $code === 'zh_tw' || strpos($code, 'zh') === 0) {
        return '<svg class="theedu-flag-svg" width="20" height="14" viewBox="0 0 20 14" style="border-radius:2px;overflow:hidden;" aria-hidden="true"><rect width="20" height="14" fill="#DE2910"/><circle cx="5" cy="4" r="1.5" fill="#FFDE00"/></svg>';
    }

    // Fallback: Crisp modern vector Globe icon
    return '<svg class="theedu-flag-svg theedu-flag-globe" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>';
}

/**
 * Prepares template context data for TheEdu layouts.
 *
 * @param moodle_page $page
 * @param bool|null $forcefrontpage force frontpage components if true
 * @return array
 */
function theme_theedu_get_template_context($page, $forcefrontpage = null) {
    global $CFG, $SITE;

    $context = [];

    // Dynamic color styles generated from database settings (immediate live update).
    $context['theedu_dynamic_colors'] = theme_theedu_get_inline_css();
    $context['theedu_brand_preset'] = theme_theedu_get_setting('brand_preset', 'default');

    // Web font URL for safe non-blocking HTML header enqueue (local woff2 used in SCSS, fallback online).
    $context['theedu_font_url'] = theme_theedu_get_font_url();

    // 1. Four Logo Positions (Kiri-Atas, Kanan-Atas, Kiri-Bawah, Kanan-Bawah).
    $logos = [
        'top_left' => [
            'enabled' => (bool) theme_theedu_get_setting('enable_logo_top_left', 1),
            'url' => theme_theedu_get_file_url('logo_top_left'),
            'link' => theme_theedu_get_setting('link_logo_top_left', $CFG->wwwroot),
            'alt' => theme_theedu_get_setting('alt_logo_top_left', format_string($SITE->fullname)),
            'height' => theme_theedu_get_setting('height_logo_top_left', '46'),
        ],
        'top_right' => [
            'enabled' => (bool) theme_theedu_get_setting('enable_logo_top_right', 0),
            'url' => theme_theedu_get_file_url('logo_top_right'),
            'link' => theme_theedu_get_setting('link_logo_top_right', ''),
            'alt' => theme_theedu_get_setting('alt_logo_top_right', 'Header Partner Logo'),
            'height' => theme_theedu_get_setting('height_logo_top_right', '42'),
        ],
        'bottom_left' => [
            'enabled' => (bool) theme_theedu_get_setting('enable_logo_bottom_left', 1),
            'url' => theme_theedu_get_file_url('logo_bottom_left'),
            'link' => theme_theedu_get_setting('link_logo_bottom_left', $CFG->wwwroot),
            'alt' => theme_theedu_get_setting('alt_logo_bottom_left', format_string($SITE->fullname)),
            'height' => theme_theedu_get_setting('height_logo_bottom_left', '42'),
        ],
        'bottom_right' => [
            'enabled' => (bool) theme_theedu_get_setting('enable_logo_bottom_right', 0),
            'url' => theme_theedu_get_file_url('logo_bottom_right'),
            'link' => theme_theedu_get_setting('link_logo_bottom_right', ''),
            'alt' => theme_theedu_get_setting('alt_logo_bottom_right', 'Footer Partner Logo'),
            'height' => theme_theedu_get_setting('height_logo_bottom_right', '38'),
        ],
    ];

    $context['theedu_logos'] = $logos;

    // Floating Header option.
    $context['theedu_floating_header'] = (bool) theme_theedu_get_setting('floating_header', 0);

    // Header Shortcuts (Profil, Pesan, Notifikasi, My Courses, Edit Mode).
    $isloggedinuser = isloggedin() && !isguestuser();
    $context['theedu_header'] = [
        'show_mycourses' => (bool) theme_theedu_get_setting('header_show_mycourses', 1) && $isloggedinuser,
        'show_notifications' => (bool) theme_theedu_get_setting('header_show_notifications', 1),
        'show_messages' => (bool) theme_theedu_get_setting('header_show_messages', 1),
        'show_user_profile' => (bool) theme_theedu_get_setting('header_show_user_profile', 1),
        'show_editmode' => (bool) theme_theedu_get_setting('header_show_editmode', 1),
        'mycourses_url' => $CFG->wwwroot . '/my/courses.php',
    ];

    // Login Background Image (Supports uploaded file or external image URL).
    $loginbg = theme_theedu_get_file_url('login_bg_image');
    if (empty($loginbg)) {
        $loginbg = theme_theedu_get_setting('login_bg_url');
    }
    $context['theedu_login_bg'] = $loginbg;

    // 2. Dark Mode configuration.
    $context['theedu_darkmode'] = [
        'enabled' => (bool) theme_theedu_get_setting('enable_darkmode', 1),
        'default' => theme_theedu_get_setting('darkmode_default', 'auto'),
    ];

    global $USER;

    // 3. Page Context Identification (Frontpage, Dashboard, Course).
    if ($forcefrontpage !== null) {
        $isfrontpage = (bool)$forcefrontpage;
    } else {
        $isfrontpage = ($page->pagelayout === 'frontpage') ||
                       ($page->pagetype === 'site-index') ||
                       (defined('SITEID') && !empty($page->course) && $page->course->id == SITEID && $page->pagelayout !== 'admin' && $page->pagetype !== 'my-index');
    }

    $isdashboard = ($page->pagelayout === 'mydashboard') || ($page->pagetype === 'my-index');
    $iscourse = ($page->pagelayout === 'course') || ($page->pagelayout === 'incourse');

    $context['is_frontpage'] = $isfrontpage;
    $context['is_dashboard'] = $isdashboard;
    $context['is_course'] = $iscourse;
    $context['user_fullname'] = isloggedin() ? fullname($USER) : '';
    $context['user_firstname'] = isloggedin() ? (!empty($USER->firstname) ? $USER->firstname : fullname($USER)) : get_string('role_learner', 'theme_theedu');
    if (function_exists('userdate')) {
        $context['user_date'] = userdate(time(), get_string('strftimedatefullshort', 'core_langconfig'));
    } else {
        $context['user_date'] = date('l, d F Y');
    }

    // Moove-inspired Live User Dashboard Stats & Course Metrics
    if (isloggedin() && !isguestuser()) {
        $context['user_avatar_html'] = !empty($OUTPUT) ? $OUTPUT->user_picture($USER, ['size' => 64, 'class' => 'theedu-hero-avatar rounded-circle']) : '';
        $context['user_role_label'] = is_siteadmin() ? get_string('role_admin', 'theme_theedu') : get_string('role_learner', 'theme_theedu');

        $usercourses = function_exists('enrol_get_users_courses') ? enrol_get_users_courses($USER->id, true) : [];
        if (!empty($usercourses)) {
            $usercourses = array_filter($usercourses, function($c) {
                return (int)$c->id !== SITEID;
            });
        }

        $enrolledcount   = count($usercourses);
        $completedcount  = 0;
        $inprogresscount = 0;
        $pastcount       = 0;
        $ongoingcount    = 0;
        $upcomingcount   = 0;

        $todaystart = strtotime('today midnight');
        $todayend   = strtotime('tomorrow midnight') - 1;

        if (!empty($CFG->enablecompletion)) {
            require_once($CFG->libdir . '/completionlib.php');
        }

        foreach ($usercourses as $ucourse) {
            // Timeline metrics:
            // Akan Datang (Upcoming): Scheduled after today
            // Lewat (Past): Ended before today
            // Berlangsung (Ongoing): Active today (including startdate = today)
            $startdate = !empty($ucourse->startdate) ? (int)$ucourse->startdate : 0;
            $enddate   = !empty($ucourse->enddate) ? (int)$ucourse->enddate : 0;

            if ($startdate > $todayend) {
                $upcomingcount++;
            } else if ($enddate > 0 && $enddate < $todaystart) {
                $pastcount++;
            } else {
                $ongoingcount++;
            }

            // Progress metrics:
            // Berjalan (In Progress): Course completion progress < 80%
            // Complete (Completed): Course completion progress >= 80%
            $iscompleted = false;

            if (class_exists('\core_completion\progress')) {
                $p = \core_completion\progress::get_course_progress_percentage($ucourse, $USER->id);
                if ($p !== null) {
                    $progval = (float)$p;
                    if ($progval >= 80.0) {
                        $iscompleted = true;
                    }
                }
            }

            if (!$iscompleted && !empty($CFG->enablecompletion)) {
                $cinfo = new \completion_info($ucourse);
                if ($cinfo->is_enabled() && $cinfo->is_course_complete($USER->id)) {
                    $iscompleted = true;
                }
            }

            if ($iscompleted) {
                $completedcount++;
            } else {
                $inprogresscount++;
            }
        }

        $context['user_course_count']     = $enrolledcount;
        $context['user_completed_count']  = $completedcount;
        $context['user_inprogress_count'] = $inprogresscount;
        $context['user_past_count']       = $pastcount;
        $context['user_ongoing_count']    = $ongoingcount;
        $context['user_upcoming_count']   = $upcomingcount;
        $context['user_has_courses']      = ($enrolledcount > 0);

        // Build Dynamic 5+ Cards based on administrator checkboxes and order
        $cards = [];

        // 1. Enrolled (Terdaftar)
        if (theme_theedu_get_setting('stat_card_enrolled_enable', 1)) {
            $cards[] = [
                'key'         => 'enrolled',
                'order'       => (int) theme_theedu_get_setting('stat_card_enrolled_order', 1),
                'count'       => $enrolledcount,
                'color_class' => 'text-primary',
                'label'       => get_string('stat_enrolled', 'theme_theedu'),
                'desc'        => get_string('stat_enrolled_desc', 'theme_theedu'),
            ];
        }

        // 2. In Progress (Berjalan < 80%)
        if (theme_theedu_get_setting('stat_card_inprogress_enable', 1)) {
            $cards[] = [
                'key'         => 'inprogress',
                'order'       => (int) theme_theedu_get_setting('stat_card_inprogress_order', 2),
                'count'       => $inprogresscount,
                'color_class' => 'text-info',
                'label'       => get_string('stat_inprogress', 'theme_theedu'),
                'desc'        => get_string('stat_inprogress_desc', 'theme_theedu'),
            ];
        }

        // 3. Completed (Selesai ≥ 80%)
        if (theme_theedu_get_setting('stat_card_completed_enable', 1)) {
            $cards[] = [
                'key'         => 'completed',
                'order'       => (int) theme_theedu_get_setting('stat_card_completed_order', 3),
                'count'       => $completedcount,
                'color_class' => 'text-success',
                'label'       => get_string('stat_completed', 'theme_theedu'),
                'desc'        => get_string('stat_completed_desc', 'theme_theedu'),
            ];
        }

        // 4. Ongoing (Berlangsung Hari Ini)
        if (theme_theedu_get_setting('stat_card_ongoing_enable', 1)) {
            $cards[] = [
                'key'         => 'ongoing',
                'order'       => (int) theme_theedu_get_setting('stat_card_ongoing_order', 4),
                'count'       => $ongoingcount,
                'color_class' => 'text-warning',
                'label'       => get_string('stat_ongoing', 'theme_theedu'),
                'desc'        => get_string('stat_ongoing_desc', 'theme_theedu'),
            ];
        }

        // 5. Upcoming (Akan Datang)
        if (theme_theedu_get_setting('stat_card_upcoming_enable', 1)) {
            $cards[] = [
                'key'         => 'upcoming',
                'order'       => (int) theme_theedu_get_setting('stat_card_upcoming_order', 5),
                'count'       => $upcomingcount,
                'color_class' => 'text-secondary',
                'label'       => get_string('stat_upcoming', 'theme_theedu'),
                'desc'        => get_string('stat_upcoming_desc', 'theme_theedu'),
            ];
        }

        // 6. Past (Lewat / Sudah Berakhir)
        if (theme_theedu_get_setting('stat_card_past_enable', 0)) {
            $cards[] = [
                'key'         => 'past',
                'order'       => (int) theme_theedu_get_setting('stat_card_past_order', 6),
                'count'       => $pastcount,
                'color_class' => 'text-muted',
                'label'       => get_string('stat_past', 'theme_theedu'),
                'desc'        => get_string('stat_past_desc', 'theme_theedu'),
            ];
        }

        usort($cards, function($a, $b) {
            return $a['order'] <=> $b['order'];
        });

        $context['theedu_dashboard_stat_cards'] = $cards;
        $context['has_dashboard_stat_cards']   = !empty($cards);
    } else {
        $context['user_avatar_html']            = '';
        $context['user_role_label']             = get_string('role_guest', 'theme_theedu');
        $context['user_course_count']           = 0;
        $context['user_completed_count']        = 0;
        $context['user_inprogress_count']       = 0;
        $context['user_past_count']             = 0;
        $context['user_ongoing_count']          = 0;
        $context['user_upcoming_count']         = 0;
        $context['user_has_courses']            = false;
        $context['theedu_dashboard_stat_cards'] = [];
        $context['has_dashboard_stat_cards']   = false;
    }

    $slidervis = theme_theedu_get_setting('slider_visibility', 'all');
    $enableslider = (bool) theme_theedu_get_setting('enable_slider', 1) && $isfrontpage && theme_theedu_is_visible_to_user($slidervis, $isloggedinuser);

    $slides = [];
    if ($enableslider) {
        $numslides = (int) theme_theedu_get_setting('slider_count', 3);
        if ($numslides < 1) {
            $numslides = 1;
        }
        $activefound = false;

        $fallbacks = [
            1 => [
                'title' => get_string('default_slide1_title', 'theme_theedu'),
                'caption' => get_string('default_slide1_caption', 'theme_theedu'),
                'btn' => get_string('default_slide1_btn', 'theme_theedu'),
            ],
            2 => [
                'title' => 'Transform Your Skills With Interactive Learning',
                'caption' => 'Access certified courses, track your personal milestones, and learn directly from industry mentors.',
                'btn' => 'Explore Courses',
            ],
            3 => [
                'title' => 'Modern Campus LMS on Desktop, Tablet & Mobile',
                'caption' => 'Enjoy dark mode, responsive activities, progress tracking, and pastel user interfaces.',
                'btn' => 'Start Learning',
            ],
        ];

        for ($i = 1; $i <= $numslides; $i++) {
            $slideenabled = (bool) theme_theedu_get_setting("slide_enable_{$i}", ($i === 1 ? 1 : 0));
            $imageurl = theme_theedu_get_file_url("slide_image_{$i}");
            $badge = theme_theedu_get_setting("slide_badge_{$i}", '');
            $title = theme_theedu_get_setting("slide_title_{$i}", '');
            $caption = theme_theedu_get_setting("slide_caption_{$i}", '');
            $btntext = theme_theedu_get_setting("slide_btn_text_{$i}", '');
            $btnurl = theme_theedu_get_setting("slide_btn_url_{$i}", '');

            // Fallback default sample content if fresh install or empty.
            if (empty(trim(strip_tags($title))) && isset($fallbacks[$i])) {
                $title = $fallbacks[$i]['title'];
            }
            if (empty(trim(strip_tags($caption))) && isset($fallbacks[$i])) {
                $caption = $fallbacks[$i]['caption'];
            }
            if (empty(trim($btntext)) && isset($fallbacks[$i])) {
                $btntext = $fallbacks[$i]['btn'];
            }
            if (empty(trim($btnurl))) {
                $btnurl = $CFG->wwwroot . '/course/index.php';
            }

            if ($slideenabled) {
                $isactive = !$activefound;
                if ($isactive) {
                    $activefound = true;
                }
                $hasimage = !empty($imageurl);
                $hascontent = !empty(trim(strip_tags($title))) || !empty(trim(strip_tags($caption))) || (!empty($btntext) && !empty($btnurl));
                $slides[] = [
                    'index' => count($slides),
                    'active' => $isactive,
                    'image' => $imageurl,
                    'has_image' => $hasimage,
                    'badge' => !empty($badge) ? format_text($badge, FORMAT_HTML) : '',
                    'has_badge' => !empty(trim(strip_tags($badge))),
                    'title' => !empty($title) ? format_text($title, FORMAT_HTML) : '',
                    'caption' => !empty($caption) ? format_text($caption, FORMAT_HTML) : '',
                    'has_content' => $hascontent,
                    'has_button' => !empty($btntext) && !empty($btnurl),
                    'button_text' => s($btntext),
                    'button_url' => clean_param($btnurl, PARAM_URL),
                ];
            }
        }

        // Safety guarantee: If no slide was enabled, activate Slide 1 sample automatically.
        if (empty($slides)) {
            $slides[] = [
                'index' => 0,
                'active' => true,
                'image' => '',
                'has_image' => false,
                'badge' => '',
                'has_badge' => false,
                'title' => format_text($fallbacks[1]['title'], FORMAT_HTML),
                'caption' => format_text($fallbacks[1]['caption'], FORMAT_HTML),
                'has_content' => true,
                'has_button' => true,
                'button_text' => s($fallbacks[1]['btn']),
                'button_url' => $CFG->wwwroot . '/course/index.php',
            ];
        }
    }

    $context['theedu_slider'] = [
        'enabled' => $enableslider && !empty($slides),
        'interval' => (int) theme_theedu_get_setting('slider_interval', 5000),
        'slides' => $slides,
        'has_multiple' => (count($slides) > 1),
    ];

    // Course Search Bar on Frontpage with Dynamic Popular Category Tags.
    $showsearchtags = (bool) theme_theedu_get_setting('search_show_tags', 1);
    $searchtags = [];
    if ($showsearchtags) {
        $topcats = [];
        try {
            if (class_exists('\core_course_category')) {
                if (method_exists('\core_course_category', 'top')) {
                    $top = \core_course_category::top();
                    if ($top && method_exists($top, 'get_children')) {
                        $topcats = $top->get_children();
                    }
                }
                if (empty($topcats) && method_exists('\core_course_category', 'get_all')) {
                    $topcats = \core_course_category::get_all();
                }
            }
        } catch (\Throwable $e) {
            $topcats = [];
        }

        if (!empty($topcats)) {
            $tagidx = 0;
            foreach ($topcats as $tcat) {
                if (is_object($tcat) && method_exists($tcat, 'is_uservisible') && $tcat->is_uservisible()) {
                    $cname = method_exists($tcat, 'get_formatted_name') ? $tcat->get_formatted_name() : (isset($tcat->name) ? $tcat->name : '');
                    if (!empty($cname)) {
                        $searchtags[] = [
                            'name' => $cname,
                            'url' => $CFG->wwwroot . '/course/search.php?search=' . urlencode($cname),
                        ];
                        $tagidx++;
                        if ($tagidx >= 5) {
                            break;
                        }
                    }
                }
            }
        }

        if (empty($searchtags)) {
            $searchtags = [
                ['name' => 'General Training', 'url' => $CFG->wwwroot . '/course/search.php?search=General'],
                ['name' => 'Technical', 'url' => $CFG->wwwroot . '/course/search.php?search=Technical'],
                ['name' => 'Safety', 'url' => $CFG->wwwroot . '/course/search.php?search=Safety'],
                ['name' => 'Management', 'url' => $CFG->wwwroot . '/course/search.php?search=Management'],
            ];
        }
    }

    $searchvis = theme_theedu_get_setting('search_visibility', 'all');
    $enablesearch = (bool) theme_theedu_get_setting('enable_course_search', 1) && $isfrontpage && theme_theedu_is_visible_to_user($searchvis, $isloggedinuser);

    $context['theedu_search'] = [
        'enabled' => $enablesearch,
        'heading' => theme_theedu_get_setting('search_heading', get_string('search_heading_default', 'theme_theedu')),
        'subheading' => theme_theedu_get_setting('search_subheading', get_string('search_subheading_default', 'theme_theedu')),
        'placeholder' => theme_theedu_get_setting('search_placeholder', get_string('search_placeholder_default', 'theme_theedu')),
        'has_tags' => !empty($searchtags),
        'tags' => $searchtags,
    ];

    // 4. Frontpage Course Categories / Highlights Grid (Dynamic Columns & Modern Cards).
    $catvis = theme_theedu_get_setting('categories_visibility', 'all');
    $enablecategories = (bool) theme_theedu_get_setting('enable_categories', 1) && $isfrontpage && theme_theedu_is_visible_to_user($catvis, $isloggedinuser);
    $categoryitems = [];
    $catcolumns = (int) theme_theedu_get_setting('category_columns', 4);
    if ($catcolumns < 2 || $catcolumns > 6) {
        $catcolumns = 4;
    }

    $enablecatthumbs = (bool) theme_theedu_get_setting('enable_category_thumbnails', 1);
    $catcardstyle = theme_theedu_get_setting('category_card_style', 'banner');
    $catthumbheight = (int) theme_theedu_get_setting('category_thumbnail_height', 140);
    if ($catthumbheight < 60 || $catthumbheight > 400) {
        $catthumbheight = 140;
    }
    $showcatcoursecount = (bool) theme_theedu_get_setting('show_category_course_count', 1);
    $showcatsubcatcount = (bool) theme_theedu_get_setting('show_category_subcat_count', 1);
    $showcatdesc = (bool) theme_theedu_get_setting('show_category_desc', 1);

    if ($enablecategories) {
        $catsource = theme_theedu_get_setting('category_source', 'custom');
        $pasteltones = [
            0 => ['bg' => 'rgba(108, 92, 231, 0.12)', 'icon' => '#6C5CE7'],
            1 => ['bg' => 'rgba(0, 206, 201, 0.12)',  'icon' => '#00A8A4'],
            2 => ['bg' => 'rgba(255, 118, 117, 0.12)', 'icon' => '#D63031'],
            3 => ['bg' => 'rgba(253, 203, 110, 0.22)', 'icon' => '#D68100'],
            4 => ['bg' => 'rgba(116, 185, 255, 0.15)', 'icon' => '#0984E3'],
            5 => ['bg' => 'rgba(253, 121, 168, 0.15)', 'icon' => '#E84393'],
        ];

        if ($catsource === 'moodle') {
            // Auto fetch Moodle course categories
            $topcategories = class_exists('\core_course_category') ? \core_course_category::get_all() : [];
            $count = 0;
            $maxcats = (int) theme_theedu_get_setting('category_count', 8);

            foreach ($topcategories as $cat) {
                if ($cat->is_uservisible()) {
                    $tone = $pasteltones[$count % count($pasteltones)];
                    $gradientinfo = theme_theedu_get_category_fallback_gradient($count);
                    $coursecount = $cat->get_courses_count();
                    $subcatcount = method_exists($cat, 'get_children_count') ? $cat->get_children_count() : count($cat->get_children());
                    $catthumb = theme_theedu_get_category_thumbnail_url($cat->id, $cat->description);
                    $rawdesc = strip_tags($cat->description);
                    if (strlen($rawdesc) > 120) {
                        $rawdesc = substr($rawdesc, 0, 117) . '...';
                    }
                    $categoryitems[] = [
                        'id' => $cat->id,
                        'icon' => $gradientinfo['icon'],
                        'title' => $cat->get_formatted_name(),
                        'desc' => $rawdesc,
                        'has_desc' => !empty(trim($rawdesc)) && $showcatdesc,
                        'url' => $CFG->wwwroot . '/course/index.php?categoryid=' . $cat->id,
                        'bg_color' => $tone['bg'],
                        'icon_color' => $tone['icon'],
                        'image_url' => $catthumb,
                        'has_image' => !empty($catthumb),
                        'fallback_gradient' => $gradientinfo['gradient'],
                        'course_count' => $coursecount,
                        'has_courses' => ($coursecount > 0) && $showcatcoursecount,
                        'subcat_count' => $subcatcount,
                        'has_subcats' => ($subcatcount > 0) && $showcatsubcatcount,
                        'is_subcategory' => false,
                    ];
                    $count++;
                    if ($count >= $maxcats) {
                        break;
                    }
                }
            }
        }

        // Fallback or Custom Fixed Categories (Also auto-fallbacks if Moodle has 0 categories)
        if (empty($categoryitems)) {
            $defaultcats = [
                1 => ['icon' => 'fa-laptop-code', 'title' => get_string('default_cat1_title', 'theme_theedu'), 'desc' => get_string('default_cat1_desc', 'theme_theedu')],
                2 => ['icon' => 'fa-certificate', 'title' => get_string('default_cat2_title', 'theme_theedu'), 'desc' => get_string('default_cat2_desc', 'theme_theedu')],
                3 => ['icon' => 'fa-users',       'title' => get_string('default_cat3_title', 'theme_theedu'), 'desc' => get_string('default_cat3_desc', 'theme_theedu')],
                4 => ['icon' => 'fa-moon',        'title' => get_string('default_cat4_title', 'theme_theedu'), 'desc' => get_string('default_cat4_desc', 'theme_theedu')],
                5 => ['icon' => 'fa-brain',       'title' => get_string('default_cat5_title', 'theme_theedu'), 'desc' => get_string('default_cat5_desc', 'theme_theedu')],
                6 => ['icon' => 'fa-chart-pie',   'title' => get_string('default_cat6_title', 'theme_theedu'), 'desc' => get_string('default_cat6_desc', 'theme_theedu')],
                7 => ['icon' => 'fa-palette',     'title' => get_string('default_cat7_title', 'theme_theedu'), 'desc' => get_string('default_cat7_desc', 'theme_theedu')],
                8 => ['icon' => 'fa-language',    'title' => get_string('default_cat8_title', 'theme_theedu'), 'desc' => get_string('default_cat8_desc', 'theme_theedu')],
            ];

            $numcats = (int) theme_theedu_get_setting('category_count', 4);
            if ($numcats < 1) {
                $numcats = 4;
            }
            for ($k = 1; $k <= $numcats; $k++) {
                $cicon = theme_theedu_get_setting("category_icon_{$k}", '');
                if (empty(trim($cicon)) && isset($defaultcats[$k])) {
                    $cicon = $defaultcats[$k]['icon'];
                }

                $ctitle = theme_theedu_get_setting("category_title_{$k}", '');
                if (empty(trim(strip_tags($ctitle))) && isset($defaultcats[$k])) {
                    $ctitle = $defaultcats[$k]['title'];
                }

                $cdesc = theme_theedu_get_setting("category_desc_{$k}", '');
                if (empty(trim(strip_tags($cdesc))) && isset($defaultcats[$k])) {
                    $cdesc = $defaultcats[$k]['desc'];
                }

                $curl = theme_theedu_get_setting("category_url_{$k}", '');
                if (empty(trim($curl))) {
                    $curl = $CFG->wwwroot . '/course/index.php';
                }

                $tone = $pasteltones[($k - 1) % count($pasteltones)];
                $gradientinfo = theme_theedu_get_category_fallback_gradient($k - 1);
                $categoryitems[] = [
                    'id' => $k,
                    'icon' => s($cicon),
                    'title' => format_text($ctitle, FORMAT_HTML),
                    'desc' => format_text($cdesc, FORMAT_HTML),
                    'has_desc' => !empty(trim(strip_tags($cdesc))) && $showcatdesc,
                    'url' => clean_param($curl, PARAM_URL),
                    'bg_color' => $tone['bg'],
                    'icon_color' => $tone['icon'],
                    'image_url' => '',
                    'has_image' => false,
                    'fallback_gradient' => $gradientinfo['gradient'],
                    'course_count' => 0,
                    'has_courses' => false,
                    'subcat_count' => 0,
                    'has_subcats' => false,
                    'is_subcategory' => false,
                ];
            }
        }
    }

    $catheading = theme_theedu_get_setting('category_heading', '');
    if (empty(trim($catheading))) {
        $catheading = get_string('categories_heading_default', 'theme_theedu');
    }
    $catsubheading = theme_theedu_get_setting('category_subheading', '');
    if (empty(trim($catsubheading))) {
        $catsubheading = get_string('categories_subheading_default', 'theme_theedu');
    }
    $catallurl = theme_theedu_get_setting('category_all_url', '');
    if (empty(trim($catallurl))) {
        $catallurl = $CFG->wwwroot . '/course/index.php';
    }

    $context['theedu_categories'] = [
        'enabled' => $enablecategories && !empty($categoryitems),
        'columns' => $catcolumns,
        'items' => $categoryitems,
        'heading' => format_text($catheading, FORMAT_HTML),
        'subheading' => format_text($catsubheading, FORMAT_HTML),
        'all_url' => $catallurl,
        'enable_thumbnails' => $enablecatthumbs && ($catcardstyle === 'banner'),
        'card_style_banner' => ($catcardstyle === 'banner'),
        'card_style_compact' => ($catcardstyle === 'compact') || !$enablecatthumbs,
        'thumbnail_height' => $catthumbheight . 'px',
        'show_course_count' => $showcatcoursecount,
        'show_subcat_count' => $showcatsubcatcount,
        'show_desc' => $showcatdesc,
    ];

    // 5. Frontpage Enrolled / Featured Courses.
    $coursevis = theme_theedu_get_setting('courses_visibility', 'all');
    $enablecourses = (bool) theme_theedu_get_setting('enable_frontpage_courses', 1) && $isfrontpage && theme_theedu_is_visible_to_user($coursevis, $isloggedinuser);
    $courseslist = [];
    $thumbmode = theme_theedu_get_setting('course_thumbnail_mode', 'image_or_icon');
    $defaulticon = theme_theedu_get_setting('course_default_icon', 'fa-graduation-cap');
    $maxcourses = (int) theme_theedu_get_setting('course_display_count', 6);
    $coursecols = (int) theme_theedu_get_setting('course_columns', 3);

    $colclass = 'col-lg-4 col-md-6';
    if ($coursecols === 2) {
        $colclass = 'col-lg-6 col-md-6';
    } else if ($coursecols === 4) {
        $colclass = 'col-lg-3 col-md-6';
    }

    if ($enablecourses) {
        $pastelgradients = [
            'linear-gradient(135deg, rgba(108, 92, 231, 0.15) 0%, rgba(0, 206, 201, 0.12) 100%)',
            'linear-gradient(135deg, rgba(0, 206, 201, 0.15) 0%, rgba(253, 203, 110, 0.18) 100%)',
            'linear-gradient(135deg, rgba(255, 118, 117, 0.15) 0%, rgba(253, 121, 168, 0.15) 100%)',
            'linear-gradient(135deg, rgba(116, 185, 255, 0.18) 0%, rgba(108, 92, 231, 0.12) 100%)',
        ];

        if ($isloggedinuser) {
            // =========================================================================
            // A. LOGGED-IN USERS: STRICTLY SHOW ONLY ACTIVELY ENROLLED COURSES!
            // =========================================================================
            $rawcourses = [];
            if (function_exists('enrol_get_users_courses')) {
                // $onlyactive = true enforces active user enrolment status and valid start/end dates.
                $rawcourses = enrol_get_users_courses($USER->id, true, 'id, fullname, shortname, category, summary, summaryformat, visible, sortorder', 'visible DESC, sortorder ASC');
            } else if (function_exists('enrol_get_my_courses')) {
                $rawcourses = enrol_get_my_courses(['id', 'fullname', 'shortname', 'category', 'summary', 'summaryformat'], 'visible DESC, sortorder ASC');
            }

            if (!empty($rawcourses)) {
                $idx = 0;
                foreach ($rawcourses as $c) {
                    // Safety check 1: Exclude front page course (SITEID).
                    if ((int)$c->id === SITEID) {
                        continue;
                    }

                    // Safety check 2: Strict active enrolment verification in course context.
                    $coursecontext = \context_course::instance($c->id, IGNORE_MISSING);
                    if ($coursecontext && function_exists('is_enrolled')) {
                        if (!is_enrolled($coursecontext, $USER->id, '', true)) {
                            // User is NOT actively enrolled in this course (e.g. suspended, expired, or non-enrolled role) - SKIP!
                            continue;
                        }
                    }

                    // Safety check 3: Course visibility.
                    if (!$c->visible && $coursecontext && !has_capability('moodle/course:viewhiddencourses', $coursecontext)) {
                        continue;
                    }

                    $courseobj = class_exists('\core_course_list_element') ? new \core_course_list_element($c) : null;

                    // Image extraction
                    $imageurl = '';
                    if ($courseobj) {
                        foreach ($courseobj->get_course_overviewfiles() as $file) {
                            if ($file->is_valid_image()) {
                                $imageurl = \moodle_url::make_file_url(
                                    '/pluginfile.php',
                                    '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                                    $file->get_filearea() . $file->get_filepath() . $file->get_filename(),
                                    false
                                )->out(false);
                                break;
                            }
                        }
                    }

                    $useicon = ($thumbmode === 'always_icon') || ($thumbmode === 'image_or_icon' && empty($imageurl));

                    // Category
                    $catname = '';
                    if ($c->category && class_exists('\core_course_category')) {
                        $cat = \core_course_category::get($c->category, IGNORE_MISSING);
                        if ($cat) {
                            $catname = $cat->get_formatted_name();
                        }
                    }

                    // Progress
                    $progress = null;
                    if (class_exists('\core_completion\progress')) {
                        $p = \core_completion\progress::get_course_progress_percentage($c);
                        if ($p !== null) {
                            $progress = round($p);
                        }
                    }

                    // Teachers (Robust extraction from course contacts & role assignments)
                    $inst = theme_theedu_get_course_instructor_details($c->id, $courseobj, $page);

                    $courseslist[] = [
                        'fullname' => format_string($c->fullname),
                        'url' => $CFG->wwwroot . '/course/view.php?id=' . $c->id,
                        'summary' => format_text($c->summary, $c->summaryformat),
                        'category_name' => $catname,
                        'image_url' => $imageurl,
                        'use_icon_thumb' => $useicon,
                        'thumb_icon' => $defaulticon,
                        'thumb_gradient' => $pastelgradients[$idx % count($pastelgradients)],
                        'has_progress' => ($progress !== null),
                        'progress' => $progress,
                        'has_teacher' => $inst['has_teacher'],
                        'teacher_name' => $inst['teacher_name'],
                        'teacher_role' => $inst['teacher_role'],
                        'teacher_icon' => $inst['teacher_icon'],
                        'teacher_avatar' => $inst['teacher_avatar'],
                        'has_avatar' => $inst['has_avatar'],
                    ];
                    $idx++;
                    if ($idx >= $maxcourses) {
                        break;
                    }
                }
            }

            // If user has 0 enrolled courses, check the configured empty behavior.
            $emptybehavior = theme_theedu_get_setting('courses_empty_behavior', 'empty_state');
            if (empty($courseslist) && $emptybehavior === 'show_featured') {
                $sitecourses = function_exists('get_courses') ? get_courses('all', 'c.sortorder ASC', 'c.id, c.fullname, c.shortname, c.category, c.summary, c.summaryformat, c.visible') : [];
                if (!empty($sitecourses)) {
                    unset($sitecourses[SITEID]);
                }
                if (!empty($sitecourses)) {
                    $idx = 0;
                    foreach ($sitecourses as $c) {
                        if (!$c->visible) {
                            continue;
                        }
                        $courseobj = class_exists('\core_course_list_element') ? new \core_course_list_element($c) : null;
                        $imageurl = '';
                        if ($courseobj) {
                            foreach ($courseobj->get_course_overviewfiles() as $file) {
                                if ($file->is_valid_image()) {
                                    $imageurl = \moodle_url::make_file_url(
                                        '/pluginfile.php',
                                        '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                                        $file->get_filearea() . $file->get_filepath() . $file->get_filename(),
                                        false
                                    )->out(false);
                                    break;
                                }
                            }
                        }
                        $useicon = ($thumbmode === 'always_icon') || ($thumbmode === 'image_or_icon' && empty($imageurl));
                        $catname = '';
                        if ($c->category && class_exists('\core_course_category')) {
                            $cat = \core_course_category::get($c->category, IGNORE_MISSING);
                            if ($cat) {
                                $catname = $cat->get_formatted_name();
                            }
                        }
                        $inst = theme_theedu_get_course_instructor_details($c->id, $courseobj, $page);
                        $courseslist[] = [
                            'fullname' => format_string($c->fullname),
                            'url' => $CFG->wwwroot . '/course/view.php?id=' . $c->id,
                            'summary' => format_text($c->summary, $c->summaryformat),
                            'category_name' => $catname,
                            'image_url' => $imageurl,
                            'use_icon_thumb' => $useicon,
                            'thumb_icon' => $defaulticon,
                            'thumb_gradient' => $pastelgradients[$idx % count($pastelgradients)],
                            'has_progress' => false,
                            'progress' => null,
                            'has_teacher' => $inst['has_teacher'],
                            'teacher_name' => $inst['teacher_name'],
                            'teacher_role' => $inst['teacher_role'],
                            'teacher_icon' => $inst['teacher_icon'],
                            'teacher_avatar' => $inst['teacher_avatar'],
                            'has_avatar' => $inst['has_avatar'],
                        ];
                        $idx++;
                        if ($idx >= $maxcourses) {
                            break;
                        }
                    }
                }
            }

        } else {
            // =========================================================================
            // B. GUEST / ANONYMOUS USERS: DISPLAY POPULAR / FEATURED SITE COURSES
            // =========================================================================
            $sitecourses = function_exists('get_courses') ? get_courses('all', 'c.sortorder ASC', 'c.id, c.fullname, c.shortname, c.category, c.summary, c.summaryformat, c.visible') : [];
            if (!empty($sitecourses)) {
                unset($sitecourses[SITEID]); // Exclude frontpage course
            }
            if (!empty($sitecourses)) {
                $idx = 0;
                foreach ($sitecourses as $c) {
                    if (!$c->visible) {
                        continue;
                    }
                    $courseobj = class_exists('\core_course_list_element') ? new \core_course_list_element($c) : null;

                    // Image extraction
                    $imageurl = '';
                    if ($courseobj) {
                        foreach ($courseobj->get_course_overviewfiles() as $file) {
                            if ($file->is_valid_image()) {
                                $imageurl = \moodle_url::make_file_url(
                                    '/pluginfile.php',
                                    '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                                    $file->get_filearea() . $file->get_filepath() . $file->get_filename(),
                                    false
                                )->out(false);
                                break;
                            }
                        }
                    }

                    $useicon = ($thumbmode === 'always_icon') || ($thumbmode === 'image_or_icon' && empty($imageurl));

                    // Category
                    $catname = '';
                    if ($c->category && class_exists('\core_course_category')) {
                        $cat = \core_course_category::get($c->category, IGNORE_MISSING);
                        if ($cat) {
                            $catname = $cat->get_formatted_name();
                        }
                    }

                    // Teachers (Robust extraction)
                    $inst = theme_theedu_get_course_instructor_details($c->id, $courseobj, $page);

                    $courseslist[] = [
                        'fullname' => format_string($c->fullname),
                        'url' => $CFG->wwwroot . '/course/view.php?id=' . $c->id,
                        'summary' => format_text($c->summary, $c->summaryformat),
                        'category_name' => $catname,
                        'image_url' => $imageurl,
                        'use_icon_thumb' => $useicon,
                        'thumb_icon' => $defaulticon,
                        'thumb_gradient' => $pastelgradients[$idx % count($pastelgradients)],
                        'has_progress' => false,
                        'progress' => null,
                        'has_teacher' => $inst['has_teacher'],
                        'teacher_name' => $inst['teacher_name'],
                        'teacher_role' => $inst['teacher_role'],
                        'teacher_icon' => $inst['teacher_icon'],
                        'teacher_avatar' => $inst['teacher_avatar'],
                        'has_avatar' => $inst['has_avatar'],
                    ];
                    $idx++;
                    if ($idx >= $maxcourses) {
                        break;
                    }
                }
            }

            // Fallback sample demo courses only for guests on a fresh empty install.
            if (empty($courseslist)) {
                $samplecourses = [
                    [
                        'fullname' => get_string('sample_course1_title', 'theme_theedu'),
                        'category_name' => get_string('sample_course1_cat', 'theme_theedu'),
                        'summary' => get_string('sample_course1_desc', 'theme_theedu'),
                        'thumb_icon' => 'fa-laptop-code',
                        'progress' => 65,
                        'teacher_name' => 'Budi Santoso, S.T.',
                        'teacher_role' => get_string('role_teacher', 'theme_theedu'),
                        'teacher_icon' => 'fa-chalkboard-user',
                    ],
                    [
                        'fullname' => get_string('sample_course2_title', 'theme_theedu'),
                        'category_name' => get_string('sample_course2_cat', 'theme_theedu'),
                        'summary' => get_string('sample_course2_desc', 'theme_theedu'),
                        'thumb_icon' => 'fa-database',
                        'progress' => 40,
                        'teacher_name' => 'Ahmad Fauzi, M.Kom.',
                        'teacher_role' => get_string('role_noneditingteacher', 'theme_theedu'),
                        'teacher_icon' => 'fa-user-pen',
                    ],
                    [
                        'fullname' => get_string('sample_course3_title', 'theme_theedu'),
                        'category_name' => get_string('sample_course3_cat', 'theme_theedu'),
                        'summary' => get_string('sample_course3_desc', 'theme_theedu'),
                        'thumb_icon' => 'fa-chart-pie',
                        'progress' => 85,
                        'teacher_name' => 'Deni Ferdiyanto, M.M.',
                        'teacher_role' => get_string('role_manager', 'theme_theedu'),
                        'teacher_icon' => 'fa-user-tie',
                    ],
                ];

                $idx = 0;
                foreach ($samplecourses as $sc) {
                    $courseslist[] = [
                        'fullname' => $sc['fullname'],
                        'url' => $CFG->wwwroot . '/course/index.php',
                        'summary' => $sc['summary'],
                        'category_name' => $sc['category_name'],
                        'image_url' => '',
                        'use_icon_thumb' => true,
                        'thumb_icon' => !empty($sc['thumb_icon']) ? $sc['thumb_icon'] : $defaulticon,
                        'thumb_gradient' => $pastelgradients[$idx % count($pastelgradients)],
                        'has_progress' => true,
                        'progress' => $sc['progress'],
                        'has_teacher' => true,
                        'teacher_name' => $sc['teacher_name'],
                        'teacher_role' => $sc['teacher_role'],
                        'teacher_icon' => $sc['teacher_icon'],
                        'teacher_avatar' => '',
                        'has_avatar' => false,
                    ];
                    $idx++;
                }
            }
        }
    }

    $emptybehavior = theme_theedu_get_setting('courses_empty_behavior', 'empty_state');
    $isdisplayingfeatured = ($isloggedinuser && empty($rawcourses) && $emptybehavior === 'show_featured') || !$isloggedinuser;

    $courseheading = theme_theedu_get_setting('course_heading', '');
    if (empty($courseheading)) {
        $courseheading = $isdisplayingfeatured ? get_string('featured_courses_heading', 'theme_theedu') : get_string('mycourses_heading', 'theme_theedu');
    }

    $coursesubheading = theme_theedu_get_setting('course_subheading', '');
    if (empty($coursesubheading)) {
        $coursesubheading = $isdisplayingfeatured ? get_string('featured_courses_subheading', 'theme_theedu') : get_string('mycourses_subheading', 'theme_theedu');
    }

    $isenabled = $enablecourses;
    if ($isloggedinuser && empty($courseslist) && $emptybehavior === 'hide_section') {
        $isenabled = false;
    }

    $context['theedu_courses'] = [
        'enabled' => $isenabled,
        'has_courses' => !empty($courseslist),
        'is_loggedin' => $isloggedinuser,
        'heading' => $courseheading,
        'subheading' => $coursesubheading,
        'view_all_url' => $CFG->wwwroot . '/course/index.php',
        'column_class' => $colclass,
        'courses' => $courseslist,
        'show_teacher' => (bool) theme_theedu_get_setting('course_show_teacher', 1),
        'show_teacher_role' => (bool) theme_theedu_get_setting('course_show_teacher_role', 1),
        'show_progress' => (bool) theme_theedu_get_setting('course_show_progress', 1),
        'show_category' => (bool) theme_theedu_get_setting('course_show_category', 1),
        'show_summary' => (bool) theme_theedu_get_setting('course_show_summary', 1),
        'show_thumbnail' => (bool) theme_theedu_get_setting('course_show_thumbnail', 1),
    ];

    // 6. Footer Settings.
    $middlelinksraw = theme_theedu_get_setting('footer_middle_links', '');
    if (empty(trim($middlelinksraw))) {
        $middlelinksraw = get_string('footer_middle_links_default', 'theme_theedu');
    }
    $issiteadmin = has_capability('moodle/site:config', \context_system::instance());
    $middlelinks = [];
    $middlelines = preg_split('/\r\n|\r|\n/', $middlelinksraw);
    foreach ($middlelines as $mline) {
        $mline = trim($mline);
        if (!empty($mline)) {
            $parts = explode('|', $mline, 2);
            $ltitle = trim($parts[0]);
            $lurl = isset($parts[1]) ? trim($parts[1]) : '#';
            if (strpos($lurl, 'http://') !== 0 && strpos($lurl, 'https://') !== 0 && strpos($lurl, '//') !== 0 && strpos($lurl, '#') !== 0) {
                if (strpos($lurl, '/') === 0) {
                    $lurl = $CFG->wwwroot . $lurl;
                } else {
                    $lurl = $CFG->wwwroot . '/' . $lurl;
                }
            }
            // Role restriction: hide Site Administration links from non-admins.
            if ((strpos($lurl, '/admin/') !== false || stripos($ltitle, 'Site Admin') !== false) && !$issiteadmin) {
                continue;
            }
            // Role restriction: hide Dashboard / private links from guests.
            if (strpos($lurl, '/my/') !== false && !$isloggedinuser) {
                continue;
            }
            $middlelinks[] = [
                'title' => s($ltitle),
                'url' => clean_param($lurl, PARAM_URL),
            ];
        }
    }

    $middletitle = theme_theedu_get_setting('footer_middle_title', '');
    if (empty(trim($middletitle))) {
        $middletitle = get_string('footer_middle_title_default', 'theme_theedu');
    }

    $bottomrightmode = theme_theedu_get_setting('footer_bottom_right_mode', 'custom');
    $bottomlinksraw = theme_theedu_get_setting('footer_bottom_right_links', '');
    if (empty(trim($bottomlinksraw))) {
        $bottomlinksraw = get_string('footer_bottom_right_links_default', 'theme_theedu');
    }
    $bottomlinks = [];
    $bottomlines = preg_split('/\r\n|\r|\n/', $bottomlinksraw);
    foreach ($bottomlines as $bline) {
        $bline = trim($bline);
        if (!empty($bline)) {
            $parts = explode('|', $bline, 2);
            $btitle = trim(rtrim(trim($parts[0]), ',;'));
            $burl = isset($parts[1]) ? trim($parts[1]) : '#';
            if (strpos($burl, 'http://') !== 0 && strpos($burl, 'https://') !== 0 && strpos($burl, '//') !== 0 && strpos($burl, '#') !== 0) {
                if (strpos($burl, '/') === 0) {
                    $burl = $CFG->wwwroot . $burl;
                } else {
                    $burl = $CFG->wwwroot . '/' . $burl;
                }
            }
            // Role restriction: hide Site Administration links from non-admins.
            if ((strpos($burl, '/admin/') !== false || stripos($btitle, 'Site Admin') !== false) && !$issiteadmin) {
                continue;
            }
            // Role restriction: hide Dashboard / private links from guests.
            if (strpos($burl, '/my/') !== false && !$isloggedinuser) {
                continue;
            }
            $bottomlinks[] = [
                'title' => s($btitle),
                'url' => clean_param($burl, PARAM_URL),
            ];
        }
    }

    $bottomrighttext = theme_theedu_get_setting('footer_bottom_right_text', '');
    $custombottomright = ($bottomrightmode === 'custom' || $bottomrightmode === 'both');
    $showstandardfooter = ($bottomrightmode === 'moodle' || $bottomrightmode === 'both');

    $context['theedu_footer'] = [
        'about_text' => format_text(theme_theedu_get_setting('footer_about', get_string('default_footer_about', 'theme_theedu')), FORMAT_HTML),
        'copyright' => format_text(theme_theedu_get_setting('footer_copyright', '© ' . date('Y') . ' ' . format_string($SITE->fullname) . '. All rights reserved.'), FORMAT_HTML),
        'middle_title' => format_text($middletitle, FORMAT_HTML),
        'middle_links' => $middlelinks,
        'has_middle_links' => !empty($middlelinks),
        'bottom_links' => $bottomlinks,
        'has_bottom_links' => !empty($bottomlinks),
        'bottom_right_text' => !empty(trim($bottomrighttext)) ? format_text($bottomrighttext, FORMAT_HTML) : '',
        'has_bottom_right_text' => !empty(trim($bottomrighttext)),
        'custom_bottom_right' => $custombottomright,
        'show_standard_footer' => $showstandardfooter,
        'facebook' => theme_theedu_get_setting('footer_facebook', ''),
        'twitter' => theme_theedu_get_setting('footer_twitter', ''),
        'instagram' => theme_theedu_get_setting('footer_instagram', ''),
        'youtube' => theme_theedu_get_setting('footer_youtube', ''),
        'linkedin' => theme_theedu_get_setting('footer_linkedin', ''),
    ];

    // Footer Language Selector (Dropdown dynamically derived from installed Moodle language packs)
    $showlangselector = (bool) theme_theedu_get_setting('footer_show_language_selector', 1);

    global $SESSION;
    $sesslang = !empty($SESSION->lang) ? $SESSION->lang : '';
    $userlang = (!empty($USER) && !empty($USER->lang)) ? $USER->lang : '';
    $currlang = function_exists('current_language') ? current_language() : 'en';

    $actislang = !empty($sesslang) ? $sesslang : (!empty($userlang) ? $userlang : $currlang);

    $translations = [];
    if (function_exists('get_string_manager')) {
        $translations = get_string_manager()->get_list_of_translations();
    }
    if (empty($translations)) {
        $translations = ['en' => 'English'];
    }
    // Guarantee English and Indonesia are available
    if (!isset($translations['en'])) {
        $translations['en'] = 'English';
    }
    if (!isset($translations['id'])) {
        $translations['id'] = 'Bahasa Indonesia';
    }

    $currenturl = !empty($page->url) ? $page->url : new \moodle_url('/');

    $flagmap = [
        'en'    => '🇬🇧',
        'en_us' => '🇺🇸',
        'id'    => '🇮🇩',
        'es'    => '🇪🇸',
        'fr'    => '🇫🇷',
        'de'    => '🇩🇪',
        'ja'    => '🇯🇵',
        'zh_cn' => '🇨🇳',
        'zh_tw' => '🇹🇼',
        'ar'    => '🇸🇦',
        'pt'    => '🇵🇹',
        'pt_br' => '🇧🇷',
        'ru'    => '🇷🇺',
        'ko'    => '🇰🇷',
        'it'    => '🇮🇹',
        'nl'    => '🇳🇱',
        'vi'    => '🇻🇳',
        'th'    => '🇹🇭',
        'ms'    => '🇲🇾',
    ];

    $langitems = [];
    $currentlangname = 'English';
    $currentlangflag = '🇬🇧';
    $currentlangflaghtml = theme_theedu_get_lang_flag_svg($actislang);

    foreach ($translations as $lcode => $lname) {
        $isactive = ($lcode === $actislang) || (strpos($actislang, $lcode) === 0);
        $cleanname = trim(preg_replace('/\s*\(.*?\)\s*/', '', $lname));
        if (empty($cleanname)) {
            $cleanname = $lname;
        }

        $basecode = strtolower(str_replace('-', '_', $lcode));
        $flag = isset($flagmap[$basecode]) ? $flagmap[$basecode] : '🌐';
        $flaghtml = theme_theedu_get_lang_flag_svg($lcode);

        if ($isactive) {
            $currentlangname = $cleanname;
            $currentlangflag = $flag;
            $currentlangflaghtml = $flaghtml;
        }

        $langurl = new \moodle_url($currenturl, ['lang' => $lcode]);

        $langitems[] = [
            'code'      => $lcode,
            'name'      => $cleanname,
            'raw_name'  => $lname,
            'flag'      => $flag,
            'flag_html' => $flaghtml,
            'url'       => $langurl->out(false),
            'active'    => $isactive,
        ];
    }

    $context['theedu_footer_lang'] = [
        'show_selector'     => $showlangselector && (count($langitems) >= 2),
        'current_lang'      => $actislang,
        'current_name'      => $currentlangname,
        'current_flag'      => $currentlangflag,
        'current_flag_html' => $currentlangflaghtml,
        'has_multiple'      => (count($langitems) >= 2),
        'languages'         => $langitems,
    ];

    // 7. Course Activity Bottom Navigation Bar (Prev, Jump-To, Next).
    $enableactivitynav = (bool) theme_theedu_get_setting('enable_activity_nav', 1);
    if ($enableactivitynav) {
        $context['theedu_activity_nav'] = theme_theedu_get_activity_navigation($page);
    } else {
        $context['theedu_activity_nav'] = ['has_nav' => false];
    }

    return $context;
}

/**
 * Helper to generate modern bottom activity navigation (Prev, Next, Jump-to dropdown).
 *
 * @param \moodle_page $page
 * @return array Context data for activity navigation.
 */
function theme_theedu_get_activity_navigation($page) {
    global $CFG, $COURSE;

    $emptyresult = ['has_nav' => false];

    // Must be in a valid course (not site course)
    $course = (!empty($page->course) && $page->course->id > 1) ? $page->course : $COURSE;
    if (empty($course) || !isset($course->id) || $course->id <= 1) {
        return $emptyresult;
    }

    // Must not be the course main page itself (/course/view.php)
    if ($page->pagetype === 'course-view' || $page->pagelayout === 'course') {
        // Only show inside activities, not on the course section list itself
        if (empty($page->cm) && (empty($page->context) || $page->context->contextlevel != CONTEXT_MODULE)) {
            return $emptyresult;
        }
    }

    // Try to get current course_module ($cm)
    $cm = !empty($page->cm) ? $page->cm : null;
    if (empty($cm) && !empty($page->context) && $page->context->contextlevel == CONTEXT_MODULE) {
        try {
            $modinfo = get_fast_modinfo($course);
            if (!empty($modinfo->cms[$page->context->instanceid])) {
                $cm = $modinfo->cms[$page->context->instanceid];
            }
        } catch (\Throwable $e) {
            $cm = null;
        }
    }

    // Also check optional_param id for mod views where $page->cm is not yet set
    if (empty($cm)) {
        $cmid = optional_param('id', 0, PARAM_INT);
        if ($cmid > 0) {
            try {
                $modinfo = get_fast_modinfo($course);
                if (!empty($modinfo->cms[$cmid])) {
                    $cm = $modinfo->cms[$cmid];
                }
            } catch (\Throwable $e) {
                $cm = null;
            }
        }
    }

    if (empty($cm)) {
        return $emptyresult;
    }

    try {
        $modinfo = get_fast_modinfo($course);
    } catch (\Throwable $e) {
        return $emptyresult;
    }

    $prevcm = null;
    $nextcm = null;
    $foundcurrent = false;
    $jumpsections = [];

    // Traverse visible sections and activities in natural course sequence
    $sections = $modinfo->get_section_info_all();
    foreach ($sections as $sectionnum => $section) {
        if (!$section->uservisible) {
            continue;
        }

        $sectionname = get_section_name($course, $section);
        $sectionactivities = [];

        if (!empty($modinfo->sections[$sectionnum])) {
            foreach ($modinfo->sections[$sectionnum] as $modnumber) {
                if (empty($modinfo->cms[$modnumber])) {
                    continue;
                }
                $othercm = $modinfo->cms[$modnumber];

                // Activity must be visible to user and not stealth
                if (!$othercm->uservisible || $othercm->is_stealth()) {
                    continue;
                }

                $iscurrent = ($othercm->id == $cm->id);

                if ($iscurrent) {
                    $foundcurrent = true;
                } else if (!$foundcurrent) {
                    $prevcm = $othercm;
                } else if ($foundcurrent && $nextcm === null) {
                    $nextcm = $othercm;
                }

                $acturl = $othercm->url ? $othercm->url->out(false) : '';
                if (!empty($acturl)) {
                    $sectionactivities[] = [
                        'id' => $othercm->id,
                        'name' => format_string($othercm->name),
                        'url' => $acturl,
                        'is_current' => $iscurrent,
                        'modname' => $othercm->modname,
                    ];
                }
            }
        }

        if (!empty($sectionactivities)) {
            $jumpsections[] = [
                'section_num' => $sectionnum,
                'section_name' => format_string($sectionname),
                'activities' => $sectionactivities,
            ];
        }
    }

    $courseurl = $CFG->wwwroot . '/course/view.php?id=' . $course->id;

    $hasprev = !empty($prevcm) && !empty($prevcm->url);
    $hasnext = !empty($nextcm) && !empty($nextcm->url);

    return [
        'has_nav' => true,
        'has_prev' => $hasprev,
        'prev_url' => $hasprev ? $prevcm->url->out(false) : $courseurl,
        'prev_name' => $hasprev ? format_string($prevcm->name) : format_string($course->fullname),
        'has_next' => $hasnext,
        'next_url' => $hasnext ? $nextcm->url->out(false) : $courseurl,
        'next_name' => $hasnext ? format_string($nextcm->name) : format_string($course->fullname),
        'course_url' => $courseurl,
        'course_name' => format_string($course->fullname),
        'jump_sections' => $jumpsections,
        'has_jump' => !empty($jumpsections),
    ];
}

/**
 * Generates the rich HTML dashboard for the "About Theme & Release Notes" settings tab.
 *
 * @return string HTML content for the about page.
 */
function theme_theedu_get_about_html() {
    global $CFG;

    // Dynamically introspect version metadata from version.php
    $version_number = '2026110906';
    $version_release = 'v1.5.7';
    $version_maturity = 'STABLE';
    $version_requires = '2024042200 (Moodle 4.4+)';

    $version_file = __DIR__ . '/version.php';
    if (file_exists($version_file)) {
        $plugin = new stdClass();
        include($version_file);
        if (!empty($plugin->version)) {
            $version_number = (string)$plugin->version;
        }
        if (!empty($plugin->release)) {
            $version_release = (string)$plugin->release;
        }
        if (defined('MATURITY_STABLE') && isset($plugin->maturity) && $plugin->maturity === MATURITY_STABLE) {
            $version_maturity = 'STABLE';
        }
        if (!empty($plugin->requires)) {
            $version_requires = $plugin->requires . ' (Moodle 4.4+)';
        }
    }

    $moodle_host = !empty($CFG->release) ? s($CFG->release) : 'Moodle 4.4, 4.5, 5.0, 5.1, 5.2, 5.3+';

    $html = '
    <div class="theedu-about-container">
        <style>
        .theedu-about-container {
            max-width: 1200px;
            margin: 0.5rem 0 2rem 0;
            line-height: 1.6;
        }
        .theedu-about-hero {
            background: linear-gradient(135deg, #1E1B4B 0%, #312E81 40%, #4338CA 75%, #6C5CE7 100%);
            border-radius: 1.5rem;
            padding: 2.5rem;
            color: #FFFFFF;
            position: relative;
            overflow: hidden;
            box-shadow: 0 12px 35px rgba(67, 56, 202, 0.25);
            margin-bottom: 2rem;
        }
        .theedu-about-hero::before {
            content: "";
            position: absolute;
            top: -60px;
            right: -60px;
            width: 280px;
            height: 280px;
            background: radial-gradient(circle, rgba(0, 206, 201, 0.28) 0%, rgba(255, 255, 255, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        .theedu-about-hero-content {
            position: relative;
            z-index: 2;
        }
        .theedu-about-logo-badge {
            width: 58px;
            height: 58px;
            border-radius: 1rem;
            background: linear-gradient(135deg, #00CEC9 0%, #6C5CE7 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.85rem;
            font-weight: 900;
            color: #FFFFFF;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            margin-bottom: 1.25rem;
        }
        .theedu-about-title {
            font-size: 2.25rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
            color: #FFFFFF !important;
        }
        .theedu-about-subtitle {
            font-size: 1.05rem;
            opacity: 0.92;
            max-width: 780px;
            margin-bottom: 1.5rem;
            line-height: 1.55;
            color: #E0E7FF;
        }
        .theedu-badge-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
            align-items: center;
        }
        .theedu-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.4rem 1rem;
            border-radius: 2rem;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.02em;
        }
        .theedu-pill-version {
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #FFFFFF;
        }
        .theedu-pill-status {
            background: #10B981;
            color: #FFFFFF;
        }
        .theedu-pill-license {
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #FFFFFF;
        }
        .theedu-pill-gh {
            background: #FFFFFF;
            color: #1E1B4B;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .theedu-pill-gh:hover {
            background: #00CEC9;
            color: #FFFFFF;
            transform: translateY(-2px);
        }
        .theedu-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.75rem;
            margin-bottom: 2.5rem;
        }
        .theedu-info-card {
            background: #FFFFFF;
            border: 1px solid rgba(108, 92, 231, 0.12);
            border-radius: 1.25rem;
            padding: 1.75rem;
            box-shadow: 0 4px 20px rgba(108, 92, 231, 0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .theedu-info-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(108, 92, 231, 0.08);
        }
        .theedu-info-card-header {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            margin-bottom: 1.25rem;
            padding-bottom: 0.85rem;
            border-bottom: 1px solid rgba(108, 92, 231, 0.08);
        }
        .theedu-card-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: rgba(108, 92, 231, 0.1);
            color: #6C5CE7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        .theedu-info-card-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1E293B;
            margin: 0;
        }
        .theedu-info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .theedu-info-table tr td {
            padding: 0.65rem 0;
            font-size: 0.92rem;
            border-bottom: 1px dashed rgba(108, 92, 231, 0.08);
            vertical-align: middle;
        }
        .theedu-info-table tr:last-child td {
            border-bottom: none;
        }
        .theedu-info-label {
            font-weight: 600;
            color: #64748B;
            width: 38%;
        }
        .theedu-info-val {
            font-weight: 600;
            color: #1E293B;
        }
        .theedu-release-section {
            background: #FFFFFF;
            border: 1px solid rgba(108, 92, 231, 0.12);
            border-radius: 1.25rem;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(108, 92, 231, 0.04);
        }
        .theedu-release-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.75rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(108, 92, 231, 0.1);
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .theedu-release-header h3 {
            font-size: 1.35rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            color: #1E293B;
            margin: 0;
        }
        .theedu-version-card {
            border: 1px solid rgba(108, 92, 231, 0.1);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            background: #FFFFFF;
            transition: all 0.2s ease;
        }
        .theedu-version-card:hover {
            border-color: #6C5CE7;
            box-shadow: 0 6px 20px rgba(108, 92, 231, 0.08);
        }
        .theedu-version-card.latest {
            border: 2px solid #6C5CE7;
            background: linear-gradient(180deg, rgba(108, 92, 231, 0.02) 0%, #FFFFFF 100%);
        }
        .theedu-version-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .theedu-version-tag {
            font-size: 1.15rem;
            font-weight: 800;
            color: #6C5CE7;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .theedu-version-date {
            font-size: 0.85rem;
            color: #64748B;
            font-weight: 500;
        }
        .theedu-release-badge {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 2px 8px;
            border-radius: 6px;
            display: inline-block;
            margin-right: 0.4rem;
            letter-spacing: 0.02em;
        }
        .badge-latest { background: #10B981; color: #FFFFFF; }
        .badge-new { background: rgba(16, 185, 129, 0.12); color: #059669; }
        .badge-fix { background: rgba(239, 68, 68, 0.12); color: #DC2626; }
        .badge-improve { background: rgba(59, 130, 246, 0.12); color: #2563EB; }
        .badge-ui { background: rgba(108, 92, 231, 0.12); color: #6C5CE7; }
        .theedu-version-card ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .theedu-version-card ul li {
            padding: 0.5rem 0;
            font-size: 0.92rem;
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            line-height: 1.5;
        }
        .theedu-version-card ul li i.bullet {
            color: #6C5CE7;
            font-size: 0.65rem;
            margin-top: 0.45rem;
            flex-shrink: 0;
        }
        .theedu-version-card ul li strong {
            color: #1E293B;
        }
        /* Dark Mode Compatibility */
        [data-theme="dark"] .theedu-about-container .theedu-info-card,
        [data-theme="dark"] .theedu-about-container .theedu-release-section,
        [data-theme="dark"] .theedu-about-container .theedu-version-card {
            background: #1E2638 !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
            color: #E2E8F0 !important;
        }
        [data-theme="dark"] .theedu-about-container .theedu-info-card-title,
        [data-theme="dark"] .theedu-about-container .theedu-release-header h3,
        [data-theme="dark"] .theedu-about-container .theedu-info-val,
        [data-theme="dark"] .theedu-about-container .theedu-version-card ul li strong {
            color: #F8FAFC !important;
        }
        [data-theme="dark"] .theedu-about-container .theedu-info-label,
        [data-theme="dark"] .theedu-about-container .theedu-version-date {
            color: #94A3B8 !important;
        }
        [data-theme="dark"] .theedu-about-container .theedu-version-card.latest {
            background: linear-gradient(180deg, rgba(108, 92, 231, 0.15) 0%, #1E2638 100%) !important;
            border-color: #6C5CE7 !important;
        }
        [data-theme="dark"] .theedu-about-container .theedu-info-table tr td {
            border-bottom-color: rgba(255, 255, 255, 0.06) !important;
        }
        @media (max-width: 768px) {
            .theedu-about-hero { padding: 1.5rem; }
            .theedu-about-title { font-size: 1.75rem; }
            .theedu-version-meta { flex-direction: column; align-items: flex-start; }
        }
        </style>

        <!-- Hero Header Card -->
        <div class="theedu-about-hero">
            <div class="theedu-about-hero-content">
                <div class="theedu-about-logo-badge">E</div>
                <h1 class="theedu-about-title">TheEdu Theme for Moodle</h1>
                <p class="theedu-about-subtitle">An enterprise-grade, accessible, and highly customizable learning theme engineered for Moodle 4.4 through 5.3+. Featuring fluid single-row navigation, soft pastel palettes, automatic dark mode, and dynamic frontpage modules.</p>
                <div class="theedu-badge-group">
                    <span class="theedu-pill theedu-pill-version"><i class="fa fa-tag"></i> Release ' . s($version_release) . '</span>
                    <span class="theedu-pill theedu-pill-status"><i class="fa fa-check-circle"></i> Build ' . s($version_number) . ' (' . s($version_maturity) . ')</span>
                    <span class="theedu-pill theedu-pill-license"><i class="fa fa-shield-alt"></i> GPL-3.0 License</span>
                    <a href="https://github.com/ferdiyantodeni/theme_theedu" target="_blank" rel="noopener noreferrer" class="theedu-pill theedu-pill-gh"><i class="fa fa-github fab fa-github"></i> GitHub Repository</a>
                </div>
            </div>
        </div>

        <!-- Two-Column Information Grid -->
        <div class="theedu-info-grid">
            <!-- Developer & Organization Card -->
            <div class="theedu-info-card">
                <div class="theedu-info-card-header">
                    <div class="theedu-card-icon"><i class="fa fa-code"></i></div>
                    <h4 class="theedu-info-card-title">Developer & Project Information</h4>
                </div>
                <table class="theedu-info-table">
                    <tr>
                        <td class="theedu-info-label">Author / Lead Dev</td>
                        <td class="theedu-info-val">Ferdiyanto Deni</td>
                    </tr>
                    <tr>
                        <td class="theedu-info-label">Organization</td>
                        <td class="theedu-info-val"><a href="https://gridiyans.my.id/" target="_blank" rel="noopener noreferrer" style="color: #6C5CE7; text-decoration: none; font-weight: 700;">Gridiyans Apps - Future Technology</a></td>
                    </tr>
                    <tr>
                        <td class="theedu-info-label">GitHub Profile</td>
                        <td class="theedu-info-val"><a href="https://github.com/ferdiyantodeni" target="_blank" rel="noopener noreferrer" style="color: #6C5CE7; text-decoration: none; font-weight: 700;">@ferdiyantodeni</a></td>
                    </tr>
                    <tr>
                        <td class="theedu-info-label">Project Repository</td>
                        <td class="theedu-info-val"><a href="https://github.com/ferdiyantodeni/theme_theedu" target="_blank" rel="noopener noreferrer" style="color: #6C5CE7; text-decoration: none; font-weight: 700;">github.com/ferdiyantodeni/theme_theedu</a></td>
                    </tr>
                    <tr>
                        <td class="theedu-info-label">Open Source License</td>
                        <td class="theedu-info-val">GNU General Public License v3.0 or later</td>
                    </tr>
                    <tr>
                        <td class="theedu-info-label">Asset Architecture</td>
                        <td class="theedu-info-val"><span style="color: #059669; font-weight: 700;"><i class="fa fa-check-circle"></i> 100% Self-Contained Offline Assets</span> (Local Fonts & Icons)</td>
                    </tr>
                </table>
            </div>

            <!-- System Specifications Card -->
            <div class="theedu-info-card">
                <div class="theedu-info-card-header">
                    <div class="theedu-card-icon"><i class="fa fa-server"></i></div>
                    <h4 class="theedu-info-card-title">System Specifications & Compatibility</h4>
                </div>
                <table class="theedu-info-table">
                    <tr>
                        <td class="theedu-info-label">Current Version</td>
                        <td class="theedu-info-val"><span style="background: rgba(108, 92, 231, 0.1); color: #6C5CE7; padding: 2px 8px; border-radius: 6px; font-weight: 700;">' . s($version_release) . ' (Build ' . s($version_number) . ')</span></td>
                    </tr>
                    <tr>
                        <td class="theedu-info-label">Moodle Compatibility</td>
                        <td class="theedu-info-val">Moodle 4.4, 4.5, 5.0, 5.1, 5.2, 5.3+</td>
                    </tr>
                    <tr>
                        <td class="theedu-info-label">Host Environment</td>
                        <td class="theedu-info-val">' . s($moodle_host) . '</td>
                    </tr>
                    <tr>
                        <td class="theedu-info-label">Base Architecture</td>
                        <td class="theedu-info-val">Moodle Boost Theme (<code>theme_boost</code>)</td>
                    </tr>
                    <tr>
                        <td class="theedu-info-label">CSS & UI Framework</td>
                        <td class="theedu-info-val">Bootstrap 5 + SCSS Custom Pipeline</td>
                    </tr>
                    <tr>
                        <td class="theedu-info-label">Icon Engine</td>
                        <td class="theedu-info-val">FontAwesome 6 Free (Offline Bundled)</td>
                    </tr>
                    <tr>
                        <td class="theedu-info-label">Dark Mode Engine</td>
                        <td class="theedu-info-val">CSS Custom Properties with Local Storage</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Professional Release Notes in English -->
        <div class="theedu-release-section">
            <div class="theedu-release-header">
                <h3><i class="fa fa-history" style="color: #6C5CE7;"></i> Release Notes & Changelog (English)</h3>
                <span style="font-size: 0.85rem; color: #64748B; font-weight: 500;">Comprehensive Version History</span>
            </div>

            <!-- Version 1.7.0 -->
            <div class="theedu-version-card latest">
                <div class="theedu-version-meta">
                    <div class="theedu-version-tag">
                        <i class="fa fa-cube"></i> v1.7.0
                        <span class="theedu-release-badge badge-latest">Current Release</span>
                    </div>
                    <div class="theedu-version-date"><i class="fa fa-calendar-alt"></i> November 10, 2026 &bull; Build 2026111007</div>
                </div>
                <ul>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-feature">New Feature</span>
                            <strong>Secondary Navigation & Tab Buttons Customization Suite:</strong> Added a full-fledged dedicated administration tab (<code>theme_theedu_secnav</code>) with 7 curated design presets (Modern Pill, Soft Rounded 8px, Minimal Clean Flat, Ghost Outline, Fresh Emerald, Executive Blue, Enclosed Bar Card) plus Custom mode.
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-ui">UI / UX</span>
                            <strong>Fine-Grained Geometry & Color Controls:</strong> Full control over button corner curvature (border radius e.g. <code>2rem</code>, <code>8px</code>, <code>4px</code>, <code>0px</code>), padding, font size, button gap, normal background/text/border, hover state colors, active gradient/solid colors, soft glow elevation shadows, bar background/padding, and alignment (left, center, right).
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-ui">UI / UX</span>
                            <strong>Instant Live Updates & Dark Mode Synchronization:</strong> Dynamically injects CSS variables and overrides into <code>:root</code>, allowing immediate visual updates without theme cache purging across course views, Site Administration, gradebook, and user profile tabs.
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Version 1.6.4 -->
            <div class="theedu-version-card">
                <div class="theedu-version-meta">
                    <div class="theedu-version-tag"><i class="fa fa-cube"></i> v1.6.4</div>
                    <div class="theedu-version-date"><i class="fa fa-calendar-alt"></i> November 10, 2026 &bull; Build 2026111006</div>
                </div>
                <ul>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-fix">Fixed</span>
                            <strong>Active Language Checkmark Display:</strong> Replaced font glyph icon with an unmistakable inline vector SVG checkmark (<code>stroke-width: 3</code>) enclosed in an accent circular badge, completely eliminating missing character glyph boxes (<code>[X]</code>) across all operating systems.
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-ui">UI / UX</span>
                            <strong>Native Vector SVG Country Flags:</strong> Replaced Unicode emoji regional indicator flags with crisp, dedicated inline SVG vector flags for Indonesia, United Kingdom, United States, Japan, Germany, France, Spain, and global fallback icons, completely resolving Windows emoji flag limitations (where flags rendered as jammed text characters like <code>ID</code>, <code>GB</code>, <code>JP</code>).
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-ui">UI / UX</span>
                            <strong>Language Dropdown Layout & Comma Cleanup:</strong> Expanded dropup menu container width to <code>min-width: 220px; max-width: 340px</code> for comfortable multi-byte scripts (e.g. Japanese), enforced zero link text-decoration, and sanitized trailing commas on custom footer links.
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Version 1.6.3 -->
            <div class="theedu-version-card">
                <div class="theedu-version-meta">
                    <div class="theedu-version-tag"><i class="fa fa-cube"></i> v1.6.3</div>
                    <div class="theedu-version-date"><i class="fa fa-calendar-alt"></i> November 10, 2026 &bull; Build 2026111005</div>
                </div>
                <ul>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-fix">Fixed</span>
                            <strong>Dropup Menu High-Contrast Text & Readability Resolution:</strong> Eliminated text blending inside the dropup language menu by enforcing dark slate <code>#1E293B</code> text color on light backgrounds and bright white <code>#F8FAFC</code> in dark mode via strict high-specificity CSS overrides, preventing inheritance collisions from <code>#page-footer a</code>.
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-ui">UI / UX</span>
                            <strong>Footer Navigation Spacing & Elegant Divider:</strong> Expanded spacing between footer bottom menu links (<code>gap: 1.25rem</code>) and added a subtle vertical divider line separating custom links from the language switcher to eliminate visual crowding.
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Version 1.6.2 -->
            <div class="theedu-version-card">
                <div class="theedu-version-meta">
                    <div class="theedu-version-tag"><i class="fa fa-cube"></i> v1.6.2</div>
                    <div class="theedu-version-date"><i class="fa fa-calendar-alt"></i> November 10, 2026 &bull; Build 2026111004</div>
                </div>
                <ul>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-new">New</span>
                            <strong>Dynamic Dropdown Language Selector:</strong> Transformed the footer language switcher into a compact, elegant Dropup menu. Language options dynamically read from all installed Moodle language packs (via <code>get_string_manager()->get_list_of_translations()</code>) with localized names, country flags, and active state indicators.
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-fix">Fixed</span>
                            <strong>Dashboard Page Footer Visibility Restoration:</strong> Resolved an issue where the footer failed to render on the dashboard (<code>/my/</code>) and course pages by placing <code>{{> theme_theedu/footer}}</code> inside the primary scrollable <code>#topofscroll</code> container in <code>templates/drawers.mustache</code>.
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Version 1.6.1 -->
            <div class="theedu-version-card">
                <div class="theedu-version-meta">
                    <div class="theedu-version-tag"><i class="fa fa-cube"></i> v1.6.1</div>
                    <div class="theedu-version-date"><i class="fa fa-calendar-alt"></i> November 10, 2026 &bull; Build 2026111003</div>
                </div>
                <ul>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-new">New</span>
                            <strong>Dynamic 5-Card Dashboard Statistics Architecture:</strong> Replaced preset mode dropdown with individual checkbox toggles and numerical display sequence order for all metric cards (Enrolled, In Progress [&lt;80%], Completed [&ge;80%], Ongoing [Today], Upcoming, Past). Administrators can freely enable any combination of cards and customize their left-to-right display order.
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-fix">Fixed</span>
                            <strong>Robust Session & User Language Switch Synchronization:</strong> Added <code>theme_theedu_page_init()</code> callback in <code>config.php</code> and <code>lib.php</code> to immediately capture <code>?lang=</code> requests, updating <code>$SESSION->lang</code>, <code>$USER->lang</code>, and user preferences even for authenticated users where default core Moodle ignores query parameters.
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-ui">UI / UX</span>
                            <strong>Footer Bottom Bar De-Cluttering & Anti-Overlap:</strong> Re-architected footer bottom layout into dedicated rows: main row with copyright and cleanly-aligned action capsule (custom links + language pill), and an isolated bottom row for standard Moodle system footer outputs, eliminating all element stacking and collisions.
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-improve">Updated</span>
                            <strong>Organization Metadata:</strong> Updated theme developer organization to <a href="https://gridiyans.my.id/" target="_blank" rel="noopener noreferrer" style="color: #6C5CE7; font-weight: 700;">Gridiyans Apps - Future Technology</a>.
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Version 1.6.0 -->
            <div class="theedu-version-card">
                <div class="theedu-version-meta">
                    <div class="theedu-version-tag"><i class="fa fa-cube"></i> v1.6.0</div>
                    <div class="theedu-version-date"><i class="fa fa-calendar-alt"></i> November 10, 2026 &bull; Build 2026111002</div>
                </div>
                <ul>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-new">New</span>
                            <strong>Dashboard Hero Course Metrics & Multi-Mode Display:</strong> Integrated explicit course metric calculations:
                            <br>&bull; <em>In Progress:</em> Enrolled courses with completion progress &lt; 80%.
                            <br>&bull; <em>Completed:</em> Enrolled courses with completion progress &ge; 80% (or marked complete via completion API).
                            <br>&bull; <em>Past:</em> Enrolled courses with end date prior to today midnight.
                            <br>&bull; <em>Ongoing:</em> Enrolled courses active today (start date &le; today end and not yet ended).
                            <br>&bull; <em>Upcoming:</em> Enrolled courses scheduled to start after today.
                            <br>&bull; <em>Enrolled:</em> Total active enrolled courses.
                            Added settings switch <code>dashboard_stats_mode</code> supporting Progress, Timeline, and Mixed modes.
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-fix">Fixed</span>
                            <strong>Zero-Mixed-Language Localization & English Default:</strong> Cleaned all hardcoded Indonesian strings from <code>templates/drawers.mustache</code> and <code>lib.php</code>. All dashboard labels, greetings, role badges, and descriptions now use proper Moodle language strings (<code>{{#str}}</code>) ensuring 100% professional English by default.
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-ui">UI / UX</span>
                            <strong>Footer Interactive Language Selector:</strong> Added an elegant pill-style language switcher in the footer bottom bar with flag indicators (🇬🇧 English [default] & 🇮🇩 Indonesia) allowing seamless 1-click language toggling.
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Version 1.5.9 -->
            <div class="theedu-version-card">
                <div class="theedu-version-meta">
                    <div class="theedu-version-tag"><i class="fa fa-cube"></i> v1.5.9</div>
                    <div class="theedu-version-date"><i class="fa fa-calendar-alt"></i> November 10, 2026 &bull; Build 2026111001</div>
                </div>
                <ul>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-fix">Fixed</span>
                            <strong>Strict Enrolled Courses Isolation:</strong> Fixed "My Enrolled Courses" section on the frontpage incorrectly showing un-enrolled courses by eliminating the runaway <code>get_courses(\'all\')</code> fallback for logged-in users and enforcing strict <code>is_enrolled($coursecontext, $USER->id, \'\', true)</code> access verification.
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Version 1.5.8 -->
            <div class="theedu-version-card">
                <div class="theedu-version-meta">
                    <div class="theedu-version-tag"><i class="fa fa-cube"></i> v1.5.8</div>
                    <div class="theedu-version-date"><i class="fa fa-calendar-alt"></i> November 9, 2026 &bull; Build 2026110907</div>
                </div>
                <ul>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-ui">UI / UX</span>
                            <strong>Floating Centered Login Card:</strong> Transformed login page into a modern centered floating card with compact responsive padding, frosted-glass backdrop, and centered brand logo, ensuring full visibility of background scenery without edge cutoffs.
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Version 1.5.7 -->
            <div class="theedu-version-card">
                <div class="theedu-version-meta">
                    <div class="theedu-version-tag"><i class="fa fa-cube"></i> v1.5.7</div>
                    <div class="theedu-version-date"><i class="fa fa-calendar-alt"></i> November 9, 2026 &bull; Build 2026110906</div>
                </div>
                <ul>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-fix">Fixed</span>
                            <strong>Login Page Cross-Browser Layout & Anti-Clipping (Edge & Chrome):</strong> Eliminated duplicate nested card wrappers around <code>output.main_content</code> in <code>templates/login.mustache</code> and added <code>overflow: visible !important; height: auto !important;</code>. This prevents Microsoft Edge and Chrome from cutting off guest login notices, password recovery links, or signup instructions.
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-fix">Fixed</span>
                            <strong>Login Background Image Rendering:</strong> Resolved <code>#page-wrapper</code> solid background occlusion by declaring <code>background: transparent !important</code> on <code>body.pagelayout-login #page-wrapper</code> and applying full-screen cover rendering with elegant glassmorphic dark gradients. Added alternative <code>login_bg_url</code> setting in Tab 1 (Branding).
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Version 1.5.6 -->
            <div class="theedu-version-card">
                <div class="theedu-version-meta">
                    <div class="theedu-version-tag"><i class="fa fa-cube"></i> v1.5.6</div>
                    <div class="theedu-version-date"><i class="fa fa-calendar-alt"></i> November 9, 2026 &bull; Build 2026110905</div>
                </div>
                <ul>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-fix">Fixed</span>
                            <strong>Moodle 4.5 Navbar Background Color Cascade Fix:</strong> Updated <code>scss/_header.scss</code> to use <code>var(--theedu-navbar-bg, rgba(255, 255, 255, 0.92))</code> instead of hardcoded RGBA, and amplified inline CSS specificity with <code>#page-wrapper.theedu-floating-header .navbar.fixed-top</code>, ensuring custom navbar colors apply reliably across both Moodle 4.5 LTS and Moodle 5.x.
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Version 1.5.5 -->
            <div class="theedu-version-card">
                <div class="theedu-version-meta">
                    <div class="theedu-version-tag"><i class="fa fa-cube"></i> v1.5.5</div>
                    <div class="theedu-version-date"><i class="fa fa-calendar-alt"></i> November 9, 2026 &bull; Build 2026110904</div>
                </div>
                <ul>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-fix">Fixed</span>
                            <strong>Raw HTML Dashboard Rendering Engine:</strong> Replaced <code>admin_setting_heading</code> with a custom <code>theme_theedu_admin_setting_about</code> class extending <code>admin_setting</code>. This bypasses Moodle\'s <code>format_text()</code> text filter and HTML Purifier, allowing the dashboard styles, gradient cards, and badges to compile and render as clean native HTML rather than raw code text.
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Version 1.5.4 -->
            <div class="theedu-version-card">
                <div class="theedu-version-meta">
                    <div class="theedu-version-tag"><i class="fa fa-cube"></i> v1.5.4</div>
                    <div class="theedu-version-date"><i class="fa fa-calendar-alt"></i> November 9, 2026 &bull; Build 2026110903</div>
                </div>
                <ul>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-fix">Fixed</span>
                            <strong>Settings Library Inclusion Fix:</strong> Added <code>require_once(__DIR__ . \'/lib.php\')</code> at the top of <code>settings.php</code> and guarded the dashboard invocation with <code>function_exists()</code>, permanently resolving the <code>Call to undefined function theme_theedu_get_about_html()</code> exception during Moodle admin tree building.
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-new">New</span>
                            <strong>Dedicated "About Theme & Release Notes" Settings Tab:</strong> Added Tab 10 in Site Administration theme settings providing real-time developer credentials, institution metadata, technical specifications, and categorized English release notes.
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Version 1.5.3 -->
            <div class="theedu-version-card">
                <div class="theedu-version-meta">
                    <div class="theedu-version-tag"><i class="fa fa-cube"></i> v1.5.3</div>
                    <div class="theedu-version-date"><i class="fa fa-calendar-alt"></i> November 9, 2026 &bull; Build 2026110902</div>
                </div>
                <ul>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-improve">Architecture</span>
                            <strong>Dynamic Version Introspection:</strong> Real-time reflection of current installed release version, build number, maturity status, and host Moodle compatibility directly from <code>version.php</code>.
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-ui">Documentation</span>
                            <strong>Comprehensive English Release Notes:</strong> Integrated enterprise-grade release history with categorized status badges (<code>[New]</code>, <code>[Fixed]</code>, <code>[Improved]</code>, <code>[UI / UX]</code>, <code>[Security]</code>) documenting all feature additions, UI enhancements, and security hardening.
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-improve">Offline</span>
                            <strong>Zero-CDN Architecture Verification:</strong> Audited all font, icon, and asset pipelines to certify 100% offline functionality in air-gapped intranet environments.
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Version 1.5.2 -->
            <div class="theedu-version-card">
                <div class="theedu-version-meta">
                    <div class="theedu-version-tag"><i class="fa fa-cube"></i> v1.5.2</div>
                    <div class="theedu-version-date"><i class="fa fa-calendar-alt"></i> November 9, 2026 &bull; Build 2026110901</div>
                </div>
                <ul>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-fix">Fixed</span>
                            <strong>Navbar Background Color Customization Resolution:</strong> Resolved an issue where <code>color_navbar_bg</code> and <code>color_navbar_text</code> were locked by the default preset palette. Custom color configurations now immediately take precedence without requiring switching to \'custom\' preset.
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-improve">Improved</span>
                            <strong>Customizable Carousel Slide Badge:</strong> Removed the hardcoded "TheEdu Learning" text badge from slider caption cards. Added a new setting <code>slide_badge_{$i}</code> in Tab 4 (Homepage Slider) allowing administrators to leave it empty (clean layout) or enter custom institution taglines.
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Version 1.5.1 -->
            <div class="theedu-version-card">
                <div class="theedu-version-meta">
                    <div class="theedu-version-tag"><i class="fa fa-cube"></i> v1.5.1</div>
                    <div class="theedu-version-date"><i class="fa fa-calendar-alt"></i> November 8, 2026 &bull; Build 2026110821</div>
                </div>
                <ul>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-ui">UI / UX</span>
                            <strong>Modern Category & Subcategory Card Architecture:</strong> Transformed traditional hierarchical Moodle category folder dropdowns into modern elevated cards matching the course card design system.
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-new">Feature</span>
                            <strong>Toggleable Category Thumbnail Image Area:</strong> Added support for category banners (extracting image from description or generating dynamic education gradients across 8 colorways). Added settings toggle (<code>enable_category_thumbnails</code>) to switch between thumbnail banner and compact icon mode.
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-improve">Improved</span>
                            <strong>Subcategory Feature Parity:</strong> Top-level categories and subcategories share identical visual cards, course count pills, subcategory count pills, descriptions, and circular action buttons.
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Version 1.5.0 -->
            <div class="theedu-version-card">
                <div class="theedu-version-meta">
                    <div class="theedu-version-tag"><i class="fa fa-cube"></i> v1.5.0</div>
                    <div class="theedu-version-date"><i class="fa fa-calendar-alt"></i> November 8, 2026 &bull; Build 2026110820</div>
                </div>
                <ul>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-new">Security</span>
                            <strong>Multi-Level Frontpage Block Visibility:</strong> Resolved navigation and administration block leakage for unauthenticated guest visitors. Added granular role visibility controls (All Visitors, Guests Only, Logged-in Users, Registered Non-Admin, Administrators Only, or Hidden).
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-improve">Improved</span>
                            <strong>Guest Navbar Menu Toggle:</strong> Added <code>navbar_guest_visibility</code> in Tab 1 allowing administrators to hide primary navigation items for guests, presenting an ultra-clean landing page with branding and login button.
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Version 1.4.7 - 1.4.9 -->
            <div class="theedu-version-card">
                <div class="theedu-version-meta">
                    <div class="theedu-version-tag"><i class="fa fa-cube"></i> v1.4.7 &ndash; v1.4.9</div>
                    <div class="theedu-version-date"><i class="fa fa-calendar-alt"></i> November 8, 2026 &bull; Builds 2026110817 &ndash; 2026110819</div>
                </div>
                <ul>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-ui">Layout</span>
                            <strong>Universal Spacing Standard (Tab 8):</strong> Standardized navbar-to-content distance (20px standard), side margins, card padding, and footer gap across Dashboard, My Courses, Course Content, and Site Admin.
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-new">Feature</span>
                            <strong>Dynamic Course Activity Navigation Bar:</strong> Added Next Activity, Previous Activity, and Jump-to-Topic navigation controls below course materials (PDF, Book, URL, Page), Quiz Finish Review, and Assignment submissions.
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-fix">Fixed</span>
                            <strong>Mobile Viewport Floating Menu Stacking:</strong> Resolved z-index and overflow clipping issues causing notification bell and profile popover menus to be occluded on mobile viewports.
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Version 1.4.0 - 1.4.6 -->
            <div class="theedu-version-card">
                <div class="theedu-version-meta">
                    <div class="theedu-version-tag"><i class="fa fa-cube"></i> v1.4.0 &ndash; v1.4.6</div>
                    <div class="theedu-version-date"><i class="fa fa-calendar-alt"></i> November 7 &ndash; 8, 2026 &bull; Builds 2026110701 &ndash; 2026110816</div>
                </div>
                <ul>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-ui">Layout</span>
                            <strong>Card Symmetry & Grid Parity:</strong> Synchronized card typography, thumbnail aspect ratio (16:9), teacher avatar pill, and progress bar layout between My Enrolled Courses and Category views with 50% tighter whitespace.
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-improve">Core</span>
                            <strong>4-Logo Multi-Branding Engine:</strong> Full independent management of Top-Left, Top-Right, Footer-Left, and Footer-Right logo placements.
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Version 1.0.0 - 1.3.5 -->
            <div class="theedu-version-card">
                <div class="theedu-version-meta">
                    <div class="theedu-version-tag"><i class="fa fa-cube"></i> v1.0.0 &ndash; v1.3.5</div>
                    <div class="theedu-version-date"><i class="fa fa-calendar-alt"></i> November 6, 2026 &bull; Foundation Releases</div>
                </div>
                <ul>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-improve">Offline</span>
                            <strong>100% Offline Font & Icon Architecture:</strong> Self-hosted Plus Jakarta Sans, Inter, Outfit, Poppins, Nunito, and Roboto fonts bundled locally in WOFF2 format along with FontAwesome 6 Free. Zero CDN dependency.
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-circle bullet"></i>
                        <div>
                            <span class="theedu-release-badge badge-new">Core</span>
                            <strong>Bootstrap 5 & Boost Engine:</strong> Engineered on Moodle Boost (<code>theme_boost</code>) with single-row floating navigation bar, 5 designer pastel presets, and instant flicker-free dark mode.
                        </div>
                    </li>
                </ul>
            </div>

        </div>

    </div>';

    return $html;
}
