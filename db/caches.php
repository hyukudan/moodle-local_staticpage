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
 * Cache definitions for local_staticpage.
 *
 * @package    local_staticpage
 * @copyright  2026 PreparaOposiciones
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$definitions = [
    // R22c: published pages list used for prev/next navigation on every legal
    // page view. Pages are admin-edited and change rarely; 24h TTL is fine.
    // Static acceleration is small (1 list).
    'navigation' => [
        'mode' => cache_store::MODE_APPLICATION,
        'staticacceleration' => true,
        'staticaccelerationsize' => 4,
        'simplekeys' => true,
        'simpledata' => false,
        'ttl' => 86400,
    ],
    // R22c: rendered HTML output of format_text for DB-sourced pages. Filter
    // pipeline (multilang, autolink, format_string) is expensive when run on
    // every legal pageview. 24h TTL — invalidates when admin saves the page
    // by purging this area on event handler (see classes/observer.php).
    'rendered_html' => [
        'mode' => cache_store::MODE_APPLICATION,
        'staticacceleration' => true,
        'staticaccelerationsize' => 30,
        'simplekeys' => false,
        'simpledata' => true,
        'ttl' => 86400,
    ],
];
