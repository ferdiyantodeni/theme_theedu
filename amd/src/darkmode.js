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
 * Dark mode manager for theme_theedu.
 *
 * @module     theme_theedu/darkmode
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([], function() {
    'use strict';

    var STORAGE_KEY = 'theedu_theme_mode';

    /**
     * Get preferred theme mode.
     *
     * @param {string} defaultMode
     * @returns {string} 'dark' or 'light'
     */
    function getInitialTheme(defaultMode) {
        var saved = localStorage.getItem(STORAGE_KEY);
        if (saved === 'dark' || saved === 'light') {
            return saved;
        }

        if (defaultMode === 'dark') {
            return 'dark';
        }
        if (defaultMode === 'light') {
            return 'light';
        }

        // Auto mode: check system OS preference
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'dark';
        }

        return 'light';
    }

    /**
     * Apply theme to DOM.
     *
     * @param {string} mode
     */
    function applyTheme(mode) {
        if (mode === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            document.body.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.setAttribute('data-theme', 'light');
            document.body.setAttribute('data-theme', 'light');
        }
        localStorage.setItem(STORAGE_KEY, mode);
    }

    /**
     * Initialize dark mode toggle buttons.
     *
     * @param {string} defaultMode
     */
    function init(defaultMode) {
        defaultMode = defaultMode || 'auto';
        var current = getInitialTheme(defaultMode);
        applyTheme(current);

        // Bind toggle buttons
        document.addEventListener('click', function(e) {
            var toggle = e.target.closest('.theedu-darkmode-toggle');
            if (!toggle) {
                return;
            }
            e.preventDefault();

            var now = document.documentElement.getAttribute('data-theme');
            var next = (now === 'dark') ? 'light' : 'dark';
            applyTheme(next);
        });

        // Listen for OS scheme change if user hasn't explicitly overridden
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                if (!localStorage.getItem(STORAGE_KEY)) {
                    applyTheme(e.matches ? 'dark' : 'light');
                }
            });
        }
    }

    return {
        init: init
    };
});
