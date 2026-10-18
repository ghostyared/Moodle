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

namespace mod_aiqbank\local;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/filelib.php');

/**
 * Minimal client for the supported AI providers (Gemini, OpenAI, Grok and OpenAI-compatible servers).
 *
 * @package    mod_aiqbank
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ai_client {

    /** @var array Default base URL and model per provider. */
    public const DEFAULTS = [
        'gemini' => ['baseurl' => 'https://generativelanguage.googleapis.com/v1beta', 'model' => 'gemini-2.5-flash'],
        'openai' => ['baseurl' => 'https://api.openai.com/v1', 'model' => 'gpt-4o-mini'],
        'grok' => ['baseurl' => 'https://api.x.ai/v1', 'model' => 'grok-3-mini'],
        'custom' => ['baseurl' => '', 'model' => ''],
    ];

    /** @var string */
    protected $provider;
    /** @var string */
    protected $apikey;
    /** @var string */
    protected $model;
    /** @var string */
    protected $baseurl;
    /** @var float|null */
    protected $temperature;
    /** @var int */
    protected $timeout;
    /** @var bool */
    protected $jsonmode;
    /** @var int */
    protected $batchsize;

    /**
     * Constructor.
     *
     * @param string $provider gemini|openai|grok|custom
     * @param string $apikey API key.
     * @param string $model Model name.
     * @param string $baseurl Base URL.
     * @param float|null $temperature Sampling temperature (null = provider default).
     * @param int $timeout Request timeout in seconds.
     * @param bool $jsonmode Ask the provider for JSON output.
     * @param int $batchsize Questions requested per call.
     */
    public function __construct(string $provider, string $apikey, string $model, string $baseurl,
            ?float $temperature = 0.7, int $timeout = 120, bool $jsonmode = true, int $batchsize = 10) {
        $this->provider = isset(self::DEFAULTS[$provider]) ? $provider : 'gemini';
        $this->apikey = $apikey;
        $this->model = $model;
        $this->baseurl = $baseurl;
        $this->temperature = $temperature;
        $this->timeout = $timeout;
        $this->jsonmode = $jsonmode;
        $this->batchsize = $batchsize;
    }

    /**
     * Build a client from the plugin's admin settings.
     *
     * @return self
     */
    public static function from_config(): self {
        $cfg = get_config('mod_aiqbank');

        $provider = $cfg->provider ?? 'gemini';
        if (!isset(self::DEFAULTS[$provider])) {
            $provider = 'gemini';
        }
        $baseurl = trim($cfg->baseurl ?? '');
        if ($baseurl === '') {
            $baseurl = self::DEFAULTS[$provider]['baseurl'];
        }
        $model = trim($cfg->model ?? '');
        if ($model === '') {
            $model = self::DEFAULTS[$provider]['model'];
        }
        $temperature = (isset($cfg->temperature) && trim($cfg->temperature) !== '') ? (float) $cfg->temperature : null;
        $timeout = max(10, (int) ($cfg->timeout ?? 120));
        $jsonmode = !isset($cfg->jsonmode) || !empty($cfg->jsonmode);
        $batchsize = max(1, min(20, (int) ($cfg->batchsize ?? 10)));

        return new self($provider, trim($cfg->apikey ?? ''), $model, $baseurl, $temperature, $timeout, $jsonmode, $batchsize);
    }

    /**
     * Whether the settings are complete enough to call the provider.
     *
     * @return bool
     */
    public function is_configured(): bool {
        if ($this->model === '' || $this->baseurl === '') {
            return false;
        }
        // Self-hosted OpenAI-compatible servers (e.g. Ollama) may not need a key.
        return $this->apikey !== '' || $this->provider === 'custom';
    }

    /**
     * @return string Provider key.
     */
    public function get_provider(): string {
        return $this->provider;
    }

    /**
     * @return string Model name.
     */
    public function get_model(): string {
        return $this->model;
    }

    /**
     * @return int Questions requested per API call.
     */
    public function get_batchsize(): int {
        return $this->batchsize;
    }

    /**
     * Send a prompt and return the raw text of the answer.
     *
     * @param string $system System instructions.
     * @param string $user User prompt.
     * @return string Model output.
     * @throws \moodle_exception On configuration, connection or provider errors.
     */
    public function complete(string $system, string $user): string {
        if (!$this->is_configured()) {
            throw new \moodle_exception('notconfigured', 'mod_aiqbank');
        }

        if ($this->provider === 'gemini') {
            [$url, $headers, $body] = $this->build_gemini_request($system, $user);
        } else {
            [$url, $headers, $body] = $this->build_openai_request($system, $user);
        }

        $curl = new \curl();
        $curl->setHeader($headers);
        $response = $curl->post($url, json_encode($body, JSON_UNESCAPED_UNICODE), [
            'CURLOPT_TIMEOUT' => $this->timeout,
            'CURLOPT_CONNECTTIMEOUT' => 20,
        ]);

        if ($curl->get_errno()) {
            throw new \moodle_exception('error_ai', 'mod_aiqbank', '',
                get_string('error_connection', 'mod_aiqbank', s((string) $curl->error)));
        }

        $info = $curl->get_info();
        $http = (int) ($info['http_code'] ?? 0);
        $data = json_decode((string) $response, true);

        if ($http < 200 || $http >= 300) {
            $message = '';
            if (is_array($data)) {
                $err = $data['error'] ?? '';
                $message = is_array($err) ? (string) ($err['message'] ?? '') : (string) $err;
            }
            if ($message === '') {
                $message = trim(strip_tags((string) $response));
            }
            throw new \moodle_exception('error_ai', 'mod_aiqbank', '',
                'HTTP ' . $http . ' - ' . s(\core_text::substr($message, 0, 400)));
        }

        if (!is_array($data)) {
            throw new \moodle_exception('error_ai', 'mod_aiqbank', '', get_string('error_emptyresponse', 'mod_aiqbank'));
        }

        $text = $this->provider === 'gemini' ? $this->extract_gemini($data) : $this->extract_openai($data);
        if (trim($text) === '') {
            throw new \moodle_exception('error_ai', 'mod_aiqbank', '', get_string('error_emptyresponse', 'mod_aiqbank'));
        }
        return $text;
    }

    /**
     * Build a Gemini generateContent request.
     *
     * @param string $system
     * @param string $user
     * @return array [url, headers, body]
     */
    protected function build_gemini_request(string $system, string $user): array {
        $model = preg_replace('~^models/~', '', $this->model);
        $url = rtrim($this->baseurl, '/') . '/models/' . rawurlencode($model) . ':generateContent';
        $headers = [
            'Content-Type: application/json',
            'x-goog-api-key: ' . $this->apikey,
        ];
        $generation = [];
        if ($this->temperature !== null) {
            $generation['temperature'] = $this->temperature;
        }
        if ($this->jsonmode) {
            $generation['responseMimeType'] = 'application/json';
        }
        $body = [
            'systemInstruction' => ['parts' => [['text' => $system]]],
            'contents' => [['role' => 'user', 'parts' => [['text' => $user]]]],
        ];
        if ($generation) {
            $body['generationConfig'] = $generation;
        }
        return [$url, $headers, $body];
    }

    /**
     * Build an OpenAI-style chat completions request (OpenAI, Grok, custom).
     *
     * @param string $system
     * @param string $user
     * @return array [url, headers, body]
     */
    protected function build_openai_request(string $system, string $user): array {
        $base = rtrim($this->baseurl, '/');
        $url = preg_match('~/chat/completions$~', $base) ? $base : $base . '/chat/completions';
        $headers = ['Content-Type: application/json'];
        if ($this->apikey !== '') {
            $headers[] = 'Authorization: Bearer ' . $this->apikey;
        }
        $body = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
        ];
        // Reasoning models (gpt-5*, o1/o3...) only accept the default temperature.
        if ($this->temperature !== null && !preg_match('/^(gpt-5|o\d)/i', $this->model)) {
            $body['temperature'] = $this->temperature;
        }
        if ($this->jsonmode) {
            $body['response_format'] = ['type' => 'json_object'];
        }
        return [$url, $headers, $body];
    }

    /**
     * Extract the text from a Gemini response.
     *
     * @param array $data Decoded response.
     * @return string
     */
    protected function extract_gemini(array $data): string {
        $text = '';
        foreach ($data['candidates'][0]['content']['parts'] ?? [] as $part) {
            $text .= $part['text'] ?? '';
        }
        if ($text === '' && !empty($data['promptFeedback']['blockReason'])) {
            throw new \moodle_exception('error_ai', 'mod_aiqbank', '',
                get_string('error_blocked', 'mod_aiqbank', s((string) $data['promptFeedback']['blockReason'])));
        }
        return $text;
    }

    /**
     * Extract the text from an OpenAI-style response.
     *
     * @param array $data Decoded response.
     * @return string
     */
    protected function extract_openai(array $data): string {
        $content = $data['choices'][0]['message']['content'] ?? '';
        return is_string($content) ? $content : '';
    }
}
