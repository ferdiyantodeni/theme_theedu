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
    $files = $fs->get_area_files($syscontext->id, 'theme_theedu', $filearea, 0, 'itemid, filepath, filename', false);

    if ($files) {
        $file = reset($files);
        return \moodle_url::make_pluginfile_url(
            $syscontext->id,
            'theme_theedu',
            $filearea,
            null,
            '/',
            $file->get_filename()
        )->out(false);
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
    $preset = theme_theedu_get_setting('brand_preset', 'default');
    $palettes = theme_theedu_get_presets_palette();

    if ($preset !== 'custom' && isset($palettes[$preset][$key])) {
        return $palettes[$preset][$key];
    }

    return theme_theedu_get_setting('color_' . $key, $default);
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
}
.navbar.fixed-top, nav.fixed-top {
    background-color: var(--theedu-navbar-bg) !important;
    color: var(--theedu-navbar-text) !important;
}
.navbar.fixed-top .navbar-nav .nav-link,
.navbar.fixed-top .theedu-site-title,
.navbar.fixed-top .header-shortcut-btn,
.navbar.fixed-top .usermenu-wrapper,
.navbar.fixed-top .theedu-lang-wrapper,
.navbar.fixed-top .theedu-user-pill .user-name {
    color: var(--theedu-navbar-text) !important;
}
.navbar.fixed-top .navbar-nav .nav-link:hover,
.navbar.fixed-top .navbar-nav .nav-link.active,
.navbar.fixed-top .navbar-nav .nav-link[aria-current=\"true\"] {
    color: var(--theedu-primary) !important;
    background: rgba(108, 92, 231, 0.08) !important;
}
body,
#page-wrapper,
#page,
#page.drawers,
.theedu-frontpage-container,
#page-content {
    background-color: var(--theedu-body-bg) !important;
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
[data-theme=\"dark\"] {
    --theedu-primary: {$darkprimary};
    --theedu-body-bg: {$darkbodybg};
    --theedu-card-bg: {$darkcardbg};
    --theedu-navbar-bg: {$darknavbarbg};
    --theedu-navbar-text: #E4E7EB;
    --theedu-footer-bg: #0D121B;
    --theedu-footer-text: #CBD5E1;
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
.course_category_tree .subcategories {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)) !important;
    gap: 1.25rem !important;
    margin: 1.5rem 0 2rem !important;
    padding: 0 !important;
}
.course_category_tree .subcategories .category {
    background: var(--theedu-card-bg, #FFFFFF) !important;
    border: 1px solid rgba(108, 92, 231, 0.12) !important;
    border-radius: 1.25rem !important;
    padding: 1.25rem 1.5rem !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: space-between !important;
    box-shadow: 0 4px 16px rgba(108, 92, 231, 0.04) !important;
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease !important;
}
.course_category_tree .subcategories .category:hover {
    transform: translateY(-4px) !important;
    box-shadow: 0 10px 28px rgba(108, 92, 231, 0.12) !important;
    border-color: var(--theedu-primary, #6C5CE7) !important;
}
.course_category_tree .subcategories .category .categoryname {
    font-size: 1.1rem !important;
    font-weight: 700 !important;
    margin: 0 !important;
}
.course_category_tree .subcategories .category .categoryname a {
    color: var(--theedu-navbar-text, #2D3436) !important;
    text-decoration: none !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 0.6rem !important;
}
.course_category_tree .subcategories .category .categoryname a::before {
    content: \"\f07b\" !important;
    font-family: \"Font Awesome 6 Free\" !important;
    font-weight: 900 !important;
    color: var(--theedu-primary, #6C5CE7) !important;
    font-size: 1.35rem !important;
}
.course_category_tree .subcategories .category .categoryname a:hover {
    color: var(--theedu-primary, #6C5CE7) !important;
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
[data-theme=\"dark\"] .course_category_tree .subcategories .category {
    background: #1E2638 !important;
    border-color: rgba(255, 255, 255, 0.08) !important;
}
[data-theme=\"dark\"] .course_category_tree .subcategories .category .categoryname a {
    color: #E4E7EB !important;
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

/* 1. TOP SPACING: ~20px gap under navbar */
.theedu-floating-header #page.drawers {
    margin-top: 70px !important;
    padding-top: var(--theedu-content-margin-top, 20px) !important;
}
.theedu-floating-header #page.drawers .main-inner {
    margin-top: 0 !important;
}
#page.drawers {
    margin-top: 60px !important;
    padding-top: var(--theedu-content-margin-top, 20px) !important;
}
#page.drawers .main-inner {
    margin-top: 0 !important;
}

/* 2. SIDES: Match Content Width to Navbar & Shrink when Drawers Open */
#page.drawers {
    padding-left: var(--theedu-content-side-padding, 20px) !important;
    padding-right: var(--theedu-content-side-padding, 20px) !important;
}
#page.drawers .main-inner,
.pagelayout-standard #page.drawers .main-inner,
.pagelayout-course #page.drawers .main-inner,
.pagelayout-incourse #page.drawers .main-inner,
.pagelayout-mydashboard #page.drawers .main-inner,
body.limitedwidth #page.drawers .main-inner,
body.mediumwidth #page.drawers .main-inner {
    max-width: var(--theedu-content-max-width, 1440px) !important;
    width: 100% !important;
    margin-left: auto !important;
    margin-right: auto !important;
    padding: var(--theedu-content-card-padding, 24px) !important;
}

/* 3. BOTTOM GAP TO FOOTER: ~20px */
#page.drawers .main-inner {
    margin-bottom: var(--theedu-content-margin-bottom, 20px) !important;
}
#page.drawers #page-content {
    padding-bottom: 0 !important;
}
@media (max-width: 768px) {
    .theedu-floating-header #page.drawers {
        padding-top: 2rem !important;
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
 * Helper to evaluate visibility of frontpage components based on user status.
 *
 * @param string $visibility 'all', 'loggedin', 'guest', 'none'
 * @param bool $isloggedin
 * @return bool
 */
function theme_theedu_is_visible_to_user($visibility, $isloggedin) {
    if ($visibility === 'none') {
        return false;
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

    // Login Background Image.
    $context['theedu_login_bg'] = theme_theedu_get_file_url('login_bg_image');

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
    $context['user_firstname'] = isloggedin() ? (!empty($USER->firstname) ? $USER->firstname : fullname($USER)) : 'Pelajar';
    if (function_exists('userdate')) {
        $context['user_date'] = userdate(time(), get_string('strftimedatefullshort', 'core_langconfig'));
    } else {
        $context['user_date'] = date('l, d F Y');
    }

    // Moove-inspired Live User Dashboard Stats
    if (isloggedin() && !isguestuser()) {
        $context['user_avatar_html'] = !empty($OUTPUT) ? $OUTPUT->user_picture($USER, ['size' => 64, 'class' => 'theedu-hero-avatar rounded-circle']) : '';
        $context['user_role_label'] = is_siteadmin() ? 'Administrator' : 'Peserta Pembelajaran';

        $usercourses = function_exists('enrol_get_users_courses') ? enrol_get_users_courses($USER->id, true) : [];
        $enrolledcount = count($usercourses);
        $completedcount = 0;

        if (!empty($CFG->enablecompletion) && !empty($usercourses)) {
            require_once($CFG->libdir . '/completionlib.php');
            foreach ($usercourses as $ucourse) {
                $cinfo = new \completion_info($ucourse);
                if ($cinfo->is_enabled() && $cinfo->is_course_complete($USER->id)) {
                    $completedcount++;
                }
            }
        }

        $context['user_course_count'] = $enrolledcount;
        $context['user_completed_count'] = $completedcount;
        $context['user_inprogress_count'] = max(0, $enrolledcount - $completedcount);
        $context['user_has_courses'] = ($enrolledcount > 0);
    } else {
        $context['user_avatar_html'] = '';
        $context['user_role_label'] = 'Tamu';
        $context['user_course_count'] = 0;
        $context['user_completed_count'] = 0;
        $context['user_inprogress_count'] = 0;
        $context['user_has_courses'] = false;
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

    // 4. Frontpage Course Categories / Highlights Grid (Dynamic Columns).
    $catvis = theme_theedu_get_setting('categories_visibility', 'all');
    $enablecategories = (bool) theme_theedu_get_setting('enable_categories', 1) && $isfrontpage && theme_theedu_is_visible_to_user($catvis, $isloggedinuser);
    $categoryitems = [];
    $catcolumns = (int) theme_theedu_get_setting('category_columns', 4);
    if ($catcolumns < 2 || $catcolumns > 6) {
        $catcolumns = 4;
    }

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
                    $coursecount = $cat->get_courses_count();
                    $categoryitems[] = [
                        'icon' => 'fa-graduation-cap',
                        'title' => $cat->get_formatted_name(),
                        'desc' => $coursecount . ' ' . get_string('courses'),
                        'url' => $CFG->wwwroot . '/course/index.php?categoryid=' . $cat->id,
                        'bg_color' => $tone['bg'],
                        'icon_color' => $tone['icon'],
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
                $categoryitems[] = [
                    'icon' => s($cicon),
                    'title' => format_text($ctitle, FORMAT_HTML),
                    'desc' => format_text($cdesc, FORMAT_HTML),
                    'url' => clean_param($curl, PARAM_URL),
                    'bg_color' => $tone['bg'],
                    'icon_color' => $tone['icon'],
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

        // 1. Check if user is logged in and has enrolled courses
        if (isloggedin() && !isguestuser() && function_exists('enrol_get_my_courses')) {
            $mycourses = enrol_get_my_courses(['id', 'fullname', 'shortname', 'category', 'summary', 'summaryformat'], 'visible DESC, sortorder ASC', $maxcourses);

            if (!empty($mycourses)) {
                $idx = 0;
                foreach ($mycourses as $c) {
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
                    if (class_exists('\core_completion\progress') && \core_completion\progress::get_course_progress_percentage($c) !== null) {
                        $progress = round(\core_completion\progress::get_course_progress_percentage($c));
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
                }
            }
        }

        // 2. If user is guest or has no enrolled courses, display active site courses
        if (empty($courseslist)) {
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

                    // Progress (if enrolled)
                    $progress = null;
                    if (isloggedin() && !isguestuser() && class_exists('\core_completion\progress')) {
                        $p = \core_completion\progress::get_course_progress_percentage($c);
                        if ($p !== null) {
                            $progress = round($p);
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
        }

        // 3. Fallback sample demo courses if fresh install or empty
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

    $isloggedinuser = isloggedin() && !isguestuser();
    $courseheading = theme_theedu_get_setting('course_heading', '');
    if (empty($courseheading)) {
        $courseheading = $isloggedinuser ? get_string('mycourses_heading', 'theme_theedu') : get_string('featured_courses_heading', 'theme_theedu');
    }

    $coursesubheading = theme_theedu_get_setting('course_subheading', '');
    if (empty($coursesubheading)) {
        $coursesubheading = $isloggedinuser ? get_string('mycourses_subheading', 'theme_theedu') : get_string('featured_courses_subheading', 'theme_theedu');
    }

    $context['theedu_courses'] = [
        'enabled' => $enablecourses && !empty($courseslist),
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
            $btitle = trim($parts[0]);
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
