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
 * English language strings for mod_aiqbank.
 *
 * @package    mod_aiqbank
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['aiqbank:addinstance'] = 'Add a new AI question bank generator';
$string['aiqbank:generate'] = 'Generate questions with AI';
$string['age'] = 'Age (years)';
$string['apikey'] = 'API key';
$string['apikey_desc'] = 'Secret key issued by the AI provider. It is stored in the Moodle configuration and never shown to teachers.';
$string['audienceheader'] = 'Level and format';
$string['back'] = 'Back';
$string['baseurl'] = 'Base URL (optional)';
$string['baseurl_desc'] = 'Leave empty to use the provider default. Required for "Other (OpenAI compatible)", for example https://api.deepseek.com/v1 or a self-hosted server. Servers on private networks must be allowed in Site administration > General > Security > HTTP security.';
$string['batchsize'] = 'Questions per request';
$string['batchsize_desc'] = 'Large requests are split into batches of this size to avoid truncated answers and timeouts.';
$string['category'] = 'Question bank category';
$string['category_help'] = 'Questions are saved in this category of the course question bank. Later, when you add or edit a Quiz, use "Add > from question bank" and choose this category.';
$string['category_new'] = 'Create a new category...';
$string['col_answer'] = 'Correct answer';
$string['col_question'] = 'Question';
$string['col_type'] = 'Type';
$string['contentheader'] = 'Content';
$string['count'] = 'Number of questions';
$string['count_help'] = 'Total number of questions to create. Questions you typed yourself (without options) are included in this total.';
$string['created'] = '{$a->count} question(s) were saved in the category "{$a->category}".';
$string['defaultname'] = 'Generate question bank with AI';
$string['destinationheader'] = 'Where to save';
$string['draft'] = 'Review before use';
$string['draft_desc'] = 'Save the questions as drafts. Drafts cannot be added to a quiz until you mark them as ready.';
$string['err_age'] = 'Enter an age between 2 and 99.';
$string['err_newcategory'] = 'Enter a name for the new category.';
$string['err_nocontent'] = 'Enter at least one topic or one question.';
$string['err_toomanystems'] = 'You can enter at most {$a} questions at a time.';
$string['err_topicsneeded'] = 'Enter at least one topic to generate the remaining questions, or lower the number of questions.';
$string['error_ai'] = 'AI provider error: {$a}';
$string['error_badjson'] = 'The AI answer could not be read. Please try again.';
$string['error_blocked'] = 'The request was blocked by the provider ({$a}).';
$string['error_connection'] = 'could not connect ({$a}).';
$string['error_emptyresponse'] = 'empty response.';
$string['error_noquestions'] = 'The AI did not return any valid question. Try again or rephrase the topics.';
$string['failed'] = '{$a} question(s) could not be saved.';
$string['fewer'] = 'Only {$a->created} of the {$a->requested} requested questions were created.';
$string['generate'] = 'Generate questions';
$string['generatemore'] = 'Generate more questions';
$string['gotobank'] = 'Open the question bank';
$string['jsonmode'] = 'Request JSON output';
$string['jsonmode_desc'] = 'Asks the provider to answer in JSON mode. Disable only if your OpenAI-compatible server rejects it.';
$string['lang_en'] = 'English';
$string['lang_es'] = 'Spanish';
$string['lang_fr'] = 'French';
$string['lang_pt'] = 'Portuguese';
$string['language'] = 'Language of the questions';
$string['level'] = 'Level';
$string['level_custom'] = 'Other age...';
$string['level_initial'] = 'Preschool';
$string['level_primary'] = 'Primary';
$string['level_secondary'] = 'Secondary';
$string['level_university'] = 'University';
$string['model'] = 'Model';
$string['model_desc'] = 'Model name. Leave empty to use the default of the provider (Gemini: gemini-2.5-flash, OpenAI: gpt-4o-mini, Grok: grok-3-mini). Check your provider documentation for the current model names.';
$string['modulename'] = 'AI question bank';
$string['modulename_help'] = 'Generates questions with artificial intelligence from one or more topics, adapted to the age of your students, and saves them in the course question bank so you can add them to a Quiz.';
$string['modulenameplural'] = 'AI question banks';
$string['newcategory'] = 'New category name';
$string['noinstances'] = 'There are no AI question bank activities in this course.';
$string['notconfigured'] = 'The AI provider is not configured.';
$string['notconfigured_admin'] = 'The AI provider is not configured yet. <a href="{$a}">Configure the provider and API key</a>.';
$string['notconfigured_teacher'] = 'The AI provider is not configured yet. Please ask the site administrator to set it up.';
$string['pluginname'] = 'AI question bank generator';
$string['preview'] = 'Generated questions';
$string['privacy:metadata'] = 'The AI question bank plugin does not store personal data. The topics and questions typed by the teacher are sent to the AI provider configured by the administrator to generate the questions.';
$string['provider'] = 'AI provider';
$string['provider_custom'] = 'Other (OpenAI compatible)';
$string['provider_desc'] = 'Service used to generate the questions.';
$string['provider_gemini'] = 'Google Gemini';
$string['provider_grok'] = 'xAI Grok';
$string['provider_openai'] = 'OpenAI (ChatGPT)';
$string['qtype'] = 'Question type';
$string['qtype_mixed'] = 'Mixed (multiple choice + true/false)';
$string['qtype_multichoice'] = 'Multiple choice';
$string['qtype_truefalse'] = 'True/False';
$string['saveddraft'] = 'The questions were saved as drafts. Open the question bank and mark them as ready after reviewing them.';
$string['settingsheading'] = 'AI provider';
$string['settingsheading_desc'] = 'Choose the artificial intelligence service used by teachers to generate questions.';
$string['stems'] = 'Questions without options (optional)';
$string['temperature'] = 'Temperature';
$string['temperature_desc'] = 'Creativity from 0 to 1 (0.7 recommended). Leave empty to use the provider default.';
$string['testconnection'] = 'Test connection';
$string['testconnection_desc'] = 'Save the settings first, then send a small test request to the provider.';
$string['testconnection_run'] = 'Test the connection';
$string['testing_ok'] = 'Connection successful with {$a->provider} (model {$a->model}).';
$string['timeout'] = 'Timeout (seconds)';
$string['timeout_desc'] = 'Maximum time to wait for each response from the provider.';
$string['topics'] = 'Topics';
$string['topics_help'] = 'One topic per line (or separated by commas), for example: Computing, Parts of the computer, Artificial intelligence. The questions will cover the topics evenly.';
$string['levelsheading'] = 'Age of each level';
$string['levelsheading_desc'] = 'Age sent to the AI for each level in the generator form. It adapts vocabulary and cognitive complexity.';
$string['levelage'] = 'Age for level: {$a}';
$string['levelage_desc'] = 'Years, between 2 and 99.';
$string['stems_help'] = "Type one question per line (press Enter after each one). Numbering is optional and is removed automatically (1. / 1) / - / •). You can also separate questions with a semicolon (;). Do not separate them with commas, because a question may contain commas. The AI keeps your wording and creates the answer options marking the correct one. These questions count towards the total number of questions.\n\nExample:\n1. What does CPU stand for?\n2. Which device is used to type text?\n3. What is artificial intelligence?";
