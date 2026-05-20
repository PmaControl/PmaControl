<?php

declare(strict_types=1);

namespace App\Controller;

use App\Library\Debug;
use App\Library\Llm\SqlAssistant;
use App\Library\Security\CsrfGuard;
use Glial\Security\Csrf;
use Glial\Synapse\Controller;

final class Llm extends Controller
{
    public const LLM_ANALYZE_CSRF_SCOPE = 'llm.analyze';

    private const SESSION_RESULT_KEY = 'llm.sql_assistant.result';

    public function index($param): void
    {
        Debug::parseDebug($param);

        if (CsrfGuard::isPost($_SERVER)) {
            $_SESSION[self::SESSION_RESULT_KEY] = $this->handleIndexPost($_POST, $_SERVER, $_SESSION);
            $this->redirectTo(LINK . 'llm/index');
            return;
        }

        $data = $this->buildViewData();
        $result = $_SESSION[self::SESSION_RESULT_KEY] ?? null;
        unset($_SESSION[self::SESSION_RESULT_KEY]);

        if (is_array($result)) {
            $data = array_replace($data, $result);
        }

        $this->set('data', $data);
    }

    public function analyze($param): void
    {
        Debug::parseDebug($param);
        $this->view = false;

        $config = SqlAssistant::normalizeConfig($this->loadLlmConfig());
        $input = SqlAssistant::normalizeInput($param[0] ?? null, (int) $config['max_input_bytes']);
        if ($input === null) {
            echo "Usage:\n";
            echo "php App/Webroot/index.php llm analyze \"<SHOW CREATE TABLE + EXPLAIN>\"\n";
            return;
        }

        $response = $this->callLLM($input, $config);
        if ($response === null) {
            echo "LLM assistant unavailable or disabled.\n";
            return;
        }

        $parsed = SqlAssistant::parseResponse($response);
        $this->displayCliResult($parsed);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildViewData(): array
    {
        $config = SqlAssistant::normalizeConfig($this->loadLlmConfig());

        return [
            'config' => $config,
            'input' => '',
            'error' => null,
            'result' => null,
            'llm_analyze_csrf_field' => Csrf::DEFAULT_FIELD,
            'llm_analyze_csrf_token' => Csrf::issueToken($_SESSION, self::LLM_ANALYZE_CSRF_SCOPE),
        ];
    }

    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed> $server
     * @param array<string, mixed> $session
     * @return array<string, mixed>
     */
    private function handleIndexPost(array $post, array $server, array $session): array
    {
        $config = SqlAssistant::normalizeConfig($this->loadLlmConfig());
        $base = [
            'config' => $config,
            'input' => '',
            'error' => null,
            'result' => null,
        ];

        if ($failure = CsrfGuard::ensureOrFail($post, $server, $session, self::LLM_ANALYZE_CSRF_SCOPE)) {
            return array_replace($base, ['error' => $failure['body']]);
        }

        $validation = SqlAssistant::validateRuntimeConfig($config);
        if (!$validation['allowed']) {
            return array_replace($base, ['error' => $validation['error']]);
        }

        $llm = isset($post['llm']) && is_array($post['llm']) ? $post['llm'] : [];
        $input = SqlAssistant::normalizeInput($llm['input'] ?? null, (int) $config['max_input_bytes']);
        $base['input'] = $input ?? '';

        if ($input === null) {
            return array_replace($base, ['error' => 'Invalid or empty SQL context.']);
        }

        $response = $this->callLLM($input, $config);
        if ($response === null) {
            return array_replace($base, ['error' => 'LLM assistant unavailable or disabled.']);
        }

        $parsed = SqlAssistant::parseResponse($response);
        return array_replace($base, ['result' => $this->buildAnalysisResult($parsed)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function loadLlmConfig(): array
    {
        $configFile = CONFIG . 'llm.config.php';
        if (!is_file($configFile)) {
            return [];
        }

        $config = include $configFile;
        return is_array($config) ? $config : [];
    }

    /**
     * @param array<string, mixed>|null $config
     */
    private function callLLM(string $input, ?array $config = null): ?string
    {
        $config = SqlAssistant::normalizeConfig($config ?? $this->loadLlmConfig());
        $validation = SqlAssistant::validateRuntimeConfig($config);
        if (!$validation['allowed']) {
            Debug::debug($validation['error']);
            return null;
        }

        $payload = json_encode(SqlAssistant::buildPayload($input, $config), JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            Debug::debug('Unable to encode LLM payload');
            return null;
        }

        $ch = curl_init((string) $config['endpoint']);
        if ($ch === false) {
            Debug::debug('Unable to initialize LLM cURL handle');
            return null;
        }

        $curlOptions = [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_CONNECTTIMEOUT => min(5, (int) $config['timeout_seconds']),
            CURLOPT_TIMEOUT => (int) $config['timeout_seconds'],
        ];

        if (defined('CURLOPT_PROTOCOLS_STR')) {
            $curlOptions[CURLOPT_PROTOCOLS_STR] = 'http,https';
        } elseif (defined('CURLOPT_PROTOCOLS')) {
            $curlOptions[CURLOPT_PROTOCOLS] = CURLPROTO_HTTP | CURLPROTO_HTTPS;
        }

        curl_setopt_array($ch, $curlOptions);

        $response = curl_exec($ch);
        if ($response === false) {
            Debug::debug(curl_error($ch));
            return null;
        }

        $statusCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        if ($statusCode >= 400) {
            Debug::debug('LLM endpoint returned HTTP ' . $statusCode);
            return null;
        }

        return SqlAssistant::extractModelResponse((string) $response);
    }

    /**
     * @param array<string, mixed> $parsed
     * @return array<string, mixed>
     */
    private function buildAnalysisResult(array $parsed): array
    {
        $indexes = $parsed['indexes'] ?? [];

        return [
            'status' => $parsed['status'] ?? 'ERROR',
            'error' => $parsed['error'] ?? null,
            'missing_input' => $parsed['missing_input'] ?? [],
            'indexes' => $indexes,
            'alter_statements' => SqlAssistant::buildAlterStatements($indexes),
        ];
    }

    /**
     * @param array<string, mixed> $parsed
     */
    private function displayCliResult(array $parsed): void
    {
        $result = $this->buildAnalysisResult($parsed);
        if ($result['status'] !== 'OK') {
            echo 'Status: ' . $result['status'] . "\n";
            if (!empty($result['error'])) {
                echo 'Error: ' . $result['error'] . "\n";
            }
            foreach ($result['missing_input'] as $missingInput) {
                echo '- missing: ' . $missingInput . "\n";
            }
            return;
        }

        if ($result['indexes'] === []) {
            echo "No missing index detected.\n";
            return;
        }

        echo "Index suggestions:\n";
        foreach ($result['indexes'] as $index) {
            echo '- ' . $index['table'] . ' (' . implode(', ', $index['columns']) . ")\n";
        }

        echo "\nALTER TABLE statements:\n";
        foreach ($result['alter_statements'] as $statement) {
            echo $statement . "\n";
        }
    }

    private function redirectTo(string $url): void
    {
        $this->view = false;
        $this->layout_name = false;
        header('Location: ' . $url, true, 303);
    }
}
