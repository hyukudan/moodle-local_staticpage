# moodle-local_staticpage (Fork PreparaOposiciones)

**Fork del plugin original [moodle-an-hochschulen/moodle-local_staticpage](https://github.com/moodle-an-hochschulen/moodle-local_staticpage) con funcionalidades extendidas para SEO, tabla de contenidos y compartir en redes sociales.**

Este fork es mantenido por [PreparaOposiciones](https://www.preparaoposiciones.com) para uso en nuestra plataforma de preparacion de oposiciones.

---

## Funcionalidades Nuevas (Fork)

### 1. SEO y Meta Tags

- **Meta description**: Campo para descripcion SEO personalizada por pagina
- **Open Graph**: Tags completos para compartir en Facebook, LinkedIn, etc.
- **Twitter Cards**: Soporte para cards de resumen con imagen
- **Schema.org JSON-LD**: Datos estructurados para rich snippets en Google
- **Canonical URLs**: URLs canonicas automaticas
- **OG Image**: Campo para imagen personalizada al compartir

### 2. Tabla de Contenidos Automatica

- Genera automaticamente un indice navegable desde los encabezados H2 y H3
- Sticky sidebar en desktop para navegacion rapida
- Smooth scroll al hacer clic en los enlaces
- Se muestra solo si hay 2+ secciones

### 3. Compartir en Redes Sociales

Botones para compartir la pagina en:
- Twitter/X
- Facebook
- LinkedIn
- WhatsApp
- Telegram
- Copiar enlace al portapapeles

### 4. Mejoras de UX

- **Tiempo de lectura**: Calculo automatico basado en el contenido
- **Navegacion prev/next**: Enlaces a pagina anterior y siguiente
- **Breadcrumbs**: Migas de pan para mejor navegacion
- **Layout frontpage**: Usa el layout de portada para efecto de navbar transparente

### 5. Base de Datos Extendida

Nueva tabla `local_staticpage_pages` con campos adicionales:
- `slug`: Identificador URL amigable
- `title`: Titulo de la pagina
- `content`: Contenido HTML
- `metadescription`: Descripcion SEO
- `ogimage`: URL de imagen para redes sociales
- `status`: draft/published
- `sortorder`: Orden para navegacion

### 6. Idioma Espanol

Pack de idioma completo en espanol (es).

---

## Instalacion

1. Clonar o descargar este repositorio
2. Copiar a `/local/staticpage/` en tu instalacion Moodle
3. Visitar la pagina de administracion para completar la instalacion
4. Configurar en: Administracion del sitio -> Static Pages

---

## Uso

### Subir Paginas

Las paginas se suben como archivos HTML en:
**Administracion del sitio -> Static Pages -> Documents**

### Estructura HTML Recomendada

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Titulo de la Pagina</title>
    <style>
        /* Estilos CSS personalizados */
    </style>
</head>
<body>
    <h1>Titulo Principal</h1>

    <h2>Primera Seccion</h2>
    <p>Contenido...</p>

    <h2>Segunda Seccion</h2>
    <p>Mas contenido...</p>
</body>
</html>
```

### URLs

Las paginas estan disponibles en:
```
https://tudominio.com/local/staticpage/view.php?page=nombre-pagina
```

Con Apache mod_rewrite:
```
https://tudominio.com/static/nombre-pagina.html
```

---

## Archivos Modificados/Nuevos

| Archivo | Descripcion |
|---------|-------------|
| `classes/page_helper.php` | **NUEVO** - Clase con utilidades: TOC, tiempo lectura, compartir |
| `view.php` | **MODIFICADO** - Logica extendida para SEO y nuevas funcionalidades |
| `styles.css` | **MODIFICADO** - Estilos para TOC, botones compartir, navegacion |
| `db/install.xml` | **MODIFICADO** - Nueva tabla para metadatos de paginas |
| `db/upgrade.php` | **MODIFICADO** - Migracion para instalaciones existentes |
| `lang/es/` | **NUEVO** - Pack de idioma espanol |

---

## Compatibilidad

- **Moodle**: 5.1+
- **PHP**: 8.1+
- **Tema**: Probado con RemUI (Edwiser), deberia funcionar con Boost y derivados

---

## Upstream

Este fork se basa en la version 5.1-r1 del plugin original:
- **Original**: https://github.com/moodle-an-hochschulen/moodle-local_staticpage
- **Documentacion original**: Ver seccion "Documentacion Original" abajo

Para actualizar desde upstream:
```bash
git remote add upstream https://github.com/moodle-an-hochschulen/moodle-local_staticpage.git
git fetch upstream
git merge upstream/main
```

---

## Mantenimiento

Este fork es mantenido por:
- **Organizacion**: Fase Consulting Ibiza, S.L.
- **Proyecto**: PreparaOposiciones
- **Repositorio**: https://github.com/hyukudan/moodle-local_staticpage

---

# Documentacion Original

El resto de este documento contiene la documentacion original del plugin upstream.

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
