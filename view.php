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
 * Local plugin "staticpage" - View page
 *
 * Enhanced version with database storage, OG tags, and SEO support.
 *
 * @package    local_staticpage
 * @copyright  2013 Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @copyright  2025 PreparaOposiciones - Enhanced version
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Include config.php.
// phpcs:disable moodle.Files.RequireLogin.Missing
require(__DIR__ . '/../../config.php');

// Globals.
global $CFG, $PAGE, $USER, $FULLME, $DB, $SITE;

// Include lib.php.
require_once($CFG->dirroot . '/local/staticpage/lib.php');

// Get plugin config.
$localstaticpageconfig = get_config('local_staticpage');

// Require login if configured.
if (
    $localstaticpageconfig->forcelogin == STATICPAGE_FORCELOGIN_YES ||
        ($localstaticpageconfig->forcelogin == STATICPAGE_FORCELOGIN_GLOBAL && $CFG->forcelogin)
) {
    require_login();
}

// View only with /static/ URL when Apache rewrite is enabled.
if ($localstaticpageconfig->apacherewrite == true) {
    if (strpos($FULLME, '/static/') === false) {
        die;
    }
}

// Get requested page's slug/name.
$pageslug = required_param('page', PARAM_ALPHANUMEXT);

// Fetch context.
$context = \context_system::instance();

// Initialize page data variables.
$pagedata = null;
$pagecontent = '';
$pagetitle = '';
$metadescription = '';
$ogimage = '';
$source = 'file'; // Track where content comes from.

// PRIORITY 1: Try to fetch from database (new storage).
if ($DB->get_manager()->table_exists('local_staticpage_pages')) {
    $pagedata = $DB->get_record('local_staticpage_pages', [
        'slug' => $pageslug,
        'status' => 1, // Only published pages.
    ]);
}

if ($pagedata) {
    // Content from database.
    $source = 'database';
    $pagetitle = format_string($pagedata->title);
    $pagecontent = $pagedata->content;
    $metadescription = $pagedata->metadescription ?? '';
    $ogimage = $pagedata->ogimage ?? '';
    $contentformat = $pagedata->contentformat;
} else {
    // FALLBACK: Try to fetch from filearea (legacy storage).
    $filename = "$pageslug.html";
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'local_staticpage', 'documents', 0, '/', $filename);

    if (!$file) {
        throw new \moodle_exception('pagenotfound', 'local_staticpage');
    }

    // Get file content and parse HTML.
    $filecontents = $file->get_content();
    $staticdoc = new DOMDocument();
    @$staticdoc->loadHTML('<?xml encoding="UTF-8">' . $filecontents);

    // Extract title from h1 or title tag.
    if (!empty($staticdoc->getElementsByTagName('h1')->item(0)->nodeValue)) {
        $firsth1 = format_string($staticdoc->getElementsByTagName('h1')->item(0)->nodeValue);
    } else {
        $firsth1 = $pageslug;
    }

    if (!empty($staticdoc->getElementsByTagName('title')->item(0)->nodeValue)) {
        $doctitle = format_string($staticdoc->getElementsByTagName('title')->item(0)->nodeValue);
    } else {
        $doctitle = $pageslug;
    }

    // Set title based on config.
    if ($localstaticpageconfig->documenttitlesource == STATICPAGE_TITLE_H1) {
        $pagetitle = $firsth1;
    } else {
        $pagetitle = $doctitle;
    }

    // Extract meta description from HTML if present.
    $metanodes = $staticdoc->getElementsByTagName('meta');
    foreach ($metanodes as $metanode) {
        if ($metanode->getAttribute('name') === 'description') {
            $metadescription = $metanode->getAttribute('content');
            break;
        }
    }

    // Extract body content.
    $body = $staticdoc->getElementsByTagName('body')->item(0);
    if ($body) {
        $pagecontent = $staticdoc->saveHTML($body);
    }

    // Extract and preserve style tags.
    if (!empty($staticdoc->getElementsByTagName('style')->item(0)->nodeValue)) {
        $style = $staticdoc->getElementsByTagName('style')->item(0)->nodeValue;
        $CFG->additionalhtmlhead = $CFG->additionalhtmlhead . '<style>' . $style . '</style>';
    }

    // Extract and preserve link tags.
    if (!empty($staticdoc->getElementsByTagName('link'))) {
        $linknodes = $staticdoc->getElementsByTagName('link');
        foreach ($linknodes as $linknode) {
            $CFG->additionalhtmlhead .= $staticdoc->saveHTML($linknode);
        }
    }

    $contentformat = FORMAT_HTML;
}

// Build the page URL.
if ($localstaticpageconfig->apacherewrite == true) {
    $pageurl = new moodle_url('/static/' . $pageslug . '.html');
} else {
    $pageurl = new moodle_url('/local/staticpage/view.php', ['page' => $pageslug]);
}

// Set page URL.
$PAGE->set_url($pageurl);

// Set page context.
$PAGE->set_context($context);

// Use frontpage layout for transparent navbar effect.
// This gives us the same visual treatment as the landing page.
$PAGE->set_pagelayout('frontpage');

// Add special body classes for static pages.
$PAGE->add_body_class('local-staticpage');
$PAGE->add_body_class('local-staticpage-' . $pageslug);
$PAGE->add_body_class('staticpage-transparent-nav'); // Custom class for navbar styling.

// Set page title and heading.
$PAGE->set_title($pagetitle . ' | ' . $SITE->shortname);
$PAGE->set_heading($pagetitle);

// ============================================================================
// SEO & OG META TAGS
// ============================================================================

// Canonical URL.
$canonicalurl = $pageurl->out(false);

// Default OG image (site logo or custom).
if (empty($ogimage)) {
    // Try to get theme logo or use a default.
    $ogimage = $CFG->wwwroot . '/theme/remui/pix/og-default.png';
}

// Build meta tags.
$metatags = [];

// Basic SEO.
if (!empty($metadescription)) {
    $metatags[] = '<meta name="description" content="' . s($metadescription) . '">';
}
$metatags[] = '<link rel="canonical" href="' . s($canonicalurl) . '">';

// Open Graph tags for Facebook, LinkedIn, etc.
$metatags[] = '<meta property="og:type" content="article">';
$metatags[] = '<meta property="og:title" content="' . s($pagetitle) . '">';
$metatags[] = '<meta property="og:url" content="' . s($canonicalurl) . '">';
$metatags[] = '<meta property="og:site_name" content="' . s($SITE->fullname) . '">';
if (!empty($metadescription)) {
    $metatags[] = '<meta property="og:description" content="' . s($metadescription) . '">';
}
$metatags[] = '<meta property="og:image" content="' . s($ogimage) . '">';
$metatags[] = '<meta property="og:locale" content="es_ES">';

// Twitter Card tags.
$metatags[] = '<meta name="twitter:card" content="summary_large_image">';
$metatags[] = '<meta name="twitter:title" content="' . s($pagetitle) . '">';
if (!empty($metadescription)) {
    $metatags[] = '<meta name="twitter:description" content="' . s($metadescription) . '">';
}
$metatags[] = '<meta name="twitter:image" content="' . s($ogimage) . '">';

// Schema.org JSON-LD for rich snippets.
$schemaorg = [
    '@context' => 'https://schema.org',
    '@type' => 'WebPage',
    'name' => $pagetitle,
    'url' => $canonicalurl,
    'publisher' => [
        '@type' => 'Organization',
        'name' => $SITE->fullname,
        'url' => $CFG->wwwroot,
    ],
];
if (!empty($metadescription)) {
    $schemaorg['description'] = $metadescription;
}
if ($pagedata && !empty($pagedata->timemodified)) {
    $schemaorg['dateModified'] = date('c', $pagedata->timemodified);
}
if ($pagedata && !empty($pagedata->timecreated)) {
    $schemaorg['datePublished'] = date('c', $pagedata->timecreated);
}

$metatags[] = '<script type="application/ld+json">' . json_encode($schemaorg, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';

// Add all meta tags to HTML head.
$CFG->additionalhtmlhead = $CFG->additionalhtmlhead . "\n" . implode("\n", $metatags);

// ============================================================================
// OUTPUT PAGE
// ============================================================================

// Use page helper for enhanced features.
use local_staticpage\page_helper;

echo $OUTPUT->header();

// Generate breadcrumbs.
echo page_helper::generate_breadcrumbs($pagetitle, $canonicalurl);

// Wrap content in a styled container for static pages.
echo '<div class="staticpage-content-wrapper">';
echo '<article class="staticpage-article">';

// Reading time and page meta.
$readingtime = page_helper::calculate_reading_time($pagecontent);
echo '<div class="staticpage-meta">';
echo '<span class="reading-time"><i class="fa fa-clock-o"></i> ' . $readingtime['formatted'] . '</span>';
echo '</div>';

// Process content and generate TOC.
$tocresult = page_helper::generate_toc($pagecontent);
$processedcontent = $tocresult['content'];

// Show TOC if there are headings.
if (!empty($tocresult['toc'])) {
    echo $tocresult['toc'];
}

// Page content.
if ($source === 'database') {
    // Database content - format with Moodle text filters.
    echo format_text($processedcontent, $contentformat, [
        'trusted' => true,
        'noclean' => true,
        'filter' => true,
        'context' => $context,
    ]);
} else {
    // File content - respect plugin settings.
    if (
        $localstaticpageconfig->processfilters == STATICPAGE_PROCESSFILTERS_YES &&
            $localstaticpageconfig->cleanhtml == STATICPAGE_CLEANHTML_YES
    ) {
        echo format_text($processedcontent, FORMAT_HTML, ['trusted' => false, 'noclean' => false, 'filter' => true]);
    } else if (
        $localstaticpageconfig->processfilters == STATICPAGE_PROCESSFILTERS_YES &&
            $localstaticpageconfig->cleanhtml == STATICPAGE_CLEANHTML_NO
    ) {
        echo format_text($processedcontent, FORMAT_HTML, ['trusted' => true, 'noclean' => true, 'filter' => true]);
    } else if (
        $localstaticpageconfig->processfilters == STATICPAGE_PROCESSFILTERS_NO &&
            $localstaticpageconfig->cleanhtml == STATICPAGE_CLEANHTML_YES
    ) {
        echo format_text($processedcontent, FORMAT_HTML, ['trusted' => false, 'noclean' => false, 'filter' => false]);
    } else if (
        $localstaticpageconfig->processfilters == STATICPAGE_PROCESSFILTERS_NO &&
            $localstaticpageconfig->cleanhtml == STATICPAGE_CLEANHTML_NO
    ) {
        echo format_text($processedcontent, FORMAT_HTML, ['trusted' => true, 'noclean' => true, 'filter' => false]);
    } else {
        echo $processedcontent;
    }
}

// Last modified date (for database pages).
if ($pagedata && !empty($pagedata->timemodified)) {
    echo page_helper::format_last_modified($pagedata->timemodified);
}

// Social sharing buttons.
echo page_helper::generate_share_buttons($canonicalurl, $pagetitle, $metadescription);

// Previous/Next navigation (for database pages).
if ($source === 'database') {
    $pagenav = page_helper::get_page_navigation($pageslug);
    if ($pagenav['prev'] || $pagenav['next']) {
        echo '<nav class="staticpage-pagination">';
        if ($pagenav['prev']) {
            $prevurl = new moodle_url('/local/staticpage/view.php', ['page' => $pagenav['prev']->slug]);
            echo '<a href="' . $prevurl . '" class="pagination-prev">';
            echo '<i class="fa fa-arrow-left"></i> ' . get_string('previouspage', 'local_staticpage');
            echo '<span class="pagination-title">' . s($pagenav['prev']->title) . '</span>';
            echo '</a>';
        } else {
            echo '<span class="pagination-prev disabled"></span>';
        }
        if ($pagenav['next']) {
            $nexturl = new moodle_url('/local/staticpage/view.php', ['page' => $pagenav['next']->slug]);
            echo '<a href="' . $nexturl . '" class="pagination-next">';
            echo get_string('nextpage', 'local_staticpage') . ' <i class="fa fa-arrow-right"></i>';
            echo '<span class="pagination-title">' . s($pagenav['next']->title) . '</span>';
            echo '</a>';
        } else {
            echo '<span class="pagination-next disabled"></span>';
        }
        echo '</nav>';
    }
}

echo '</article>';
echo '</div>';

// Log this view.
$logevent = \local_staticpage\event\staticpage_viewed::create([
    'userid' => $USER->id,
    'context' => $context,
    'other' => [
        'title' => $pagetitle,
        'page' => $pageslug,
        'source' => $source,
    ],
]);
$logevent->trigger();

echo $OUTPUT->footer();
