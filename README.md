# moodle-local_staticpage (PreparaOposiciones Fork)

**Fork of the original [moodle-an-hochschulen/moodle-local_staticpage](https://github.com/moodle-an-hochschulen/moodle-local_staticpage) plugin with extended features for SEO, table of contents, and social sharing.**

This fork is maintained by [PreparaOposiciones](https://www.preparaoposiciones.com) for use in our exam preparation platform.

---

## New Features (Fork)

### 1. SEO & Meta Tags

- **Meta description**: Custom SEO description field per page
- **Open Graph**: Full OG tags for sharing on Facebook, LinkedIn, etc.
- **Twitter Cards**: Summary card with image support
- **Schema.org JSON-LD**: Structured data for Google rich snippets
- **Canonical URLs**: Automatic canonical URL generation
- **OG Image**: Custom social sharing image field

### 2. Automatic Table of Contents

- Automatically generates a navigable index from H2 and H3 headings
- Sticky sidebar on desktop for quick navigation
- Smooth scroll when clicking links
- Only displays when there are 2+ sections

### 3. Social Sharing Buttons

Share buttons for:
- Twitter/X
- Facebook
- LinkedIn
- WhatsApp
- Telegram
- Copy link to clipboard

### 4. UX Improvements

- **Reading time**: Automatic calculation based on content length
- **Prev/Next navigation**: Links to previous and next pages
- **Breadcrumbs**: Breadcrumb navigation for better UX
- **Frontpage layout**: Uses frontpage layout for transparent navbar effect

### 5. Extended Database

New `local_staticpage_pages` table with additional fields:
- `slug`: URL-friendly identifier
- `title`: Page title
- `content`: HTML content
- `metadescription`: SEO description
- `ogimage`: Social media image URL
- `status`: draft/published
- `sortorder`: Order for navigation

### 6. Spanish Language Pack

Complete Spanish (es) language pack included.

---

## Installation

1. Clone or download this repository
2. Copy to `/local/staticpage/` in your Moodle installation
3. Visit the admin page to complete installation
4. Configure at: Site administration -> Static Pages

---

## Usage

### Uploading Pages

Pages are uploaded as HTML files at:
**Site administration -> Static Pages -> Documents**

### Recommended HTML Structure

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Page Title</title>
    <style>
        /* Custom CSS styles */
    </style>
</head>
<body>
    <h1>Main Title</h1>

    <h2>First Section</h2>
    <p>Content...</p>

    <h2>Second Section</h2>
    <p>More content...</p>
</body>
</html>
```

### URLs

Pages are available at:
```
https://yourdomain.com/local/staticpage/view.php?page=page-name
```

With Apache mod_rewrite:
```
https://yourdomain.com/static/page-name.html
```

---

## Modified/New Files

| File | Description |
|------|-------------|
| `classes/page_helper.php` | **NEW** - Utility class: TOC, reading time, sharing |
| `view.php` | **MODIFIED** - Extended logic for SEO and new features |
| `styles.css` | **MODIFIED** - Styles for TOC, share buttons, navigation |
| `db/install.xml` | **MODIFIED** - New table for page metadata |
| `db/upgrade.php` | **MODIFIED** - Migration for existing installations |
| `lang/es/` | **NEW** - Spanish language pack |

---

## Compatibility

- **Moodle**: 5.1+
- **PHP**: 8.1+
- **Theme**: Tested with RemUI (Edwiser), should work with Boost and derivatives

---

## Upstream

This fork is based on version 5.1-r1 of the original plugin:
- **Original**: https://github.com/moodle-an-hochschulen/moodle-local_staticpage
- **Original documentation**: See "Original Documentation" section below

To update from upstream:
```bash
git remote add upstream https://github.com/moodle-an-hochschulen/moodle-local_staticpage.git
git fetch upstream
git merge upstream/main
```

---

## Maintainers

This fork is maintained by:
- **Organization**: Fase Consulting Ibiza, S.L.
- **Project**: PreparaOposiciones
- **Repository**: https://github.com/hyukudan/moodle-local_staticpage

---

# Original Documentation

The rest of this document contains the original documentation from the upstream plugin.

---

## Requirements

This plugin requires Moodle 5.1+


## Motivation for this plugin

We have seen Moodle installations where there was a need for displaying static information like an imprint, a faq or a contact page and this information couldn't be added everything to the frontpage. As Moodle doesn't have a "page" concept, admins started to create courses, place their information within these courses, open guest access to the course and link to this course from HTML blocks or the custom menu.

We thought that this course overhead doesn't make sense, so we created this plugin. It is designed to deliver static HTML documents, enriched with Moodle layout and navigation as a standard Moodle page which exist outside any course. Static pages will be available on catchy URLs like http://www.yourmoodle.com/static/faq.html and can be linked from Moodle HTML blocks, from your Moodle theme footer and so on.

Using this plugin, you can create information pages within moodle, but without misusing a whole course just for showing a textbox. It is not meant as a fully featured content management solution, especially as you have to work with raw HTML, but it is quite handy for experienced admins for creating some few static pages within Moodle.


## Usage & Settings

After installing the plugin, it does not do anything to Moodle yet.

To configure the plugin and its behaviour, please visit:
Site administration -> Static Pages.

There, you find multiple settings pages:

### 1. Documents

On this page, you upload the document files you want to serve as static pages. The filepicker accepts files with .html filename extensions. For each static page you want to serve, upload a HTML document, named as [pagename].html. local_staticpage then uses this filename as pagename.

Example:
You upload a file named faq.html. This file will be served as static page with the page name "faq".

Valid filenames:
Please note that not all symbols which are allowed in the filenames in the filepicker are supported / suitable for pagenames.
Please make sure that your filenames only contain lowercase alphanumeric characters and the - (hypen) and _ (underscore) symbols.

### 2. Settings

On this page, you can configure several aspects of local_staticpage's behaviour.

#### 2.1. Data source of document title

By default, local_staticpage will use the first `<h1>` tag as document title and document heading of the resulting static page.
In this section, you can change this behaviour to using the first `<title>` tag for each of these.

#### 2.2. Force Apache mod_rewrite

With this setting, you can configure local_staticpage to only serve static pages on a clean URL, using Apache's mod_rewrite module.

#### 2.3. Force login

With this setting, you can configure local_staticpage to only serve static pages to logged in users or also to service static pages non-logged in visitors.

#### 2.4. Process Content

In this section, you can configure if Moodle filters should be processed when serving a static page's content.

### 3. List of static pages

On this page, there is a list which shows all static pages which have been uploaded into the static pages document area and their URLs.


## Apache mod_rewrite

### Using mod_rewrite

local_staticpage is able to use Apache's mod_rewrite module to provide static pages on a clean and understandable URL.

If you are running Moodle in the root of your webserver, please add the following to your Apache configuration or your .htaccess file in the Moodle directory:

```
RewriteEngine On
RewriteRule ^/static/(.*)\.html$ /local/staticpage/view.php?page=$1&%{QUERY_STRING} [L]
```

Now, the static pages are available on
http://www.yourmoodle.com/static/[pagename].html

### Not using mod_rewrite

If you don't want or are unable to use Apache's mod_rewrite, local_staticpage will still work.

The static pages are then available on
http://www.yourmoodle.com/local/staticpage/view.php?page=[pagename]


## Security considerations

Apart from the option to clean HTML code which you can set to yes, local_staticpage does NOT check the static HTML documents for any malicious code. Please make sure that your HTML code is well-formed and that only authorized users upload static page documents.


## Copyright

**Original plugin:**
- Moodle an Hochschulen e.V.
- Initially built by Ulm University, Communication and Information Centre (kiz), Alexander Bias

**Fork modifications:**
- Fase Consulting Ibiza, S.L. (PreparaOposiciones)
