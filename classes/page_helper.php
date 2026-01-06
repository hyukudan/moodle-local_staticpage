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
 * Helper class for static page rendering features.
 *
 * @package    local_staticpage
 * @copyright  2025 PreparaOposiciones
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_staticpage;

defined('MOODLE_INTERNAL') || die();

/**
 * Helper class providing utility functions for static pages.
 */
class page_helper {

    /**
     * Generate table of contents from HTML content.
     *
     * Extracts H2 and H3 headings and creates a nested TOC structure.
     *
     * @param string $content HTML content to parse
     * @return array Array with 'toc' (HTML string) and 'content' (modified HTML with IDs)
     */
    public static function generate_toc(string $content): array {
        // Find all H2 and H3 headings.
        $pattern = '/<h([23])([^>]*)>([^<]+)<\/h[23]>/i';
        $headings = [];
        $tocitems = [];

        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $index => $match) {
                $level = (int)$match[1];
                $attrs = $match[2];
                $text = strip_tags($match[3]);
                $slug = self::slugify($text) . '-' . $index;

                $headings[] = [
                    'original' => $match[0],
                    'level' => $level,
                    'text' => $text,
                    'slug' => $slug,
                ];

                $tocitems[] = [
                    'level' => $level,
                    'text' => $text,
                    'slug' => $slug,
                ];
            }
        }

        // Replace headings with ID-enhanced versions.
        foreach ($headings as $heading) {
            $replacement = sprintf(
                '<h%d id="%s"%s>%s</h%d>',
                $heading['level'],
                $heading['slug'],
                '', // Original attributes could be preserved here.
                $heading['text'],
                $heading['level']
            );
            $content = str_replace($heading['original'], $replacement, $content);
        }

        // Build TOC HTML.
        $tochtml = '';
        if (!empty($tocitems)) {
            $tochtml = '<nav class="staticpage-toc" aria-label="' . get_string('tableofcontents', 'local_staticpage') . '">';
            $tochtml .= '<h4 class="toc-title">' . get_string('tableofcontents', 'local_staticpage') . '</h4>';
            $tochtml .= '<ul class="toc-list">';

            $currentlevel = 2;
            foreach ($tocitems as $item) {
                if ($item['level'] > $currentlevel) {
                    $tochtml .= '<ul class="toc-sublist">';
                } else if ($item['level'] < $currentlevel) {
                    $tochtml .= '</ul>';
                }
                $currentlevel = $item['level'];

                $tochtml .= sprintf(
                    '<li class="toc-item toc-level-%d"><a href="#%s">%s</a></li>',
                    $item['level'],
                    $item['slug'],
                    s($item['text'])
                );
            }

            // Close any open sublists.
            while ($currentlevel > 2) {
                $tochtml .= '</ul>';
                $currentlevel--;
            }

            $tochtml .= '</ul></nav>';
        }

        return [
            'toc' => $tochtml,
            'content' => $content,
        ];
    }

    /**
     * Calculate estimated reading time.
     *
     * @param string $content HTML content
     * @param int $wordsperminute Average reading speed (default 200 wpm)
     * @return array Array with 'minutes' (int) and 'formatted' (string)
     */
    public static function calculate_reading_time(string $content, int $wordsperminute = 200): array {
        // Strip HTML tags and count words.
        $text = strip_tags($content);
        $wordcount = str_word_count($text);
        $minutes = max(1, (int)ceil($wordcount / $wordsperminute));

        return [
            'minutes' => $minutes,
            'wordcount' => $wordcount,
            'formatted' => get_string('readingtime', 'local_staticpage', $minutes),
        ];
    }

    /**
     * Generate social sharing buttons.
     *
     * @param string $url Page URL to share
     * @param string $title Page title
     * @param string $description Optional description for some platforms
     * @return string HTML for sharing buttons
     */
    public static function generate_share_buttons(string $url, string $title, string $description = ''): string {
        $encodedurl = urlencode($url);
        $encodedtitle = urlencode($title);
        $encodeddesc = urlencode($description);

        $buttons = [
            'twitter' => [
                'url' => "https://twitter.com/intent/tweet?url={$encodedurl}&text={$encodedtitle}",
                'icon' => 'fa-twitter',
                'label' => 'Twitter',
                'class' => 'share-btn-twitter',
            ],
            'linkedin' => [
                'url' => "https://www.linkedin.com/sharing/share-offsite/?url={$encodedurl}",
                'icon' => 'fa-linkedin',
                'label' => 'LinkedIn',
                'class' => 'share-btn-linkedin',
            ],
            'whatsapp' => [
                'url' => "https://wa.me/?text={$encodedtitle}%20{$encodedurl}",
                'icon' => 'fa-whatsapp',
                'label' => 'WhatsApp',
                'class' => 'share-btn-whatsapp',
            ],
            'facebook' => [
                'url' => "https://www.facebook.com/sharer/sharer.php?u={$encodedurl}",
                'icon' => 'fa-facebook',
                'label' => 'Facebook',
                'class' => 'share-btn-facebook',
            ],
            'telegram' => [
                'url' => "https://t.me/share/url?url={$encodedurl}&text={$encodedtitle}",
                'icon' => 'fa-telegram',
                'label' => 'Telegram',
                'class' => 'share-btn-telegram',
            ],
        ];

        $html = '<div class="share-buttons">';
        $html .= '<span class="share-label">' . get_string('sharethispage', 'local_staticpage') . '</span>';

        foreach ($buttons as $platform => $data) {
            $html .= sprintf(
                '<a href="%s" class="share-btn %s" target="_blank" rel="noopener noreferrer" title="%s">' .
                '<i class="fa %s"></i><span class="sr-only">%s</span></a>',
                $data['url'],
                $data['class'],
                get_string('shareon', 'local_staticpage', $data['label']),
                $data['icon'],
                $data['label']
            );
        }

        // Copy link button.
        $html .= sprintf(
            '<button type="button" class="share-btn share-btn-copy" data-url="%s" title="%s">' .
            '<i class="fa fa-link"></i><span class="sr-only">%s</span></button>',
            s($url),
            get_string('copylink', 'local_staticpage'),
            get_string('copylink', 'local_staticpage')
        );

        $html .= '</div>';

        return $html;
    }

    /**
     * Generate breadcrumb navigation.
     *
     * @param string $pagetitle Current page title
     * @param string $pageurl Current page URL
     * @return string HTML breadcrumb
     */
    public static function generate_breadcrumbs(string $pagetitle, string $pageurl): string {
        global $CFG, $SITE;

        $html = '<nav class="staticpage-breadcrumbs" aria-label="' . get_string('breadcrumbs', 'local_staticpage') . '">';
        $html .= '<ol class="breadcrumb-list">';

        // Home link.
        $html .= sprintf(
            '<li class="breadcrumb-item"><a href="%s"><i class="fa fa-home"></i> %s</a></li>',
            $CFG->wwwroot,
            get_string('home')
        );

        // Current page (no link).
        $html .= sprintf(
            '<li class="breadcrumb-item active" aria-current="page">%s</li>',
            s($pagetitle)
        );

        $html .= '</ol></nav>';

        return $html;
    }

    /**
     * Format last modified date.
     *
     * @param int $timestamp Unix timestamp
     * @return string Formatted date string
     */
    public static function format_last_modified(int $timestamp): string {
        if ($timestamp <= 0) {
            return '';
        }

        return '<div class="last-updated">' .
               '<i class="fa fa-clock-o"></i> ' .
               get_string('lastupdated', 'local_staticpage', userdate($timestamp, get_string('strftimedatefullshort'))) .
               '</div>';
    }

    /**
     * Convert text to URL-friendly slug.
     *
     * @param string $text Text to slugify
     * @return string URL-friendly slug
     */
    public static function slugify(string $text): string {
        // Convert to lowercase.
        $text = mb_strtolower($text, 'UTF-8');

        // Replace accented characters.
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);

        // Replace non-alphanumeric with hyphens.
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);

        // Remove leading/trailing hyphens.
        $text = trim($text, '-');

        return $text ?: 'section';
    }

    /**
     * Get all published pages for navigation/sitemap.
     *
     * @return array Array of page objects
     */
    public static function get_published_pages(): array {
        global $DB;

        if (!$DB->get_manager()->table_exists('local_staticpage_pages')) {
            return [];
        }

        return $DB->get_records('local_staticpage_pages', [
            'status' => 1,
            'showintoc' => 1,
        ], 'sortorder ASC, title ASC');
    }

    /**
     * Generate pages navigation (prev/next links).
     *
     * @param string $currentslug Current page slug
     * @return array Array with 'prev' and 'next' page objects (or null)
     */
    public static function get_page_navigation(string $currentslug): array {
        $pages = self::get_published_pages();
        $pagesarray = array_values($pages);
        $currentindex = null;

        foreach ($pagesarray as $index => $page) {
            if ($page->slug === $currentslug) {
                $currentindex = $index;
                break;
            }
        }

        $prev = null;
        $next = null;

        if ($currentindex !== null) {
            if ($currentindex > 0) {
                $prev = $pagesarray[$currentindex - 1];
            }
            if ($currentindex < count($pagesarray) - 1) {
                $next = $pagesarray[$currentindex + 1];
            }
        }

        return [
            'prev' => $prev,
            'next' => $next,
        ];
    }
}
