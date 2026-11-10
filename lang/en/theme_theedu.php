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
 * English language strings for theme_theedu.
 *
 * @package    theme_theedu
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'TheEdu';
$string['choosereadme'] = 'TheEdu is a modern, responsive Moodle theme with a soft pastel aesthetic, dark mode support, multi-position branding logos (4 positions), dynamic frontpage slider, and customizable area colors. Compatible with Moodle 4.4 to 5.3+.';
$string['configtitle'] = 'TheEdu Settings';

// Tabs
$string['tab_branding'] = 'Branding & 4 Logos';
$string['tab_colors'] = 'Pastel Colors & Areas';
$string['tab_darkmode'] = 'Dark Mode';
$string['tab_slider'] = 'Homepage Slider';
$string['tab_categories'] = 'Homepage Categories & Highlights';
$string['tab_courses'] = 'Enrolled Courses & Card Display';
$string['tab_footer'] = 'Footer & Social';
$string['tab_advanced'] = 'Advanced & Custom CSS';

// 4 Logo Positions
$string['heading_logo_top_left'] = '1. Top-Left Logo (Kiri-Atas)';
$string['heading_logo_top_left_desc'] = 'The primary site logo located on the top left of the navigation bar.';
$string['enable_logo_top_left'] = 'Enable Top-Left Logo';
$string['enable_logo_top_left_desc'] = 'Show the logo on the top-left of the navbar.';
$string['logo_top_left'] = 'Top-Left Logo Image';
$string['logo_top_left_desc'] = 'Upload your logo image (PNG, SVG, JPG or WebP). If empty, the site shortname/fullname will be shown.';
$string['height_logo_top_left'] = 'Logo Height (px)';
$string['height_logo_top_left_desc'] = 'Height in pixels (e.g. 46). Aspect ratio will be maintained automatically.';
$string['link_logo_top_left'] = 'Logo Link URL';
$string['link_logo_top_left_desc'] = 'Optional URL when clicked. Leave blank to link to site home.';
$string['alt_logo_top_left'] = 'Logo Alt Text';
$string['alt_logo_top_left_desc'] = 'Accessible alt text for screen readers.';

$string['heading_logo_top_right'] = '2. Top-Right Logo (Kanan-Atas)';
$string['heading_logo_top_right_desc'] = 'Secondary logo positioned at the top right of the navbar/header (ideal for accreditation, partner, or sponsor logo).';
$string['enable_logo_top_right'] = 'Enable Top-Right Logo';
$string['enable_logo_top_right_desc'] = 'Display an additional logo on the top right side.';
$string['logo_top_right'] = 'Top-Right Logo Image';
$string['logo_top_right_desc'] = 'Upload your top-right logo image.';
$string['height_logo_top_right'] = 'Top-Right Logo Height (px)';
$string['height_logo_top_right_desc'] = 'Height in pixels (e.g. 42).';
$string['link_logo_top_right'] = 'Top-Right Logo Link URL';
$string['link_logo_top_right_desc'] = 'Optional URL when clicked.';
$string['alt_logo_top_right'] = 'Top-Right Logo Alt Text';
$string['alt_logo_top_right_desc'] = 'Alt text for top-right logo.';

$string['heading_logo_bottom_left'] = '3. Bottom-Left Logo (Kiri-Bawah)';
$string['heading_logo_bottom_left_desc'] = 'Footer logo placed in the bottom-left section.';
$string['enable_logo_bottom_left'] = 'Enable Bottom-Left Logo';
$string['enable_logo_bottom_left_desc'] = 'Display logo in the footer bottom-left area.';
$string['logo_bottom_left'] = 'Bottom-Left Logo Image';
$string['logo_bottom_left_desc'] = 'Upload your bottom-left logo image.';
$string['height_logo_bottom_left'] = 'Bottom-Left Logo Height (px)';
$string['height_logo_bottom_left_desc'] = 'Height in pixels (e.g. 42).';
$string['link_logo_bottom_left'] = 'Bottom-Left Logo Link URL';
$string['link_logo_bottom_left_desc'] = 'Optional URL when clicked.';
$string['alt_logo_bottom_left'] = 'Bottom-Left Logo Alt Text';
$string['alt_logo_bottom_left_desc'] = 'Alt text for bottom-left logo.';

$string['heading_logo_bottom_right'] = '4. Bottom-Right Logo (Kanan-Bawah)';
$string['heading_logo_bottom_right_desc'] = 'Footer logo placed in the bottom-right section (ideal for certification, partner, or ministry logo).';
$string['enable_logo_bottom_right'] = 'Enable Bottom-Right Logo';
$string['enable_logo_bottom_right_desc'] = 'Display logo in the footer bottom-right area.';
$string['logo_bottom_right'] = 'Bottom-Right Logo Image';
$string['logo_bottom_right_desc'] = 'Upload your bottom-right logo image.';
$string['height_logo_bottom_right'] = 'Bottom-Right Logo Height (px)';
$string['height_logo_bottom_right_desc'] = 'Height in pixels (e.g. 38).';
$string['link_logo_bottom_right'] = 'Bottom-Right Logo Link URL';
$string['link_logo_bottom_right_desc'] = 'Optional URL when clicked.';
$string['alt_logo_bottom_right'] = 'Bottom-Right Logo Alt Text';
$string['alt_logo_bottom_right_desc'] = 'Alt text for bottom-right logo.';

$string['heading_favicon'] = 'Favicon & Login Visuals';
$string['favicon'] = 'Favicon';
$string['favicondesc'] = 'Upload a custom favicon (.ico or .png).';
$string['login_bg_image'] = 'Login Page Background Image';
$string['login_bg_image_desc'] = 'Upload an optional background image for the login page (1920x1080px JPG or WebP).';
$string['login_bg_url'] = 'Login Background Image URL (Alternative)';
$string['login_bg_url_desc'] = 'Optional direct URL to an image for the login background (e.g. https://domain.com/bg.jpg). Used if no file is uploaded above.';

$string['heading_floating_header'] = 'Floating Header (Mengambang)';
$string['heading_floating_header_desc'] = 'Enable modern floating pill-style header with rounded corners, side margins, and glassmorphic blur.';
$string['floating_header'] = 'Enable Floating Header';
$string['floating_header_desc'] = 'Detaches navbar from viewport edges to float with soft shadows and rounded corners.';

// Header Shortcuts
$string['heading_header_shortcuts'] = 'Top-Right Header Shortcuts & Actions';
$string['heading_header_shortcuts_desc'] = 'Configure which shortcut icons appear on the top-right of the header navbar.';
$string['header_show_mycourses'] = 'Show My Courses Icon (Graduation Cap)';
$string['header_show_mycourses_desc'] = 'Display quick link icon to enrolled courses.';
$string['header_show_notifications'] = 'Show Notification Bell';
$string['header_show_notifications_desc'] = 'Display notification alerts icon.';
$string['header_show_messages'] = 'Show Messages Bubble';
$string['header_show_messages_desc'] = 'Display instant messaging bubble icon.';
$string['header_show_user_profile'] = 'Show User Profile & Avatar';
$string['header_show_user_profile_desc'] = 'Display user name and profile picture dropdown.';
$string['header_show_editmode'] = 'Show Edit Mode Switch';
$string['header_show_editmode_desc'] = 'Display teacher/admin quick editing toggle switch.';

// Typography
$string['heading_typography'] = 'Theme Typography & Fonts';
$string['heading_typography_desc'] = 'Select the primary font used throughout the site.';
$string['theme_font'] = 'Main Font Family';
$string['theme_font_desc'] = 'Select one of the curated web fonts or specify a custom Google Font.';
$string['custom_font_name'] = 'Custom Font Name';
$string['custom_font_name_desc'] = 'Exact font family name (e.g. "Plus Jakarta Sans", "Outfit", "Space Grotesk").';
$string['custom_font_url'] = 'Custom Google Font URL';
$string['custom_font_url_desc'] = 'Full Google Fonts stylesheet URL (e.g. https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&display=swap).';

// Colors & Areas
$string['heading_brand_presets'] = 'Brand Color Presets (Pastel Themes)';
$string['heading_brand_presets_desc'] = 'Select a ready-to-use harmonious pastel palette, or choose Custom to manually configure each color below.';
$string['brand_preset'] = 'Active Color Preset';
$string['brand_preset_desc'] = 'Choose your preferred brand color scheme.';
$string['preset_default'] = 'Preset Default (Pastel Lavender & Teal)';
$string['preset_red'] = 'Preset Merah (Pastel Crimson & Rose)';
$string['preset_blue'] = 'Preset Biru (Pastel Ocean & Sky Blue)';
$string['preset_green'] = 'Preset Hijau (Pastel Mint & Emerald)';
$string['preset_white'] = 'Preset White (Clean Minimalist & Slate)';
$string['preset_grey'] = 'Preset Grey (Sophisticated Cool Slate)';
$string['preset_yellow'] = 'Preset Yellow (Warm Amber & Sunflower)';
$string['preset_custom'] = 'Custom / Manual (Use Color Pickers Below)';

$string['heading_color_overview'] = 'Pastel Palette Configuration';
$string['heading_color_overview_desc'] = 'Customize the pastel color palette for your site. These colors are applied consistently across UI elements.';
$string['color_primary'] = 'Primary Pastel Color';
$string['color_primary_desc'] = 'Main theme color (buttons, active tabs, main accents). Default: #6C5CE7 (Soft Pastel Lavender).';
$string['color_secondary'] = 'Secondary Pastel Color';
$string['color_secondary_desc'] = 'Complementary pastel color for badges, gradients, and secondary elements. Default: #00CEC9 (Pastel Mint).';
$string['color_accent'] = 'Accent / Alert Pastel Color';
$string['color_accent_desc'] = 'Highlight color for special tags, banners, or callouts. Default: #FF7675 (Pastel Coral).';
$string['heading_area_navbar'] = 'Header & Navigation Bar Colors';
$string['color_navbar_bg'] = 'Navbar Background Color';
$string['color_navbar_bg_desc'] = 'Background color for top navbar.';
$string['color_navbar_text'] = 'Navbar Text & Links Color';
$string['color_navbar_text_desc'] = 'Text and icon color inside the top navbar.';
$string['heading_area_body'] = 'Page Body & Card Content Colors';
$string['color_body_bg'] = 'Body Background Color';
$string['color_body_bg_desc'] = 'Background color of the page canvas.';
$string['color_card_bg'] = 'Card & Block Background Color';
$string['color_card_bg_desc'] = 'Background color for course cards, blocks, and activity containers.';
$string['heading_area_footer'] = 'Footer Colors';
$string['color_footer_bg'] = 'Footer Background Color';
$string['color_footer_bg_desc'] = 'Background color of the footer area.';
$string['color_footer_text'] = 'Footer Text Color';
$string['color_footer_text_desc'] = 'Text color in the footer area.';

// Dark Mode
$string['heading_darkmode'] = 'Dark Mode Options';
$string['heading_darkmode_desc'] = 'Allow users to switch between Light Pastel and Dark Mode with smooth transition and local state persistence.';
$string['enable_darkmode'] = 'Enable Dark Mode Toggle';
$string['enable_darkmode_desc'] = 'Shows a sun/moon switch button in the header bar.';
$string['darkmode_default'] = 'Default Color Scheme';
$string['darkmode_default_desc'] = 'Initial mode for visitors before they toggle.';
$string['darkmode_auto'] = 'Automatic (Follow OS / Browser setting)';
$string['darkmode_light'] = 'Light Mode (Pastel)';
$string['darkmode_dark'] = 'Dark Mode';
$string['heading_darkmode_colors'] = 'Dark Mode Palette Fine-Tuning';
$string['dark_color_primary'] = 'Dark Mode Primary Accent';
$string['dark_color_primary_desc'] = 'Vibrant pastel accent used when dark mode is active.';
$string['dark_color_body_bg'] = 'Dark Mode Body Background';
$string['dark_color_body_bg_desc'] = 'Deep background color for dark mode.';
$string['dark_color_card_bg'] = 'Dark Mode Card Background';
$string['dark_color_card_bg_desc'] = 'Elevated surface color for cards in dark mode.';
$string['dark_color_navbar_bg'] = 'Dark Mode Navbar Background';
$string['dark_color_navbar_bg_desc'] = 'Navbar background in dark mode.';
$string['toggle_darkmode'] = 'Toggle dark mode';

// Slider
$string['heading_slider_general'] = 'Homepage Carousel Slider';
$string['heading_slider_general_desc'] = 'Configure a dynamic, responsive slider displayed on the frontpage with pastel accents, call-to-action buttons, and smooth transition effects.';
$string['enable_slider'] = 'Enable Slider on Frontpage';
$string['enable_slider_desc'] = 'Display the dynamic carousel banner on the homepage.';
$string['slider_count'] = 'Number of Active Slides';
$string['slider_count_desc'] = 'Select how many slides to display (up to 5).';
$string['slider_interval'] = 'Slide Autoplay Interval (ms)';
$string['slider_interval_desc'] = 'Time each slide is shown in milliseconds (e.g. 5000 = 5 seconds). Set 0 to disable autoplay.';
$string['slide_heading'] = 'Slide {$a}';
$string['slide_enable'] = 'Enable Slide {$a}';
$string['slide_image'] = 'Slide {$a} Image';
$string['slide_image_desc'] = 'Recommended size: 1920x650px (JPG, PNG, or WebP).';
$string['slide_badge'] = 'Slide {$a} Badge / Tagline';
$string['slide_badge_desc'] = 'Optional text badge shown above the slide title (e.g. DAIHATSU, TRAINING, NEW). Leave empty to hide completely.';
$string['slide_title'] = 'Slide {$a} Title';
$string['slide_caption'] = 'Slide {$a} Caption / Description';
$string['slide_btn_text'] = 'Slide {$a} Button Text';
$string['slide_btn_url'] = 'Slide {$a} Button Link URL';

// Frontpage Categories & Highlights
$string['heading_categories_general'] = 'Frontpage Categories & Highlights';
$string['heading_categories_general_desc'] = 'Configure course categories or feature highlights that appear fixed below the slider banner with dynamic column wrapping.';
$string['enable_categories'] = 'Enable Categories / Highlights Section';
$string['enable_categories_desc'] = 'Display this section on the homepage.';
$string['category_columns'] = 'Max Columns Before Wrapping Down';
$string['category_columns_desc'] = 'Choose how many columns can fit in a single row before items drop to the next row (2, 3, 4, 5, or 6 columns).';
$string['columns'] = 'Columns';
$string['category_source'] = 'Category Content Source';
$string['category_source_desc'] = 'Choose whether to display custom fixed categories configured below or automatically pull course categories from Moodle.';
$string['category_source_custom'] = 'Custom Fixed Categories (Configured Below)';
$string['category_source_moodle'] = 'Auto-fetch from Moodle Course Categories';
$string['category_count'] = 'Total Categories to Display';
$string['category_count_desc'] = 'Number of category cards to render.';
$string['category_heading_item'] = 'Category Card {$a}';
$string['category_icon'] = 'FontAwesome Icon Class';
$string['category_icon_desc'] = 'Example: fa-laptop-code, fa-certificate, fa-users, fa-moon, fa-brain, fa-chart-pie';
$string['category_title'] = 'Category Title';
$string['category_desc'] = 'Subtitle / Description';
$string['category_url'] = 'Link URL';
$string['category_url_desc'] = 'URL to the course category or page.';

// Default category cards
$string['default_cat1_title'] = 'Interactive Materials';
$string['default_cat1_desc'] = 'Quizzes & Video Lessons';
$string['default_cat2_title'] = 'Digital Certificates';
$string['default_cat2_desc'] = 'Instantly Verified';
$string['default_cat3_title'] = 'Community Forum';
$string['default_cat3_desc'] = '24/7 Peer Collaboration';
$string['default_cat4_title'] = 'Comfortable Dark Mode';
$string['default_cat4_desc'] = 'Gentle on the Eyes';
$string['default_cat5_title'] = 'Artificial Intelligence';
$string['default_cat5_desc'] = 'Machine Learning & LLMs';
$string['default_cat6_title'] = 'Data Analytics';
$string['default_cat6_desc'] = 'Business Intelligence & Big Data';
$string['default_cat7_title'] = 'UI/UX Design';
$string['default_cat7_desc'] = 'Figma, Design Systems & Prototyping';
$string['default_cat8_title'] = 'Foreign Languages';
$string['default_cat8_desc'] = 'English, Mandarin & Japanese';

// Visibility & Access Control
$string['vis_all'] = 'All Visitors (Guests & Logged-in)';
$string['vis_guest'] = 'Guests Only (Not logged in)';
$string['vis_loggedin'] = 'Logged-in Users Only (All roles)';
$string['vis_registered_only'] = 'Registered Regular Users Only (Non-Admin)';
$string['vis_admin'] = 'Site Administrators Only';
$string['vis_none'] = 'Disabled / Completely Hidden';

// Navigation & Administration Blocks Visibility
$string['heading_frontpage_blocks'] = 'Frontpage Blocks & Core Content Visibility';
$string['heading_frontpage_blocks_desc'] = 'Control the visibility of Moodle side/footer blocks (such as the Navigation and Administration blocks) and Moodle core activities on the Frontpage by user category.';
$string['frontpage_blocks_visibility'] = 'Frontpage Blocks Visibility (Navigation & Administration)';
$string['frontpage_blocks_visibility_desc'] = 'Choose who can see the blocks (Navigation, Administration, etc.) on the Frontpage. Setting this to "Site Administrators Only" prevents guest / non-login users from seeing navigation clutter.';
$string['frontpage_content_visibility'] = 'Moodle Core Content Visibility';
$string['frontpage_content_visibility_desc'] = 'Choose who can see Moodle core default frontpage activities/listings below the cards.';
$string['navbar_guest_visibility'] = 'Primary Navbar Menu for Guest (Non-login) Users';
$string['navbar_guest_visibility_desc'] = 'Choose whether to show or hide the primary navigation menu in the header navbar for guest/non-logged-in visitors.';
$string['nav_guest_show'] = 'Show primary navigation menu for guests';
$string['nav_guest_hide'] = 'Hide primary navigation menu for guests (clean minimalist navbar)';
$string['slider_visibility'] = 'Slider Target Audience';
$string['slider_visibility_desc'] = 'Choose who can see the hero banner slider on the home page.';
$string['search_visibility'] = 'Search Bar Target Audience';
$string['search_visibility_desc'] = 'Choose who can see the course search bar on the home page.';
$string['categories_visibility'] = 'Categories Target Audience';
$string['categories_visibility_desc'] = 'Choose who can see the category highlights grid on the home page.';
$string['courses_visibility'] = 'Courses Section Target Audience';
$string['courses_visibility_desc'] = 'Choose who can see the courses section on the home page.';
$string['frontpage_hide_moodle_content'] = 'Hide Duplicate Core Content on Home';
$string['frontpage_hide_moodle_content_desc'] = 'Hides default unstyled Moodle frontpage course/category lists when not in editing mode, keeping the home page pristine and clean.';
$string['enable_catalog_card_grid'] = 'Enable Modern Card Grid on Course Catalog';
$string['enable_catalog_card_grid_desc'] = 'Transforms Moodle category and course catalog pages into the unified TheEdu modern cards grid with thumbnail banners and teacher avatars.';

// Course Cards & Enrolled Courses
$string['heading_course_search'] = 'Frontpage Course Search Bar';
$string['heading_course_search_desc'] = 'Display an instant search box below the slider so learners can search courses directly.';
$string['enable_course_search'] = 'Enable Course Search Bar';
$string['enable_course_search_desc'] = 'Show course search box on frontpage.';
$string['search_heading'] = 'Search Section Title';
$string['search_subheading'] = 'Search Section Subtitle';
$string['search_placeholder'] = 'Search Input Placeholder';
$string['search_heading_default'] = 'Find Your Next Learning Adventure';
$string['search_subheading_default'] = 'Search by course name, topic, or instructor';
$string['search_placeholder_default'] = 'What would you like to learn today?';
$string['search_show_tags'] = 'Show Popular Category Quick Filter Tags';
$string['search_show_tags_desc'] = 'Display dynamic clickable category filter badges beneath the search box.';
$string['popular_searches'] = 'Popular Searches';
$string['heading_courses_general'] = 'Frontpage Enrolled Courses Settings';
$string['heading_courses_general_desc'] = 'Display courses on the frontpage. For logged-in users, it automatically displays their enrolled courses with progress and teacher info; for guests, it displays available/featured courses.';
$string['enable_frontpage_courses'] = 'Enable Courses Section on Frontpage';
$string['enable_frontpage_courses_desc'] = 'Show the course cards grid on the frontpage.';
$string['course_heading_setting'] = 'Custom Section Title';
$string['course_heading_setting_desc'] = 'Leave blank to use default titles ("My Enrolled Courses" for members, "Featured Courses" for visitors).';
$string['course_subheading_setting'] = 'Custom Section Subtitle';
$string['course_subheading_setting_desc'] = 'Short subtitle displayed below section title.';
$string['mycourses_heading'] = 'My Enrolled Courses';
$string['mycourses_subheading'] = 'Continue learning and track your progress in enrolled courses';
$string['featured_courses_heading'] = 'Featured Courses';
$string['featured_courses_subheading'] = 'Explore trending learning programs and enroll today';
$string['courses_empty_behavior'] = 'When Learner Has No Enrolled Courses';
$string['courses_empty_behavior_desc'] = 'Action to take on the frontpage when a logged-in user is not enrolled in any courses.';
$string['courses_empty_state'] = 'Show friendly empty state message with "Explore Courses" button (Recommended)';
$string['courses_empty_hide'] = 'Hide the courses section completely';
$string['courses_empty_featured'] = 'Show available site courses under "Featured Courses" title';
$string['noenrolledcourses'] = 'No enrolled courses yet';
$string['noenrolledcourses_desc'] = 'You are not currently enrolled in any learning courses. Browse our course catalog to find topics and register.';
$string['explorecourses'] = 'Explore Courses';
$string['course_display_count'] = 'Maximum Courses Displayed';
$string['course_display_count_desc'] = 'Total number of course cards to display.';
$string['course_columns'] = 'Course Columns per Row';
$string['course_columns_desc'] = 'Number of course cards displayed horizontally in a row.';

// Course Thumbnail & Icon
$string['heading_courses_thumbnail'] = 'Course Thumbnail & Icon Settings';
$string['heading_courses_thumbnail_desc'] = 'Configure whether course cards display an overview image, a custom thumbnail icon with pastel pattern, or auto-fallback.';
$string['course_show_thumbnail'] = 'Show Course Thumbnail Banner';
$string['course_show_thumbnail_desc'] = 'Display top thumbnail banner on course cards.';
$string['course_thumbnail_mode'] = 'Thumbnail Mode';
$string['course_thumbnail_mode_desc'] = 'Choose how thumbnail banners are rendered.';
$string['course_thumb_mode_auto'] = 'Auto: Course Image if uploaded, otherwise pastel icon';
$string['course_thumb_mode_icon'] = 'Always Icon: Display clean pastel icon badge';
$string['course_thumb_mode_image'] = 'Image Only: Course overview image only';
$string['course_default_icon'] = 'Default Course Thumbnail Icon';
$string['course_default_icon_desc'] = 'FontAwesome icon for course cards (e.g. fa-graduation-cap, fa-laptop-code, fa-book-open, fa-chalkboard-user).';

// Course Card Elements
$string['heading_courses_elements'] = 'Card Elements & Bottom Teacher Info';
$string['heading_courses_elements_desc'] = 'Toggle details shown on course cards.';
$string['course_show_teacher'] = 'Show Teacher / Instructor at Bottom';
$string['course_show_teacher_desc'] = 'Display instructor avatar and name at the bottom of the card.';
$string['course_teacher_default_icon'] = 'Default Instructor Icon';
$string['course_teacher_default_icon_desc'] = 'Default FontAwesome icon for instructors without an uploaded profile photo (e.g. fa-chalkboard-user). TheEdu automatically maps icons based on course contacts and assigned roles: Editing Teacher (fa-chalkboard-user), Non-editing Teacher/Trainer (fa-user-pen), Manager (fa-user-tie), Course Creator (fa-graduation-cap).';
$string['course_show_teacher_role'] = 'Show Instructor Role Label';
$string['course_show_teacher_role_desc'] = 'Display the role title (e.g. Teacher, Non-editing Teacher, Manager) under the instructor name.';
$string['selfpaced'] = 'Self-paced Learning';
$string['viewcourse'] = 'View Course';
$string['role_teacher'] = 'Instructor';
$string['role_noneditingteacher'] = 'Trainer / Assistant';
$string['role_manager'] = 'Course Manager';
$string['course_show_progress'] = 'Show Completion Progress Bar';
$string['course_show_progress_desc'] = 'Display progress percentage and bar for enrolled learners.';
$string['course_show_category'] = 'Show Category Tag';
$string['course_show_category_desc'] = 'Display course category badge on thumbnail.';
$string['course_show_summary'] = 'Show Course Summary';
$string['course_show_summary_desc'] = 'Display brief course description.';

// Sample courses
$string['sample_course1_title'] = 'Modern Web Development & React';
$string['sample_course1_cat'] = 'Web Development';
$string['sample_course1_desc'] = 'Build high-performance web applications using modern React, Next.js, and clean component patterns.';
$string['sample_course2_title'] = 'Applied Data Science & Machine Learning';
$string['sample_course2_cat'] = 'Data Science';
$string['sample_course2_desc'] = 'Analyze complex datasets and train accurate predictive machine learning pipelines using Python.';
$string['sample_course3_title'] = 'Digital Marketing Strategy 2026';
$string['sample_course3_cat'] = 'Digital Business';
$string['sample_course3_desc'] = 'Drive organic growth, content marketing, search engine visibility, and actionable customer analytics.';
$string['status_active'] = 'Active';
$string['status_new'] = 'New';
$string['status_popular'] = 'Popular';

// Default content
$string['default_slide1_title'] = 'Transforming Digital Learning with TheEdu & ADM';
$string['default_slide1_caption'] = 'Access standardized training modules, certified industrial competencies, and interactive courses anywhere, anytime.';
$string['default_slide1_btn'] = 'Explore Courses';
$string['default_footer_about'] = 'Empowering educators and students with intuitive learning experiences, high-quality content, and accessible tools.';

// Category Headings
$string['category_heading'] = 'Category Section Heading';
$string['category_heading_desc'] = 'Main title displayed above category cards.';
$string['categories_heading_default'] = 'Course Categories & Specializations';
$string['category_subheading'] = 'Category Section Subheading';
$string['category_subheading_desc'] = 'Subheading or short description displayed below heading.';
$string['categories_subheading_default'] = 'Browse interactive courses curated for professional skills, manufacturing standards, and leadership.';
$string['category_all_url'] = 'View All Courses URL';
$string['category_all_url_desc'] = 'Destination link for the "View All Courses" button.';

// Category & Subcategory Card Display Options
$string['heading_category_card_display'] = 'Category & Subcategory Card Presentation';
$string['heading_category_card_display_desc'] = 'Configure modern card packaging, thumbnail banner area (toggleable ON/OFF), course counts, and subcategory options for both homepage and category catalog pages.';
$string['enable_category_thumbnails'] = 'Enable Category Thumbnail Image Banner';
$string['enable_category_thumbnails_desc'] = 'When enabled, category and subcategory cards display a top thumbnail image banner (custom image from category description or elegant colorful education gradient) with overlaid badges. When disabled, cards render in a clean compact icon badge layout.';
$string['category_card_style'] = 'Category Card Display Style';
$string['category_card_style_desc'] = 'Choose between visual thumbnail banner cards or compact icon badge cards.';
$string['category_style_banner'] = 'Thumbnail Banner Area (With Image / Dynamic Gradient)';
$string['category_style_compact'] = 'Compact Icon Badge (Clean & Space-Saving)';
$string['category_thumbnail_height'] = 'Category Thumbnail Height (px)';
$string['category_thumbnail_height_desc'] = 'Height of the category thumbnail banner area in pixels (default: 140).';
$string['show_category_course_count'] = 'Show Course Count Pill';
$string['show_category_course_count_desc'] = 'Display total enrolled or available courses badge on category and subcategory cards.';
$string['show_category_subcat_count'] = 'Show Subcategory Count Pill';
$string['show_category_subcat_count_desc'] = 'Display count of child subcategories on parent category cards.';
$string['show_category_desc'] = 'Show Category Description';
$string['show_category_desc_desc'] = 'Display brief description or summary text inside category cards.';
$string['subcategories'] = 'Subcategories';
$string['subcategory'] = 'Subcategory';
$string['viewcategory'] = 'Explore Category';

// Footer
$string['heading_footer_info'] = 'Footer Information';
$string['footer_about'] = 'About Site (Footer Column 1)';
$string['footer_about_desc'] = 'Short introduction text displayed in the footer.';
$string['footer_copyright'] = 'Copyright Notice';
$string['footer_copyright_desc'] = 'Custom copyright line. Leave blank to show default site name and current year.';
$string['heading_footer_middle'] = 'Footer Middle Column (Custom Menu)';
$string['heading_footer_middle_desc'] = 'Configure navigation links and custom title in the middle column of the footer.';
$string['footer_middle_title'] = 'Middle Column Title';
$string['footer_middle_title_desc'] = 'Heading for the middle column (e.g. Quick Links, TheEdu Portal, Useful Links).';
$string['footer_middle_title_default'] = 'Quick Links';
$string['footer_middle_links'] = 'Middle Column Navigation Links';
$string['footer_middle_links_desc'] = 'Enter one link per line in format: Title|URL (e.g. Home|/\nDashboard|/my/\nCourses|/course/index.php).';
$string['footer_middle_links_default'] = "Home|/\nDashboard|/my/\nCourses|/course/index.php\nSite Administration|/admin/search.php";
$string['heading_footer_bottom_right'] = 'Footer Bottom-Right Area';
$string['heading_footer_bottom_right_desc'] = 'Configure links or notes on the bottom-right bar (replaces or augments standard Moodle data retention links).';
$string['footer_bottom_right_mode'] = 'Bottom-Right Content Mode';
$string['footer_bottom_right_mode_desc'] = 'Choose what content to display in the bottom-right corner of the footer.';
$string['footer_bottom_right_custom_mode'] = 'Custom Links & Text Only';
$string['footer_bottom_right_moodle_mode'] = 'Standard Moodle Links (Data Retention & Mobile App)';
$string['footer_bottom_right_both_mode'] = 'Both (Custom Links + Standard Moodle)';
$string['footer_bottom_right_none_mode'] = 'Hide Bottom-Right Area';
$string['footer_bottom_right_links'] = 'Bottom-Right Custom Links';
$string['footer_bottom_right_links_desc'] = 'Enter one link per line in format: Title|URL.';
$string['footer_bottom_right_links_default'] = "Data retention summary|/admin/tool/dataprivacy/summary.php\nGet the mobile app|https://download.moodle.org/mobile";
$string['footer_bottom_right_text'] = 'Bottom-Right Custom Text / Note';
$string['footer_bottom_right_text_desc'] = 'Optional text or small disclaimer displayed in the bottom-right bar.';
$string['heading_footer_social'] = 'Social Media Links';

// Course Activity Navigation
$string['previousactivity'] = 'Previous';
$string['nextactivity'] = 'Next';
$string['jumpto'] = 'Jump to...';
$string['backtocourse'] = 'Back to Course';
$string['enable_activity_nav'] = 'Enable Course Activity Navigation Bar';
$string['enable_activity_nav_desc'] = 'Show previous, next, and jump-to activity buttons below activities in course view.';

// Margin & Layout Spacing
$string['tab_margin'] = 'Margin & Spacing';
$string['heading_margin_top'] = '1. Jarak Atas (Top Spacing under Navbar)';
$string['heading_margin_top_desc'] = 'Atur jarak vertikal antara batas bawah navbar dan kartu konten utama.';
$string['content_margin_top'] = 'Jarak Navbar ke Konten (px)';
$string['content_margin_top_desc'] = 'Jarak dalam piksel antara batas bawah navbar dan kartu konten (default: 20px).';

$string['heading_margin_width'] = '2. Lebar Konten & Sisi Kiri-Kanan (Width & Side Alignment)';
$string['heading_margin_width_desc'] = 'Atur lebar kartu konten agar presisi dengan floating navbar pada halaman Dashboard, My Courses, dan Course Content.';
$string['enable_full_width_content'] = 'Samakan Lebar Konten dengan Navbar';
$string['enable_full_width_content_desc'] = 'Aktifkan agar lebar kartu konten utama mengikuti batas floating navbar (1440px) dan otomatis menciut saat drawer kiri/kanan dibuka.';
$string['content_max_width'] = 'Lebar Maksimum Konten (px)';
$string['content_max_width_desc'] = 'Lebar maksimum kartu konten utama pada layar monitor lebar (default: 1440px).';
$string['content_side_padding'] = 'Jarak Sisi Kiri & Kanan (px)';
$string['content_side_padding_desc'] = 'Jarak tepi luar kartu konten dari sisi layar browser agar selaras dengan navbar (default: 20px).';

$string['heading_margin_bottom'] = '3. Jarak Bawah ke Footer (Bottom Spacing above Footer)';
$string['heading_margin_bottom_desc'] = 'Atur jarak vertikal antara bagian bawah kartu konten utama dan footer.';
$string['content_margin_bottom'] = 'Jarak Konten ke Footer (px)';
$string['content_margin_bottom_desc'] = 'Jarak dalam piksel antara bagian bawah kartu konten dan footer (default: 20px).';
$string['content_card_padding'] = 'Padding Dalam Kartu Konten (px)';
$string['content_card_padding_desc'] = 'Ruang padding di dalam kartu konten utama (.main-inner) (default: 24px).';

// Advanced
$string['heading_customcss'] = 'Custom CSS Code';
$string['customcss'] = 'Custom CSS';
$string['customcss_desc'] = 'Add custom CSS rules here to override or fine-tune styling without modifying files.';

// Tab 10: About Theme & Release Notes
$string['tab_about'] = 'About Theme & Release Notes';
$string['heading_about_theme'] = 'TheEdu Theme Information & Release Notes';
$string['about_theme_desc'] = 'Technical specifications, developer details, and comprehensive release history.';

// Dashboard Hero & Stats (Strict English)
$string['welcome_back'] = 'Welcome back';
$string['dashboard_hero_desc'] = 'Track your learning progress, activities, and active courses here.';
$string['role_learner'] = 'Learner';
$string['role_admin'] = 'Administrator';
$string['role_guest'] = 'Guest';
$string['stat_enrolled'] = 'Enrolled';
$string['stat_enrolled_desc'] = 'Total enrolled courses';
$string['stat_completed'] = 'Completed';
$string['stat_completed_desc'] = 'Courses with completion progress of 80% or higher';
$string['stat_inprogress'] = 'In Progress';
$string['stat_inprogress_desc'] = 'Courses currently in progress (<80%)';
$string['stat_ongoing'] = 'Ongoing';
$string['stat_ongoing_desc'] = 'Courses active today';
$string['stat_upcoming'] = 'Upcoming';
$string['stat_upcoming_desc'] = 'Courses scheduled to start in the future';
$string['stat_past'] = 'Past';
$string['stat_past_desc'] = 'Courses that have ended past today';

// Dynamic Dashboard Hero Statistic Cards Settings
$string['heading_dashboard_stats'] = 'Dashboard Hero Statistic Cards';
$string['heading_dashboard_stats_desc'] = 'Select which statistic cards appear on the user dashboard hero banner and configure their dynamic display sequence order.';
$string['stat_card_order_desc'] = 'Display sequence order (e.g. 1, 2, 3, 4, 5). Lower numbers appear first.';
$string['stat_card_enrolled_desc'] = 'Show card for total active enrolled courses.';
$string['stat_card_enrolled_order'] = 'Enrolled Card Display Order';
$string['stat_card_inprogress_desc'] = 'Show card for courses in progress (completion progress <80%).';
$string['stat_card_inprogress_order'] = 'In Progress Card Display Order';
$string['stat_card_completed_desc'] = 'Show card for completed courses (completion progress ≥80%).';
$string['stat_card_completed_order'] = 'Completed Card Display Order';
$string['stat_card_ongoing_desc'] = 'Show card for courses active/occurring today.';
$string['stat_card_ongoing_order'] = 'Ongoing Card Display Order';
$string['stat_card_upcoming_desc'] = 'Show card for courses scheduled to start in the future.';
$string['stat_card_upcoming_order'] = 'Upcoming Card Display Order';
$string['stat_card_past_desc'] = 'Show card for courses that have ended past today.';
$string['stat_card_past_order'] = 'Past Card Display Order';

// Footer Language Selector
$string['heading_footer_language'] = 'Footer Language Selector';
$string['heading_footer_language_desc'] = 'Display an interactive language switcher in the bottom footer bar.';
$string['footer_show_language_selector'] = 'Show Language Selector in Footer';
$string['footer_show_language_selector_desc'] = 'Display an English [default] & Indonesia language switcher in the footer bottom bar.';
$string['footer_language_select'] = 'Language';

// Secondary Navigation & Tabs Customization
$string['tab_secnav'] = 'Secondary Navigation & Tabs';
$string['heading_secnav_presets'] = 'Navigation Design Presets';
$string['heading_secnav_presets_desc'] = 'Quickly apply a curated visual theme for the secondary navigation bar (Course tabs, Participants, Grades, Site Administration tabs, etc.).';
$string['secnav_preset'] = 'Design Preset';
$string['secnav_preset_desc'] = 'Select a design preset or choose "Custom" to configure individual button styling below.';
$string['secnav_preset_default'] = 'Modern Pill (TheEdu Purple Gradient)';
$string['secnav_preset_soft_rounded'] = 'Soft Rounded (Indigo 8px)';
$string['secnav_preset_minimal_flat'] = 'Minimal Clean (Dark Slate 6px)';
$string['secnav_preset_outline_ghost'] = 'Ghost Outline (Bordered Pill)';
$string['secnav_preset_gradient_emerald'] = 'Fresh Emerald (Teal Gradient)';
$string['secnav_preset_corporate_blue'] = 'Executive Blue (Ocean Gradient 8px)';
$string['secnav_preset_card_container'] = 'Enclosed Bar (Pill on Card Background)';
$string['secnav_preset_custom'] = 'Custom (Use Individual Settings Below)';

$string['heading_secnav_shape'] = 'Button Geometry & Spacing';
$string['heading_secnav_shape_desc'] = 'Adjust border radius (corner curvature), padding, font size, and spacing between buttons.';
$string['secnav_btn_radius'] = 'Button Border Radius';
$string['secnav_btn_radius_desc'] = 'Corner curvature of buttons. E.g. "2rem" for full pill, "8px" for soft rounded, "4px" for subtle rounded, "0px" for sharp rectangle.';
$string['secnav_btn_padding'] = 'Button Internal Padding';
$string['secnav_btn_padding_desc'] = 'Internal spacing inside each button. Default: "0.45rem 1.15rem".';
$string['secnav_btn_fontsize'] = 'Button Font Size';
$string['secnav_btn_fontsize_desc'] = 'Text size of navigation buttons. Default: "0.9rem".';
$string['secnav_btn_gap'] = 'Spacing Between Buttons';
$string['secnav_btn_gap_desc'] = 'Horizontal gap between consecutive buttons. Default: "0.4rem".';

$string['heading_secnav_normal'] = 'Normal State (Inactive Buttons)';
$string['secnav_btn_bg'] = 'Button Background Color';
$string['secnav_btn_bg_desc'] = 'Background color of inactive navigation buttons.';
$string['secnav_btn_text'] = 'Button Text Color';
$string['secnav_btn_text_desc'] = 'Text color of inactive navigation buttons.';
$string['secnav_btn_border'] = 'Button Border Color';
$string['secnav_btn_border_desc'] = 'Border color of inactive navigation buttons.';
$string['secnav_btn_border_width'] = 'Button Border Width';
$string['secnav_btn_border_width_desc'] = 'Border thickness (e.g. "1px", "2px", "0px").';

$string['heading_secnav_hover'] = 'Hover State (Mouse Over)';
$string['secnav_btn_hover_bg'] = 'Hover Background Color';
$string['secnav_btn_hover_bg_desc'] = 'Background color when hovering over a button.';
$string['secnav_btn_hover_text'] = 'Hover Text Color';
$string['secnav_btn_hover_text_desc'] = 'Text color when hovering over a button.';
$string['secnav_btn_hover_border'] = 'Hover Border Color';
$string['secnav_btn_hover_border_desc'] = 'Border color when hovering over a button.';

$string['heading_secnav_active'] = 'Active State (Selected Tab)';
$string['secnav_btn_active_bg'] = 'Active Background / Gradient Start';
$string['secnav_btn_active_bg_desc'] = 'Background color of the active tab, or starting color for gradient.';
$string['secnav_btn_active_gradient'] = 'Active Gradient End Color';
$string['secnav_btn_active_gradient_desc'] = 'Secondary gradient color. Set identical to Active Background for a solid color.';
$string['secnav_btn_active_text'] = 'Active Text Color';
$string['secnav_btn_active_text_desc'] = 'Text color of the active tab.';
$string['secnav_btn_active_border'] = 'Active Border Color';
$string['secnav_btn_active_border_desc'] = 'Border color of the active tab.';
$string['secnav_btn_active_shadow'] = 'Active Button Glow Shadow';
$string['secnav_btn_active_shadow_desc'] = 'Enable a soft colored elevation glow on the active tab.';

$string['heading_secnav_container'] = 'Bar Container & Alignment';
$string['secnav_bar_bg'] = 'Navigation Bar Background';
$string['secnav_bar_bg_desc'] = 'Background color for the container bar. Leave empty for transparent.';
$string['secnav_bar_padding'] = 'Navigation Bar Padding';
$string['secnav_bar_padding_desc'] = 'Padding inside the navigation bar container (e.g. "0.5rem 0.8rem"). Default: "0".';
$string['secnav_bar_radius'] = 'Navigation Bar Border Radius';
$string['secnav_bar_radius_desc'] = 'Corner curvature for the container bar (e.g. "2rem", "12px", "0px"). Default: "0px".';
$string['secnav_align'] = 'Buttons Alignment';
$string['secnav_align_desc'] = 'Horizontal alignment of the navigation buttons.';
$string['secnav_align_left'] = 'Left (Default)';
$string['secnav_align_center'] = 'Center';
$string['secnav_align_right'] = 'Right';

$string['heading_secnav_darkmode'] = 'Dark Mode Adjustments';
$string['heading_secnav_darkmode_desc'] = 'Fine-tune secondary navigation colors when Dark Mode is active.';
$string['dark_secnav_btn_bg'] = 'Dark Mode Button Background';
$string['dark_secnav_btn_bg_desc'] = 'Background color of inactive buttons in Dark Mode.';
$string['dark_secnav_btn_text'] = 'Dark Mode Button Text';
$string['dark_secnav_btn_text_desc'] = 'Text color of inactive buttons in Dark Mode.';
$string['dark_secnav_btn_border'] = 'Dark Mode Button Border';
$string['dark_secnav_btn_border_desc'] = 'Border color of inactive buttons in Dark Mode.';
$string['dark_secnav_btn_hover_bg'] = 'Dark Mode Hover Background';
$string['dark_secnav_btn_hover_bg_desc'] = 'Hover background in Dark Mode.';
$string['dark_secnav_btn_hover_text'] = 'Dark Mode Hover Text';
$string['dark_secnav_btn_hover_text_desc'] = 'Hover text in Dark Mode.';
$string['dark_secnav_btn_active_bg'] = 'Dark Mode Active Background';
$string['dark_secnav_btn_active_bg_desc'] = 'Active tab background in Dark Mode.';
$string['dark_secnav_btn_active_text'] = 'Dark Mode Active Text';
$string['dark_secnav_btn_active_text_desc'] = 'Active tab text in Dark Mode.';
