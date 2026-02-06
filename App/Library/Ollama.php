<?php 


final class OllamaClient
{
    public function __construct(
        private string $endpoint,
        private string $model,
        private int $timeout = 10
    ) {}

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