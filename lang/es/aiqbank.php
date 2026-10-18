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
 * Spanish language strings for mod_aiqbank.
 *
 * @package    mod_aiqbank
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['aiqbank:addinstance'] = 'Añadir un nuevo generador de banco de preguntas con IA';
$string['aiqbank:generate'] = 'Generar preguntas con IA';
$string['age'] = 'Edad (años)';
$string['apikey'] = 'Clave API';
$string['apikey_desc'] = 'Clave secreta entregada por el proveedor de IA. Se guarda en la configuración de Moodle y nunca se muestra a los docentes.';
$string['audienceheader'] = 'Nivel y formato';
$string['back'] = 'Volver';
$string['baseurl'] = 'URL base (opcional)';
$string['baseurl_desc'] = 'Déjela vacía para usar la del proveedor. Es obligatoria en "Otro (compatible con OpenAI)", por ejemplo https://api.deepseek.com/v1 o un servidor propio. Los servidores en redes privadas deben permitirse en Administración del sitio > General > Seguridad > Seguridad HTTP.';
$string['batchsize'] = 'Preguntas por solicitud';
$string['batchsize_desc'] = 'Las solicitudes grandes se dividen en lotes de este tamaño para evitar respuestas cortadas y tiempos de espera agotados.';
$string['category'] = 'Categoría del banco de preguntas';
$string['category_help'] = 'Las preguntas se guardan en esta categoría del banco de preguntas del curso. Luego, al añadir o editar un Cuestionario, use "Añadir > del banco de preguntas" y elija esta categoría.';
$string['category_new'] = 'Crear una categoría nueva...';
$string['col_answer'] = 'Respuesta correcta';
$string['col_question'] = 'Pregunta';
$string['col_type'] = 'Tipo';
$string['contentheader'] = 'Contenido';
$string['count'] = 'Cantidad de preguntas';
$string['count_help'] = 'Total de preguntas que se crearán. Las preguntas que usted escriba sin alternativas están incluidas en este total.';
$string['created'] = 'Se guardaron {$a->count} pregunta(s) en la categoría "{$a->category}".';
$string['defaultname'] = 'Generar banco de preguntas con IA';
$string['destinationheader'] = 'Dónde guardar';
$string['draft'] = 'Revisar antes de usar';
$string['draft_desc'] = 'Guarda las preguntas como borrador. Un borrador no se puede añadir a un cuestionario hasta marcarlo como listo.';
$string['err_age'] = 'Ingrese una edad entre 2 y 99.';
$string['err_newcategory'] = 'Ingrese un nombre para la nueva categoría.';
$string['err_nocontent'] = 'Ingrese al menos un tema o una pregunta.';
$string['err_toomanystems'] = 'Puede ingresar como máximo {$a} preguntas a la vez.';
$string['err_topicsneeded'] = 'Ingrese al menos un tema para generar las preguntas restantes, o reduzca la cantidad de preguntas.';
$string['error_ai'] = 'Error del proveedor de IA: {$a}';
$string['error_badjson'] = 'No se pudo leer la respuesta de la IA. Inténtelo de nuevo.';
$string['error_blocked'] = 'El proveedor bloqueó la solicitud ({$a}).';
$string['error_connection'] = 'no se pudo conectar ({$a}).';
$string['error_emptyresponse'] = 'respuesta vacía.';
$string['error_noquestions'] = 'La IA no devolvió ninguna pregunta válida. Inténtelo de nuevo o reformule los temas.';
$string['failed'] = 'No se pudieron guardar {$a} pregunta(s).';
$string['fewer'] = 'Solo se crearon {$a->created} de las {$a->requested} preguntas solicitadas.';
$string['generate'] = 'Generar preguntas';
$string['generatemore'] = 'Generar más preguntas';
$string['gotobank'] = 'Abrir el banco de preguntas';
$string['jsonmode'] = 'Solicitar salida en JSON';
$string['jsonmode_desc'] = 'Pide al proveedor que responda en modo JSON. Desactívelo solo si su servidor compatible con OpenAI lo rechaza.';
$string['lang_en'] = 'Inglés';
$string['lang_es'] = 'Español';
$string['lang_fr'] = 'Francés';
$string['lang_pt'] = 'Portugués';
$string['language'] = 'Idioma de las preguntas';
$string['level'] = 'Nivel';
$string['level_custom'] = 'Otra edad...';
$string['level_initial'] = 'Inicial';
$string['level_primary'] = 'Primaria';
$string['level_secondary'] = 'Secundaria';
$string['level_university'] = 'Universitario';
$string['model'] = 'Modelo';
$string['model_desc'] = 'Nombre del modelo. Déjelo vacío para usar el predeterminado del proveedor (Gemini: gemini-2.5-flash, OpenAI: gpt-4o-mini, Grok: grok-3-mini). Revise la documentación de su proveedor para conocer los nombres de modelo vigentes.';
$string['modulename'] = 'Banco de preguntas IA';
$string['modulename_help'] = 'Genera preguntas con inteligencia artificial a partir de uno o varios temas, adaptadas a la edad de sus estudiantes, y las guarda en el banco de preguntas del curso para que pueda añadirlas a un Cuestionario.';
$string['modulenameplural'] = 'Bancos de preguntas IA';
$string['newcategory'] = 'Nombre de la nueva categoría';
$string['noinstances'] = 'No hay actividades de banco de preguntas IA en este curso.';
$string['notconfigured'] = 'El proveedor de IA no está configurado.';
$string['notconfigured_admin'] = 'El proveedor de IA aún no está configurado. <a href="{$a}">Configurar el proveedor y la clave API</a>.';
$string['notconfigured_teacher'] = 'El proveedor de IA aún no está configurado. Solicite al administrador del sitio que lo configure.';
$string['pluginname'] = 'Generador de banco de preguntas con IA';
$string['preview'] = 'Preguntas generadas';
$string['privacy:metadata'] = 'El plugin de banco de preguntas IA no almacena datos personales. Los temas y preguntas que escribe el docente se envían al proveedor de IA configurado por el administrador para generar las preguntas.';
$string['provider'] = 'Proveedor de IA';
$string['provider_custom'] = 'Otro (compatible con OpenAI)';
$string['provider_desc'] = 'Servicio que se usará para generar las preguntas.';
$string['provider_gemini'] = 'Google Gemini';
$string['provider_grok'] = 'xAI Grok';
$string['provider_openai'] = 'OpenAI (ChatGPT)';
$string['qtype'] = 'Tipo de pregunta';
$string['qtype_mixed'] = 'Mixto (opción múltiple + verdadero/falso)';
$string['qtype_multichoice'] = 'Opción múltiple';
$string['qtype_truefalse'] = 'Verdadero/Falso';
$string['saveddraft'] = 'Las preguntas se guardaron como borrador. Abra el banco de preguntas y márquelas como listas después de revisarlas.';
$string['settingsheading'] = 'Proveedor de IA';
$string['settingsheading_desc'] = 'Elija el servicio de inteligencia artificial que usarán los docentes para generar preguntas.';
$string['stems'] = 'Preguntas sin alternativas (opcional)';
$string['temperature'] = 'Temperatura';
$string['temperature_desc'] = 'Creatividad de 0 a 1 (se recomienda 0.7). Déjela vacía para usar el valor predeterminado del proveedor.';
$string['testconnection'] = 'Probar conexión';
$string['testconnection_desc'] = 'Guarde primero la configuración y luego envíe una pequeña solicitud de prueba al proveedor.';
$string['testconnection_run'] = 'Probar la conexión';
$string['testing_ok'] = 'Conexión correcta con {$a->provider} (modelo {$a->model}).';
$string['timeout'] = 'Tiempo de espera (segundos)';
$string['timeout_desc'] = 'Tiempo máximo de espera por cada respuesta del proveedor.';
$string['topics'] = 'Temas';
$string['topics_help'] = 'Un tema por línea (o separados por comas), por ejemplo: Computación, Partes de la computadora, Inteligencia artificial. Las preguntas cubrirán los temas de forma equilibrada.';
$string['levelsheading'] = 'Edad de cada nivel';
$string['levelsheading_desc'] = 'Edad que se envía a la IA para cada nivel del formulario del generador. Adapta el vocabulario y la complejidad cognitiva.';
$string['levelage'] = 'Edad del nivel: {$a}';
$string['levelage_desc'] = 'Años, entre 2 y 99.';
$string['stems_help'] = "Escriba una pregunta por línea (presione Enter después de cada una). La numeración es opcional y se elimina sola (1. / 1) / - / •). También puede separarlas con punto y coma (;). No las separe con comas, porque una pregunta puede contener comas. La IA respeta su redacción y crea las alternativas marcando la correcta. Estas preguntas cuentan dentro del total de preguntas.\n\nEjemplo:\n1. ¿Qué significa CPU?\n2. ¿Qué dispositivo se usa para escribir texto?\n3. ¿Qué es la inteligencia artificial?";
