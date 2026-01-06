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
 * Local plugin "staticpage" - Spanish language pack
 *
 * @package    local_staticpage
 * @copyright  2025 PreparaOposiciones
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Páginas Estáticas';
$string['pagenotfound'] = 'Página no encontrada';

// Enhanced static pages strings.
$string['tableofcontents'] = 'Índice de contenidos';
$string['readingtime'] = '{$a} min de lectura';
$string['sharethispage'] = 'Compartir:';
$string['shareon'] = 'Compartir en {$a}';
$string['copylink'] = 'Copiar enlace';
$string['linkcopied'] = 'Enlace copiado al portapapeles';
$string['breadcrumbs'] = 'Migas de pan';
$string['lastupdated'] = 'Última actualización: {$a}';
$string['previouspage'] = 'Anterior';
$string['nextpage'] = 'Siguiente';
$string['managepages'] = 'Gestionar páginas estáticas';
$string['addnewpage'] = 'Añadir nueva página';
$string['editpage'] = 'Editar página';
$string['deletepage'] = 'Eliminar página';
$string['pageslug'] = 'Slug URL';
$string['pageslug_help'] = 'Identificador amigable para URL (ej: como-funciona). Solo letras minúsculas, números y guiones.';
$string['pagetitle'] = 'Título de la página';
$string['pagecontent'] = 'Contenido de la página';
$string['metadescription'] = 'Meta descripción';
$string['metadescription_help'] = 'Descripción corta para buscadores y redes sociales (máx 160 caracteres).';
$string['ogimage'] = 'Imagen para redes sociales';
$string['ogimage_help'] = 'URL de imagen para compartir en redes sociales (recomendado: 1200x630 píxeles).';
$string['pagestatus'] = 'Estado';
$string['statusdraft'] = 'Borrador';
$string['statuspublished'] = 'Publicado';
$string['showintoc'] = 'Mostrar en navegación';
$string['showintoc_help'] = 'Incluir esta página en los menús de navegación del sitio.';
$string['sortorder'] = 'Orden';
$string['pagesaved'] = 'Página guardada correctamente';
$string['pagedeleted'] = 'Página eliminada correctamente';
$string['confirmdelete'] = '¿Estás seguro de que quieres eliminar la página "{$a}"?';

// Settings page strings (inherited from original plugin).
$string['apacherewrite'] = 'Forzar Apache mod_rewrite';
$string['documents'] = 'Documentos';
$string['forcelogin'] = 'Forzar inicio de sesión';
$string['cleanhtml'] = 'Limpiar código HTML';
$string['processfilters'] = 'Procesar filtros';
$string['staticpage:managedocuments'] = 'Gestionar documentos de páginas estáticas';
