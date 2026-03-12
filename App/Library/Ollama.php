<?php 


/**
 * Class responsible for ollama client workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
final class OllamaClient
{
/**
 * Handle ollama client state through `__construct`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param string $endpoint Input value for `endpoint`.
 * @phpstan-param string $endpoint
 * @psalm-param string $endpoint
 * @param string $model Input value for `model`.
 * @phpstan-param string $model
 * @psalm-param string $model
 * @param int $timeout Input value for `timeout`.
 * @phpstan-param int $timeout
 * @psalm-param int $timeout
 * @return void Returned value for __construct.
 * @phpstan-return void
 * @psalm-return void
 * @see self::__construct()
 * @example /fr/ollamaclient/__construct
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function __construct(
        private string $endpoint,
        private string $model,
        private int $timeout = 10
    ) {}

/**
 * Handle ollama client state through `generate`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param string $prompt Input value for `prompt`.
 * @phpstan-param string $prompt
 * @psalm-param string $prompt
 * @param string $system Input value for `system`.
 * @phpstan-param string $system
 * @psalm-param string $system
 * @return string Returned value for generate.
 * @phpstan-return string
 * @psalm-return string
 * @throws \Throwable When the underlying operation fails.
 * @see self::generate()
 * @example /fr/ollamaclient/generate
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function generate(string $prompt, string $system): string
    {
        $payload = json_encode([
            'model'  => $this->model,
            'prompt' => $prompt,
            'system' => $system,
            'stream' => false,
        ], JSON_THROW_ON_ERROR);

        $ch = curl_init($this->endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => $this->timeout,
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            throw new RuntimeException('Ollama error: ' . curl_error($ch));
        }
        curl_close($ch);

        $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        if (!isset($json['response'])) {
            throw new RuntimeException('Invalid Ollama response');
        }

        return trim($json['response']);
    }
}
