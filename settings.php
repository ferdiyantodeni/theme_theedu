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
 * Settings for theme_theedu.
 *
 * @package    theme_theedu
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    // Top-level tabbed settings navigation.
    $settings = new theme_boost_admin_settingspage_tabs('themesettingtheedu', get_string('configtitle', 'theme_theedu'));

    // =========================================================================
    // TAB 1: BRANDING & 4 LOGO POSITIONS
    // =========================================================================
    $tab_branding = new admin_settingpage('theme_theedu_branding', get_string('tab_branding', 'theme_theedu'));

    // --- LOGO 1: TOP-LEFT (Kiri-Atas) ---
    $tab_branding->add(new admin_setting_heading('heading_logo_top_left',
        get_string('heading_logo_top_left', 'theme_theedu'),
        get_string('heading_logo_top_left_desc', 'theme_theedu')));

    $tab_branding->add(new admin_setting_configcheckbox('theme_theedu/enable_logo_top_left',
        get_string('enable_logo_top_left', 'theme_theedu'),
        get_string('enable_logo_top_left_desc', 'theme_theedu'), 1));

    $tab_branding->add(new admin_setting_configstoredfile('theme_theedu/logo_top_left',
        get_string('logo_top_left', 'theme_theedu'),
        get_string('logo_top_left_desc', 'theme_theedu'),
        'logo_top_left'));

    $tab_branding->add(new admin_setting_configtext('theme_theedu/height_logo_top_left',
        get_string('height_logo_top_left', 'theme_theedu'),
        get_string('height_logo_top_left_desc', 'theme_theedu'), '46', PARAM_INT));

    $tab_branding->add(new admin_setting_configtext('theme_theedu/link_logo_top_left',
        get_string('link_logo_top_left', 'theme_theedu'),
        get_string('link_logo_top_left_desc', 'theme_theedu'), '', PARAM_RAW_TRIMMED));

    $tab_branding->add(new admin_setting_configtext('theme_theedu/alt_logo_top_left',
        get_string('alt_logo_top_left', 'theme_theedu'),
        get_string('alt_logo_top_left_desc', 'theme_theedu'), '', PARAM_TEXT));

    // --- LOGO 2: TOP-RIGHT (Kanan-Atas) ---
    $tab_branding->add(new admin_setting_heading('heading_logo_top_right',
        get_string('heading_logo_top_right', 'theme_theedu'),
        get_string('heading_logo_top_right_desc', 'theme_theedu')));

    $tab_branding->add(new admin_setting_configcheckbox('theme_theedu/enable_logo_top_right',
        get_string('enable_logo_top_right', 'theme_theedu'),
        get_string('enable_logo_top_right_desc', 'theme_theedu'), 0));

    $tab_branding->add(new admin_setting_configstoredfile('theme_theedu/logo_top_right',
        get_string('logo_top_right', 'theme_theedu'),
        get_string('logo_top_right_desc', 'theme_theedu'),
        'logo_top_right'));

    $tab_branding->add(new admin_setting_configtext('theme_theedu/height_logo_top_right',
        get_string('height_logo_top_right', 'theme_theedu'),
        get_string('height_logo_top_right_desc', 'theme_theedu'), '42', PARAM_INT));

    $tab_branding->add(new admin_setting_configtext('theme_theedu/link_logo_top_right',
        get_string('link_logo_top_right', 'theme_theedu'),
        get_string('link_logo_top_right_desc', 'theme_theedu'), '', PARAM_RAW_TRIMMED));

    $tab_branding->add(new admin_setting_configtext('theme_theedu/alt_logo_top_right',
        get_string('alt_logo_top_right', 'theme_theedu'),
        get_string('alt_logo_top_right_desc', 'theme_theedu'), 'Header Right Logo', PARAM_TEXT));

    // --- LOGO 3: BOTTOM-LEFT (Kiri-Bawah) ---
    $tab_branding->add(new admin_setting_heading('heading_logo_bottom_left',
        get_string('heading_logo_bottom_left', 'theme_theedu'),
        get_string('heading_logo_bottom_left_desc', 'theme_theedu')));

    $tab_branding->add(new admin_setting_configcheckbox('theme_theedu/enable_logo_bottom_left',
        get_string('enable_logo_bottom_left', 'theme_theedu'),
        get_string('enable_logo_bottom_left_desc', 'theme_theedu'), 1));

    $tab_branding->add(new admin_setting_configstoredfile('theme_theedu/logo_bottom_left',
        get_string('logo_bottom_left', 'theme_theedu'),
        get_string('logo_bottom_left_desc', 'theme_theedu'),
        'logo_bottom_left'));

    $tab_branding->add(new admin_setting_configtext('theme_theedu/height_logo_bottom_left',
        get_string('height_logo_bottom_left', 'theme_theedu'),
        get_string('height_logo_bottom_left_desc', 'theme_theedu'), '42', PARAM_INT));

    $tab_branding->add(new admin_setting_configtext('theme_theedu/link_logo_bottom_left',
        get_string('link_logo_bottom_left', 'theme_theedu'),
        get_string('link_logo_bottom_left_desc', 'theme_theedu'), '', PARAM_RAW_TRIMMED));

    $tab_branding->add(new admin_setting_configtext('theme_theedu/alt_logo_bottom_left',
        get_string('alt_logo_bottom_left', 'theme_theedu'),
        get_string('alt_logo_bottom_left_desc', 'theme_theedu'), '', PARAM_TEXT));

    // --- LOGO 4: BOTTOM-RIGHT (Kanan-Bawah) ---
    $tab_branding->add(new admin_setting_heading('heading_logo_bottom_right',
        get_string('heading_logo_bottom_right', 'theme_theedu'),
        get_string('heading_logo_bottom_right_desc', 'theme_theedu')));

    $tab_branding->add(new admin_setting_configcheckbox('theme_theedu/enable_logo_bottom_right',
        get_string('enable_logo_bottom_right', 'theme_theedu'),
        get_string('enable_logo_bottom_right_desc', 'theme_theedu'), 0));

    $tab_branding->add(new admin_setting_configstoredfile('theme_theedu/logo_bottom_right',
        get_string('logo_bottom_right', 'theme_theedu'),
        get_string('logo_bottom_right_desc', 'theme_theedu'),
        'logo_bottom_right'));

    $tab_branding->add(new admin_setting_configtext('theme_theedu/height_logo_bottom_right',
        get_string('height_logo_bottom_right', 'theme_theedu'),
        get_string('height_logo_bottom_right_desc', 'theme_theedu'), '38', PARAM_INT));

    $tab_branding->add(new admin_setting_configtext('theme_theedu/link_logo_bottom_right',
        get_string('link_logo_bottom_right', 'theme_theedu'),
        get_string('link_logo_bottom_right_desc', 'theme_theedu'), '', PARAM_RAW_TRIMMED));

    $tab_branding->add(new admin_setting_configtext('theme_theedu/alt_logo_bottom_right',
        get_string('alt_logo_bottom_right', 'theme_theedu'),
        get_string('alt_logo_bottom_right_desc', 'theme_theedu'), 'Footer Right Logo', PARAM_TEXT));

    // Favicon & Login Background
    $tab_branding->add(new admin_setting_heading('heading_favicon',
        get_string('heading_favicon', 'theme_theedu'), ''));

    $tab_branding->add(new admin_setting_configstoredfile('theme_theedu/favicon',
        get_string('favicon', 'theme_theedu'),
        get_string('favicondesc', 'theme_theedu'),
        'favicon'));

    $tab_branding->add(new admin_setting_configstoredfile('theme_theedu/login_bg_image',
        get_string('login_bg_image', 'theme_theedu'),
        get_string('login_bg_image_desc', 'theme_theedu'),
        'login_bg_image'));

    // Floating Header Option
    $tab_branding->add(new admin_setting_heading('heading_floating_header',
        get_string('heading_floating_header', 'theme_theedu'),
        get_string('heading_floating_header_desc', 'theme_theedu')));

    $tab_branding->add(new admin_setting_configcheckbox('theme_theedu/floating_header',
        get_string('floating_header', 'theme_theedu'),
        get_string('floating_header_desc', 'theme_theedu'), 0));

    // Header Shortcuts (Profil, Setting, Pesan, Notifikasi, Edit Mode)
    $tab_branding->add(new admin_setting_heading('heading_header_shortcuts',
        get_string('heading_header_shortcuts', 'theme_theedu'),
        get_string('heading_header_shortcuts_desc', 'theme_theedu')));

    $tab_branding->add(new admin_setting_configcheckbox('theme_theedu/header_show_mycourses',
        get_string('header_show_mycourses', 'theme_theedu'),
        get_string('header_show_mycourses_desc', 'theme_theedu'), 1));

    $tab_branding->add(new admin_setting_configcheckbox('theme_theedu/header_show_notifications',
        get_string('header_show_notifications', 'theme_theedu'),
        get_string('header_show_notifications_desc', 'theme_theedu'), 1));

    $tab_branding->add(new admin_setting_configcheckbox('theme_theedu/header_show_messages',
        get_string('header_show_messages', 'theme_theedu'),
        get_string('header_show_messages_desc', 'theme_theedu'), 1));

    $tab_branding->add(new admin_setting_configcheckbox('theme_theedu/header_show_user_profile',
        get_string('header_show_user_profile', 'theme_theedu'),
        get_string('header_show_user_profile_desc', 'theme_theedu'), 1));

    $tab_branding->add(new admin_setting_configcheckbox('theme_theedu/header_show_editmode',
        get_string('header_show_editmode', 'theme_theedu'),
        get_string('header_show_editmode_desc', 'theme_theedu'), 1));

    $navbarguestopts = [
        'show' => get_string('nav_guest_show', 'theme_theedu'),
        'hide' => get_string('nav_guest_hide', 'theme_theedu'),
    ];
    $tab_branding->add(new admin_setting_configselect('theme_theedu/navbar_guest_visibility',
        get_string('navbar_guest_visibility', 'theme_theedu'),
        get_string('navbar_guest_visibility_desc', 'theme_theedu'), 'show', $navbarguestopts));

    $settings->add($tab_branding);

    // =========================================================================
    // TAB 2: COLOR SCHEME & AREA CUSTOMIZATION (PASTEL PALETTE)
    // =========================================================================
    $tab_colors = new admin_settingpage('theme_theedu_colors', get_string('tab_colors', 'theme_theedu'));

    // Typography & Google Fonts
    $tab_colors->add(new admin_setting_heading('heading_typography',
        get_string('heading_typography', 'theme_theedu'),
        get_string('heading_typography_desc', 'theme_theedu')));

    $fontoptions = [
        'plus_jakarta_sans' => 'Plus Jakarta Sans (Modern & Clean - Default)',
        'inter'             => 'Inter (Crisp & Professional)',
        'poppins'           => 'Poppins (Friendly & Rounded)',
        'nunito'            => 'Nunito (Soft Pastel Curves)',
        'outfit'            => 'Outfit (Contemporary & High-Tech)',
        'roboto'            => 'Roboto (Classic Clean)',
        'custom'            => 'Custom Google Font',
    ];
    $tab_colors->add(new admin_setting_configselect('theme_theedu/theme_font',
        get_string('theme_font', 'theme_theedu'),
        get_string('theme_font_desc', 'theme_theedu'), 'plus_jakarta_sans', $fontoptions));

    $tab_colors->add(new admin_setting_configtext('theme_theedu/custom_font_name',
        get_string('custom_font_name', 'theme_theedu'),
        get_string('custom_font_name_desc', 'theme_theedu'), '', PARAM_TEXT));

    $tab_colors->add(new admin_setting_configtext('theme_theedu/custom_font_url',
        get_string('custom_font_url', 'theme_theedu'),
        get_string('custom_font_url_desc', 'theme_theedu'), '', PARAM_RAW_TRIMMED));

    // Brand Color Presets (Pastel Themes)
    $tab_colors->add(new admin_setting_heading('heading_brand_presets',
        get_string('heading_brand_presets', 'theme_theedu'),
        get_string('heading_brand_presets_desc', 'theme_theedu')));

    $presetoptions = [
        'default' => get_string('preset_default', 'theme_theedu'),
        'red'     => get_string('preset_red', 'theme_theedu'),
        'blue'    => get_string('preset_blue', 'theme_theedu'),
        'green'   => get_string('preset_green', 'theme_theedu'),
        'white'   => get_string('preset_white', 'theme_theedu'),
        'grey'    => get_string('preset_grey', 'theme_theedu'),
        'yellow'  => get_string('preset_yellow', 'theme_theedu'),
        'custom'  => get_string('preset_custom', 'theme_theedu'),
    ];
    $tab_colors->add(new admin_setting_configselect('theme_theedu/brand_preset',
        get_string('brand_preset', 'theme_theedu'),
        get_string('brand_preset_desc', 'theme_theedu'), 'default', $presetoptions));

    $tab_colors->add(new admin_setting_heading('heading_color_overview',
        get_string('heading_color_overview', 'theme_theedu'),
        get_string('heading_color_overview_desc', 'theme_theedu')));

    // Primary Brand Pastel
    $tab_colors->add(new admin_setting_configcolourpicker('theme_theedu/color_primary',
        get_string('color_primary', 'theme_theedu'),
        get_string('color_primary_desc', 'theme_theedu'), '#6C5CE7'));

    // Secondary Accent Pastel
    $tab_colors->add(new admin_setting_configcolourpicker('theme_theedu/color_secondary',
        get_string('color_secondary', 'theme_theedu'),
        get_string('color_secondary_desc', 'theme_theedu'), '#00CEC9'));

    // Highlight / Badge Accent Pastel
    $tab_colors->add(new admin_setting_configcolourpicker('theme_theedu/color_accent',
        get_string('color_accent', 'theme_theedu'),
        get_string('color_accent_desc', 'theme_theedu'), '#FF7675'));

    // Area: Navbar / Header
    $tab_colors->add(new admin_setting_heading('heading_area_navbar',
        get_string('heading_area_navbar', 'theme_theedu'), ''));

    $tab_colors->add(new admin_setting_configcolourpicker('theme_theedu/color_navbar_bg',
        get_string('color_navbar_bg', 'theme_theedu'),
        get_string('color_navbar_bg_desc', 'theme_theedu'), '#FFFFFF'));

    $tab_colors->add(new admin_setting_configcolourpicker('theme_theedu/color_navbar_text',
        get_string('color_navbar_text', 'theme_theedu'),
        get_string('color_navbar_text_desc', 'theme_theedu'), '#2D3436'));

    // Area: Page Body & Content
    $tab_colors->add(new admin_setting_heading('heading_area_body',
        get_string('heading_area_body', 'theme_theedu'), ''));

    $tab_colors->add(new admin_setting_configcolourpicker('theme_theedu/color_body_bg',
        get_string('color_body_bg', 'theme_theedu'),
        get_string('color_body_bg_desc', 'theme_theedu'), '#F7F9FC'));

    $tab_colors->add(new admin_setting_configcolourpicker('theme_theedu/color_card_bg',
        get_string('color_card_bg', 'theme_theedu'),
        get_string('color_card_bg_desc', 'theme_theedu'), '#FFFFFF'));

    // Area: Footer
    $tab_colors->add(new admin_setting_heading('heading_area_footer',
        get_string('heading_area_footer', 'theme_theedu'), ''));

    $tab_colors->add(new admin_setting_configcolourpicker('theme_theedu/color_footer_bg',
        get_string('color_footer_bg', 'theme_theedu'),
        get_string('color_footer_bg_desc', 'theme_theedu'), '#2D3436'));

    $tab_colors->add(new admin_setting_configcolourpicker('theme_theedu/color_footer_text',
        get_string('color_footer_text', 'theme_theedu'),
        get_string('color_footer_text_desc', 'theme_theedu'), '#DFE6E9'));

    $settings->add($tab_colors);

    // =========================================================================
    // TAB 3: DARK MODE
    // =========================================================================
    $tab_darkmode = new admin_settingpage('theme_theedu_darkmode', get_string('tab_darkmode', 'theme_theedu'));

    $tab_darkmode->add(new admin_setting_heading('heading_darkmode',
        get_string('heading_darkmode', 'theme_theedu'),
        get_string('heading_darkmode_desc', 'theme_theedu')));

    $tab_darkmode->add(new admin_setting_configcheckbox('theme_theedu/enable_darkmode',
        get_string('enable_darkmode', 'theme_theedu'),
        get_string('enable_darkmode_desc', 'theme_theedu'), 1));

    $darkoptions = [
        'auto'  => get_string('darkmode_auto', 'theme_theedu'),
        'light' => get_string('darkmode_light', 'theme_theedu'),
        'dark'  => get_string('darkmode_dark', 'theme_theedu'),
    ];
    $tab_darkmode->add(new admin_setting_configselect('theme_theedu/darkmode_default',
        get_string('darkmode_default', 'theme_theedu'),
        get_string('darkmode_default_desc', 'theme_theedu'), 'auto', $darkoptions));

    // Dark Mode Palette Fine-tuning
    $tab_darkmode->add(new admin_setting_heading('heading_darkmode_colors',
        get_string('heading_darkmode_colors', 'theme_theedu'), ''));

    $tab_darkmode->add(new admin_setting_configcolourpicker('theme_theedu/dark_color_primary',
        get_string('dark_color_primary', 'theme_theedu'),
        get_string('dark_color_primary_desc', 'theme_theedu'), '#8E7CFF'));

    $tab_darkmode->add(new admin_setting_configcolourpicker('theme_theedu/dark_color_body_bg',
        get_string('dark_color_body_bg', 'theme_theedu'),
        get_string('dark_color_body_bg_desc', 'theme_theedu'), '#121824'));

    $tab_darkmode->add(new admin_setting_configcolourpicker('theme_theedu/dark_color_card_bg',
        get_string('dark_color_card_bg', 'theme_theedu'),
        get_string('dark_color_card_bg_desc', 'theme_theedu'), '#1E2638'));

    $tab_darkmode->add(new admin_setting_configcolourpicker('theme_theedu/dark_color_navbar_bg',
        get_string('dark_color_navbar_bg', 'theme_theedu'),
        get_string('dark_color_navbar_bg_desc', 'theme_theedu'), '#1A2130'));

    $settings->add($tab_darkmode);

    // =========================================================================
    // TAB 4: HOMEPAGE SLIDER (CAROUSEL)
    // =========================================================================
    $tab_slider = new admin_settingpage('theme_theedu_slider', get_string('tab_slider', 'theme_theedu'));

    $tab_slider->add(new admin_setting_heading('heading_slider_general',
        get_string('heading_slider_general', 'theme_theedu'),
        get_string('heading_slider_general_desc', 'theme_theedu')));

    $tab_slider->add(new admin_setting_configcheckbox('theme_theedu/enable_slider',
        get_string('enable_slider', 'theme_theedu'),
        get_string('enable_slider_desc', 'theme_theedu'), 1));

    $visoptions = [
        'all'             => get_string('vis_all', 'theme_theedu'),
        'guest'           => get_string('vis_guest', 'theme_theedu'),
        'loggedin'        => get_string('vis_loggedin', 'theme_theedu'),
        'registered_only' => get_string('vis_registered_only', 'theme_theedu'),
        'admin'           => get_string('vis_admin', 'theme_theedu'),
        'none'            => get_string('vis_none', 'theme_theedu'),
    ];
    $tab_slider->add(new admin_setting_configselect('theme_theedu/slider_visibility',
        get_string('slider_visibility', 'theme_theedu'),
        get_string('slider_visibility_desc', 'theme_theedu'), 'all', $visoptions));

    $slidercountoptions = [
        '1' => '1',
        '2' => '2',
        '3' => '3',
        '4' => '4',
        '5' => '5',
    ];
    $tab_slider->add(new admin_setting_configselect('theme_theedu/slider_count',
        get_string('slider_count', 'theme_theedu'),
        get_string('slider_count_desc', 'theme_theedu'), '3', $slidercountoptions));

    $tab_slider->add(new admin_setting_configtext('theme_theedu/slider_interval',
        get_string('slider_interval', 'theme_theedu'),
        get_string('slider_interval_desc', 'theme_theedu'), '5000', PARAM_INT));

    // Dynamic slides (1 to 5).
    for ($i = 1; $i <= 5; $i++) {
        $tab_slider->add(new admin_setting_heading("heading_slide_{$i}",
            get_string('slide_heading', 'theme_theedu', $i), ''));

        $defaultenable = ($i === 1) ? 1 : 0;
        $tab_slider->add(new admin_setting_configcheckbox("theme_theedu/slide_enable_{$i}",
            get_string('slide_enable', 'theme_theedu', $i), '', $defaultenable));

        $tab_slider->add(new admin_setting_configstoredfile("theme_theedu/slide_image_{$i}",
            get_string('slide_image', 'theme_theedu', $i),
            get_string('slide_image_desc', 'theme_theedu'),
            "slide_image_{$i}"));

        $defaulttitle = ($i === 1) ? get_string('default_slide1_title', 'theme_theedu') : '';
        $tab_slider->add(new admin_setting_configtext("theme_theedu/slide_title_{$i}",
            get_string('slide_title', 'theme_theedu', $i), '', $defaulttitle, PARAM_TEXT));

        $defaultcaption = ($i === 1) ? get_string('default_slide1_caption', 'theme_theedu') : '';
        $tab_slider->add(new admin_setting_configtextarea("theme_theedu/slide_caption_{$i}",
            get_string('slide_caption', 'theme_theedu', $i), '', $defaultcaption, PARAM_RAW_TRIMMED));

        $defaultbtntext = ($i === 1) ? get_string('default_slide1_btn', 'theme_theedu') : '';
        $tab_slider->add(new admin_setting_configtext("theme_theedu/slide_btn_text_{$i}",
            get_string('slide_btn_text', 'theme_theedu', $i), '', $defaultbtntext, PARAM_TEXT));

        $defaultbtnurl = ($i === 1) ? '/course/index.php' : '';
        $tab_slider->add(new admin_setting_configtext("theme_theedu/slide_btn_url_{$i}",
            get_string('slide_btn_url', 'theme_theedu', $i), '', $defaultbtnurl, PARAM_RAW_TRIMMED));
    }

    $settings->add($tab_slider);

    // =========================================================================
    // TAB 5: FRONTPAGE CATEGORIES & HIGHLIGHTS GRID
    // =========================================================================
    $tab_categories = new admin_settingpage('theme_theedu_categories', get_string('tab_categories', 'theme_theedu'));

    $tab_categories->add(new admin_setting_heading('heading_categories_general',
        get_string('heading_categories_general', 'theme_theedu'),
        get_string('heading_categories_general_desc', 'theme_theedu')));

    $tab_categories->add(new admin_setting_configcheckbox('theme_theedu/enable_categories',
        get_string('enable_categories', 'theme_theedu'),
        get_string('enable_categories_desc', 'theme_theedu'), 1));

    $tab_categories->add(new admin_setting_configselect('theme_theedu/categories_visibility',
        get_string('categories_visibility', 'theme_theedu'),
        get_string('categories_visibility_desc', 'theme_theedu'), 'all', $visoptions));

    $tab_categories->add(new admin_setting_configtext('theme_theedu/category_heading',
        get_string('category_heading', 'theme_theedu'),
        get_string('category_heading_desc', 'theme_theedu'),
        get_string('categories_heading_default', 'theme_theedu'), PARAM_TEXT));

    $tab_categories->add(new admin_setting_configtext('theme_theedu/category_subheading',
        get_string('category_subheading', 'theme_theedu'),
        get_string('category_subheading_desc', 'theme_theedu'),
        get_string('categories_subheading_default', 'theme_theedu'), PARAM_TEXT));

    $tab_categories->add(new admin_setting_configtext('theme_theedu/category_all_url',
        get_string('category_all_url', 'theme_theedu'),
        get_string('category_all_url_desc', 'theme_theedu'), '/course/index.php', PARAM_RAW_TRIMMED));

    // Dynamic Maximum Columns before wrapping to next row
    $columnoptions = [
        '2' => '2 ' . get_string('columns', 'theme_theedu'),
        '3' => '3 ' . get_string('columns', 'theme_theedu'),
        '4' => '4 ' . get_string('columns', 'theme_theedu'),
        '5' => '5 ' . get_string('columns', 'theme_theedu'),
        '6' => '6 ' . get_string('columns', 'theme_theedu'),
    ];
    $tab_categories->add(new admin_setting_configselect('theme_theedu/category_columns',
        get_string('category_columns', 'theme_theedu'),
        get_string('category_columns_desc', 'theme_theedu'), '4', $columnoptions));

    // Category Data Source: Custom Fixed Categories or Moodle Course Categories
    $sourceoptions = [
        'custom' => get_string('category_source_custom', 'theme_theedu'),
        'moodle' => get_string('category_source_moodle', 'theme_theedu'),
    ];
    $tab_categories->add(new admin_setting_configselect('theme_theedu/category_source',
        get_string('category_source', 'theme_theedu'),
        get_string('category_source_desc', 'theme_theedu'), 'custom', $sourceoptions));

    $catcountoptions = [
        '2' => '2',
        '3' => '3',
        '4' => '4',
        '5' => '5',
        '6' => '6',
        '7' => '7',
        '8' => '8',
    ];
    $tab_categories->add(new admin_setting_configselect('theme_theedu/category_count',
        get_string('category_count', 'theme_theedu'),
        get_string('category_count_desc', 'theme_theedu'), '4', $catcountoptions));

    // Category & Subcategory Card Presentation Settings (Unified Options)
    $tab_categories->add(new admin_setting_heading('heading_category_card_display',
        get_string('heading_category_card_display', 'theme_theedu'),
        get_string('heading_category_card_display_desc', 'theme_theedu')));

    $tab_categories->add(new admin_setting_configcheckbox('theme_theedu/enable_category_thumbnails',
        get_string('enable_category_thumbnails', 'theme_theedu'),
        get_string('enable_category_thumbnails_desc', 'theme_theedu'), 1));

    $catstyleoptions = [
        'banner' => get_string('category_style_banner', 'theme_theedu'),
        'compact' => get_string('category_style_compact', 'theme_theedu'),
    ];
    $tab_categories->add(new admin_setting_configselect('theme_theedu/category_card_style',
        get_string('category_card_style', 'theme_theedu'),
        get_string('category_card_style_desc', 'theme_theedu'), 'banner', $catstyleoptions));

    $tab_categories->add(new admin_setting_configtext('theme_theedu/category_thumbnail_height',
        get_string('category_thumbnail_height', 'theme_theedu'),
        get_string('category_thumbnail_height_desc', 'theme_theedu'), '140', PARAM_INT));

    $tab_categories->add(new admin_setting_configcheckbox('theme_theedu/show_category_course_count',
        get_string('show_category_course_count', 'theme_theedu'),
        get_string('show_category_course_count_desc', 'theme_theedu'), 1));

    $tab_categories->add(new admin_setting_configcheckbox('theme_theedu/show_category_subcat_count',
        get_string('show_category_subcat_count', 'theme_theedu'),
        get_string('show_category_subcat_count_desc', 'theme_theedu'), 1));

    $tab_categories->add(new admin_setting_configcheckbox('theme_theedu/show_category_desc',
        get_string('show_category_desc', 'theme_theedu'),
        get_string('show_category_desc_desc', 'theme_theedu'), 1));

    $defaulticons = [
        1 => 'fa-laptop-code',
        2 => 'fa-certificate',
        3 => 'fa-users',
        4 => 'fa-moon',
        5 => 'fa-brain',
        6 => 'fa-chart-pie',
        7 => 'fa-palette',
        8 => 'fa-language'
    ];

    for ($k = 1; $k <= 8; $k++) {
        $tab_categories->add(new admin_setting_heading("heading_category_{$k}",
            get_string('category_heading_item', 'theme_theedu', $k), ''));

        $tab_categories->add(new admin_setting_configtext("theme_theedu/category_icon_{$k}",
            get_string('category_icon', 'theme_theedu', $k),
            get_string('category_icon_desc', 'theme_theedu'), $defaulticons[$k], PARAM_TEXT));

        $catdeftitle = get_string("default_cat{$k}_title", 'theme_theedu');
        $tab_categories->add(new admin_setting_configtext("theme_theedu/category_title_{$k}",
            get_string('category_title', 'theme_theedu', $k), '', $catdeftitle, PARAM_TEXT));

        $catdefdesc = get_string("default_cat{$k}_desc", 'theme_theedu');
        $tab_categories->add(new admin_setting_configtext("theme_theedu/category_desc_{$k}",
            get_string('category_desc', 'theme_theedu', $k), '', $catdefdesc, PARAM_TEXT));

        $tab_categories->add(new admin_setting_configtext("theme_theedu/category_url_{$k}",
            get_string('category_url', 'theme_theedu', $k),
            get_string('category_url_desc', 'theme_theedu'), '/course/index.php', PARAM_RAW_TRIMMED));
    }

    $settings->add($tab_categories);

    // =========================================================================
    // TAB 6: ENROLLED COURSES & CARD PRESENTATION
    // =========================================================================
    $tab_courses = new admin_settingpage('theme_theedu_courses', get_string('tab_courses', 'theme_theedu'));

    // Frontpage Course Search Bar
    $tab_courses->add(new admin_setting_heading('heading_course_search',
        get_string('heading_course_search', 'theme_theedu'),
        get_string('heading_course_search_desc', 'theme_theedu')));

    $tab_courses->add(new admin_setting_configcheckbox('theme_theedu/enable_course_search',
        get_string('enable_course_search', 'theme_theedu'),
        get_string('enable_course_search_desc', 'theme_theedu'), 1));

    $tab_courses->add(new admin_setting_configselect('theme_theedu/search_visibility',
        get_string('search_visibility', 'theme_theedu'),
        get_string('search_visibility_desc', 'theme_theedu'), 'all', $visoptions));

    $tab_courses->add(new admin_setting_configtext('theme_theedu/search_heading',
        get_string('search_heading', 'theme_theedu'), '', '', PARAM_TEXT));

    $tab_courses->add(new admin_setting_configtext('theme_theedu/search_subheading',
        get_string('search_subheading', 'theme_theedu'), '', '', PARAM_TEXT));

    $tab_courses->add(new admin_setting_configtext('theme_theedu/search_placeholder',
        get_string('search_placeholder', 'theme_theedu'), '', '', PARAM_TEXT));

    $tab_courses->add(new admin_setting_configcheckbox('theme_theedu/search_show_tags',
        get_string('search_show_tags', 'theme_theedu'),
        get_string('search_show_tags_desc', 'theme_theedu'), 1));

    $tab_courses->add(new admin_setting_heading('heading_courses_general',
        get_string('heading_courses_general', 'theme_theedu'),
        get_string('heading_courses_general_desc', 'theme_theedu')));

    $tab_courses->add(new admin_setting_configcheckbox('theme_theedu/enable_frontpage_courses',
        get_string('enable_frontpage_courses', 'theme_theedu'),
        get_string('enable_frontpage_courses_desc', 'theme_theedu'), 1));

    $tab_courses->add(new admin_setting_configselect('theme_theedu/courses_visibility',
        get_string('courses_visibility', 'theme_theedu'),
        get_string('courses_visibility_desc', 'theme_theedu'), 'all', $visoptions));

    $tab_courses->add(new admin_setting_configcheckbox('theme_theedu/frontpage_hide_moodle_content',
        get_string('frontpage_hide_moodle_content', 'theme_theedu'),
        get_string('frontpage_hide_moodle_content_desc', 'theme_theedu'), 1));

    // Frontpage Blocks & Core Content Role Visibility
    $tab_courses->add(new admin_setting_heading('heading_frontpage_blocks',
        get_string('heading_frontpage_blocks', 'theme_theedu'),
        get_string('heading_frontpage_blocks_desc', 'theme_theedu')));

    $tab_courses->add(new admin_setting_configselect('theme_theedu/frontpage_blocks_visibility',
        get_string('frontpage_blocks_visibility', 'theme_theedu'),
        get_string('frontpage_blocks_visibility_desc', 'theme_theedu'), 'admin', $visoptions));

    $tab_courses->add(new admin_setting_configselect('theme_theedu/frontpage_content_visibility',
        get_string('frontpage_content_visibility', 'theme_theedu'),
        get_string('frontpage_content_visibility_desc', 'theme_theedu'), 'all', $visoptions));

    $tab_courses->add(new admin_setting_configcheckbox('theme_theedu/enable_catalog_card_grid',
        get_string('enable_catalog_card_grid', 'theme_theedu'),
        get_string('enable_catalog_card_grid_desc', 'theme_theedu'), 1));

    $tab_courses->add(new admin_setting_configtext('theme_theedu/course_heading',
        get_string('course_heading_setting', 'theme_theedu'),
        get_string('course_heading_setting_desc', 'theme_theedu'), '', PARAM_TEXT));

    $tab_courses->add(new admin_setting_configtext('theme_theedu/course_subheading',
        get_string('course_subheading_setting', 'theme_theedu'),
        get_string('course_subheading_setting_desc', 'theme_theedu'), '', PARAM_TEXT));

    $coursecountopts = ['3' => '3', '6' => '6', '9' => '9', '12' => '12'];
    $tab_courses->add(new admin_setting_configselect('theme_theedu/course_display_count',
        get_string('course_display_count', 'theme_theedu'),
        get_string('course_display_count_desc', 'theme_theedu'), '6', $coursecountopts));

    $coursecolopts = [
        '2' => '2 ' . get_string('columns', 'theme_theedu'),
        '3' => '3 ' . get_string('columns', 'theme_theedu'),
        '4' => '4 ' . get_string('columns', 'theme_theedu'),
    ];
    $tab_courses->add(new admin_setting_configselect('theme_theedu/course_columns',
        get_string('course_columns', 'theme_theedu'),
        get_string('course_columns_desc', 'theme_theedu'), '3', $coursecolopts));

    // Thumbnail & Icon Settings
    $tab_courses->add(new admin_setting_heading('heading_courses_thumbnail',
        get_string('heading_courses_thumbnail', 'theme_theedu'),
        get_string('heading_courses_thumbnail_desc', 'theme_theedu')));

    $tab_courses->add(new admin_setting_configcheckbox('theme_theedu/course_show_thumbnail',
        get_string('course_show_thumbnail', 'theme_theedu'),
        get_string('course_show_thumbnail_desc', 'theme_theedu'), 1));

    $thumbmodeopts = [
        'image_or_icon' => get_string('course_thumb_mode_auto', 'theme_theedu'),
        'always_icon'   => get_string('course_thumb_mode_icon', 'theme_theedu'),
        'image_only'    => get_string('course_thumb_mode_image', 'theme_theedu'),
    ];
    $tab_courses->add(new admin_setting_configselect('theme_theedu/course_thumbnail_mode',
        get_string('course_thumbnail_mode', 'theme_theedu'),
        get_string('course_thumbnail_mode_desc', 'theme_theedu'), 'image_or_icon', $thumbmodeopts));

    $tab_courses->add(new admin_setting_configtext('theme_theedu/course_default_icon',
        get_string('course_default_icon', 'theme_theedu'),
        get_string('course_default_icon_desc', 'theme_theedu'), 'fa-graduation-cap', PARAM_TEXT));

    // Card Details Display Settings (Teacher, Progress, Category, Summary)
    $tab_courses->add(new admin_setting_heading('heading_courses_elements',
        get_string('heading_courses_elements', 'theme_theedu'),
        get_string('heading_courses_elements_desc', 'theme_theedu')));

    $tab_courses->add(new admin_setting_configcheckbox('theme_theedu/course_show_teacher',
        get_string('course_show_teacher', 'theme_theedu'),
        get_string('course_show_teacher_desc', 'theme_theedu'), 1));

    $tab_courses->add(new admin_setting_configtext('theme_theedu/course_teacher_default_icon',
        get_string('course_teacher_default_icon', 'theme_theedu'),
        get_string('course_teacher_default_icon_desc', 'theme_theedu'), 'fa-chalkboard-user', PARAM_TEXT));

    $tab_courses->add(new admin_setting_configcheckbox('theme_theedu/course_show_teacher_role',
        get_string('course_show_teacher_role', 'theme_theedu'),
        get_string('course_show_teacher_role_desc', 'theme_theedu'), 1));

    $tab_courses->add(new admin_setting_configcheckbox('theme_theedu/course_show_progress',
        get_string('course_show_progress', 'theme_theedu'),
        get_string('course_show_progress_desc', 'theme_theedu'), 1));

    $tab_courses->add(new admin_setting_configcheckbox('theme_theedu/course_show_category',
        get_string('course_show_category', 'theme_theedu'),
        get_string('course_show_category_desc', 'theme_theedu'), 1));

    $tab_courses->add(new admin_setting_configcheckbox('theme_theedu/course_show_summary',
        get_string('course_show_summary', 'theme_theedu'),
        get_string('course_show_summary_desc', 'theme_theedu'), 1));

    $tab_courses->add(new admin_setting_heading('heading_activity_navigation',
        get_string('enable_activity_nav', 'theme_theedu'),
        get_string('enable_activity_nav_desc', 'theme_theedu')));

    $tab_courses->add(new admin_setting_configcheckbox('theme_theedu/enable_activity_nav',
        get_string('enable_activity_nav', 'theme_theedu'),
        get_string('enable_activity_nav_desc', 'theme_theedu'), 1));

    $settings->add($tab_courses);

    // =========================================================================
    // TAB 7: MARGIN & SPACING (Tata Letak, Lebar Konten & Spasi)
    // =========================================================================
    $tab_margin = new admin_settingpage('theme_theedu_margin', get_string('tab_margin', 'theme_theedu'));

    // 1. Jarak Atas (Top Spacing under Navbar)
    $tab_margin->add(new admin_setting_heading('heading_margin_top',
        get_string('heading_margin_top', 'theme_theedu'),
        get_string('heading_margin_top_desc', 'theme_theedu')));

    $tab_margin->add(new admin_setting_configtext('theme_theedu/content_margin_top',
        get_string('content_margin_top', 'theme_theedu'),
        get_string('content_margin_top_desc', 'theme_theedu'), '20', PARAM_INT));

    // 2. Lebar Konten & Sisi Kiri-Kanan (Width & Side Alignment)
    $tab_margin->add(new admin_setting_heading('heading_margin_width',
        get_string('heading_margin_width', 'theme_theedu'),
        get_string('heading_margin_width_desc', 'theme_theedu')));

    $tab_margin->add(new admin_setting_configcheckbox('theme_theedu/enable_full_width_content',
        get_string('enable_full_width_content', 'theme_theedu'),
        get_string('enable_full_width_content_desc', 'theme_theedu'), 1));

    $tab_margin->add(new admin_setting_configtext('theme_theedu/content_max_width',
        get_string('content_max_width', 'theme_theedu'),
        get_string('content_max_width_desc', 'theme_theedu'), '1440', PARAM_INT));

    $tab_margin->add(new admin_setting_configtext('theme_theedu/content_side_padding',
        get_string('content_side_padding', 'theme_theedu'),
        get_string('content_side_padding_desc', 'theme_theedu'), '20', PARAM_INT));

    // 3. Jarak Bawah ke Footer (Bottom Spacing above Footer)
    $tab_margin->add(new admin_setting_heading('heading_margin_bottom',
        get_string('heading_margin_bottom', 'theme_theedu'),
        get_string('heading_margin_bottom_desc', 'theme_theedu')));

    $tab_margin->add(new admin_setting_configtext('theme_theedu/content_margin_bottom',
        get_string('content_margin_bottom', 'theme_theedu'),
        get_string('content_margin_bottom_desc', 'theme_theedu'), '20', PARAM_INT));

    $tab_margin->add(new admin_setting_configtext('theme_theedu/content_card_padding',
        get_string('content_card_padding', 'theme_theedu'),
        get_string('content_card_padding_desc', 'theme_theedu'), '24', PARAM_INT));

    $settings->add($tab_margin);

    // =========================================================================
    // TAB 8: FOOTER & SOCIAL LINKS
    // =========================================================================
    $tab_footer = new admin_settingpage('theme_theedu_footer', get_string('tab_footer', 'theme_theedu'));

    $tab_footer->add(new admin_setting_heading('heading_footer_info',
        get_string('heading_footer_info', 'theme_theedu'), ''));

    $tab_footer->add(new admin_setting_configtextarea('theme_theedu/footer_about',
        get_string('footer_about', 'theme_theedu'),
        get_string('footer_about_desc', 'theme_theedu'), '', PARAM_RAW_TRIMMED));

    $tab_footer->add(new admin_setting_configtext('theme_theedu/footer_copyright',
        get_string('footer_copyright', 'theme_theedu'),
        get_string('footer_copyright_desc', 'theme_theedu'), '', PARAM_RAW_TRIMMED));

    // Middle Column: Quick Links & Custom Menu
    $tab_footer->add(new admin_setting_heading('heading_footer_middle',
        get_string('heading_footer_middle', 'theme_theedu'),
        get_string('heading_footer_middle_desc', 'theme_theedu')));

    $tab_footer->add(new admin_setting_configtext('theme_theedu/footer_middle_title',
        get_string('footer_middle_title', 'theme_theedu'),
        get_string('footer_middle_title_desc', 'theme_theedu'),
        get_string('footer_middle_title_default', 'theme_theedu'), PARAM_TEXT));

    $tab_footer->add(new admin_setting_configtextarea('theme_theedu/footer_middle_links',
        get_string('footer_middle_links', 'theme_theedu'),
        get_string('footer_middle_links_desc', 'theme_theedu'),
        get_string('footer_middle_links_default', 'theme_theedu'), PARAM_RAW_TRIMMED));

    // Bottom-Right Corner: Custom Links, Moodle Default, or Both
    $tab_footer->add(new admin_setting_heading('heading_footer_bottom_right',
        get_string('heading_footer_bottom_right', 'theme_theedu'),
        get_string('heading_footer_bottom_right_desc', 'theme_theedu')));

    $bottomrightmodes = [
        'custom' => get_string('footer_bottom_right_custom_mode', 'theme_theedu'),
        'moodle' => get_string('footer_bottom_right_moodle_mode', 'theme_theedu'),
        'both'   => get_string('footer_bottom_right_both_mode', 'theme_theedu'),
        'none'   => get_string('footer_bottom_right_none_mode', 'theme_theedu'),
    ];
    $tab_footer->add(new admin_setting_configselect('theme_theedu/footer_bottom_right_mode',
        get_string('footer_bottom_right_mode', 'theme_theedu'),
        get_string('footer_bottom_right_mode_desc', 'theme_theedu'),
        'custom', $bottomrightmodes));

    $tab_footer->add(new admin_setting_configtextarea('theme_theedu/footer_bottom_right_links',
        get_string('footer_bottom_right_links', 'theme_theedu'),
        get_string('footer_bottom_right_links_desc', 'theme_theedu'),
        get_string('footer_bottom_right_links_default', 'theme_theedu'), PARAM_RAW_TRIMMED));

    $tab_footer->add(new admin_setting_configtextarea('theme_theedu/footer_bottom_right_text',
        get_string('footer_bottom_right_text', 'theme_theedu'),
        get_string('footer_bottom_right_text_desc', 'theme_theedu'),
        '', PARAM_RAW_TRIMMED));

    $tab_footer->add(new admin_setting_heading('heading_footer_social',
        get_string('heading_footer_social', 'theme_theedu'), ''));

    $tab_footer->add(new admin_setting_configtext('theme_theedu/footer_facebook',
        'Facebook URL', '', '', PARAM_RAW_TRIMMED));

    $tab_footer->add(new admin_setting_configtext('theme_theedu/footer_twitter',
        'X (Twitter) URL', '', '', PARAM_RAW_TRIMMED));

    $tab_footer->add(new admin_setting_configtext('theme_theedu/footer_instagram',
        'Instagram URL', '', '', PARAM_RAW_TRIMMED));

    $tab_footer->add(new admin_setting_configtext('theme_theedu/footer_youtube',
        'YouTube URL', '', '', PARAM_RAW_TRIMMED));

    $tab_footer->add(new admin_setting_configtext('theme_theedu/footer_linkedin',
        'LinkedIn URL', '', '', PARAM_RAW_TRIMMED));

    $settings->add($tab_footer);

    // =========================================================================
    // TAB 6: CUSTOM CSS
    // =========================================================================
    $tab_advanced = new admin_settingpage('theme_theedu_advanced', get_string('tab_advanced', 'theme_theedu'));

    $tab_advanced->add(new admin_setting_heading('heading_customcss',
        get_string('heading_customcss', 'theme_theedu'), ''));

    $tab_advanced->add(new admin_setting_configtextarea('theme_theedu/customcss',
        get_string('customcss', 'theme_theedu'),
        get_string('customcss_desc', 'theme_theedu'), '', PARAM_RAW));

    $settings->add($tab_advanced);
}
