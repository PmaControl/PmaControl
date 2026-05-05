<?php

declare(strict_types=1);

namespace Tests\Library\Llm;

use App\Library\Llm\SqlAssistant;
use PHPUnit\Framework\TestCase;

final class SqlAssistantTest extends TestCase
{
    public function testDefaultConfigIsDisabledAndLocalOnly(): void
    {
        $config = SqlAssistant::normalizeConfig([]);

        $this->assertFalse($config['enabled']);
        $this->assertSame('http://127.0.0.1:11434/api/generate', $config['endpoint']);
        $this->assertSame('llama3', $config['model']);
        $this->assertFalse($config['allow_remote_endpoint']);
        $this->assertSame(15, $config['timeout_seconds']);
        $this->assertSame(20000, $config['max_input_bytes']);
    }

    public function testRuntimeConfigFailsClosedWhenDisabled(): void
    {
        $validation = SqlAssistant::validateRuntimeConfig([
            'enabled' => false,
            'endpoint' => 'http://127.0.0.1:11434/api/generate',
        ]);

        $this->assertFalse($validation['allowed']);
        $this->assertSame('LLM assistant disabled', $validation['error']);
    }

    public function testInvalidNumericConfigFallsBackToDefaults(): void
    {
        $config = SqlAssistant::normalizeConfig([
            'timeout_seconds' => 'not numeric',
            'max_input_bytes' => null,
        ]);

        $this->assertSame(15, $config['timeout_seconds']);
        $this->assertSame(20000, $config['max_input_bytes']);
    }

    public function testRemoteEndpointRequiresExplicitOptIn(): void
    {
        $blocked = SqlAssistant::validateRuntimeConfig([
            'enabled' => true,
            'endpoint' => 'https://llm.example.org/api/generate',
        ]);
        $allowed = SqlAssistant::validateRuntimeConfig([
            'enabled' => true,
            'endpoint' => 'https://llm.example.org/api/generate',
            'allow_remote_endpoint' => true,
        ]);

        $this->assertFalse($blocked['allowed']);
        $this->assertSame('Remote LLM endpoint is disabled', $blocked['error']);
        $this->assertTrue($allowed['allowed']);
    }

    public function testPayloadUsesCentralPromptAndConfiguredModel(): void
    {
        $payload = SqlAssistant::buildPayload('EXPLAIN SELECT * FROM orders', [
            'enabled' => true,
            'model' => 'mariadb-indexer',
        ]);

        $this->assertSame('mariadb-indexer', $payload['model']);
        $this->assertSame('EXPLAIN SELECT * FROM orders', $payload['prompt']);
        $this->assertFalse($payload['stream']);
        $this->assertStringContainsString('full table scans', $payload['system']);
    }

    public function testNormalizeInputRejectsEmptyNonScalarAndOversizedValues(): void
    {
        $this->assertSame('SELECT 1', SqlAssistant::normalizeInput(' SELECT 1 ', 20));
        $this->assertNull(SqlAssistant::normalizeInput('', 20));
        $this->assertNull(SqlAssistant::normalizeInput(['SELECT 1'], 20));
        $this->assertNull(SqlAssistant::normalizeInput(str_repeat('a', 21), 20));
    }

    public function testExtractModelResponseReadsOllamaResponseField(): void
    {
        $response = SqlAssistant::extractModelResponse('{"response":"{\"status\":\"OK\",\"indexes\":[]}"}');

        $this->assertSame('{"status":"OK","indexes":[]}', $response);
    }

    public function testParseResponseAcceptsFencedJsonAndFiltersUnsafeIdentifiers(): void
    {
        $parsed = SqlAssistant::parseResponse(
            "```json\n"
            . '{"status":"OK","indexes":['
            . '{"table":"orders","columns":["customer_id","status"]},'
            . '{"table":"bad table","columns":["customer_id"]},'
            . '{"table":"orders","columns":["unsafe`column"]}'
            . "]}\n```"
        );

        $this->assertSame('OK', $parsed['status']);
        $this->assertSame([
            ['table' => 'orders', 'columns' => ['customer_id', 'status']],
        ], $parsed['indexes']);
    }

    public function testParseErrorResponseNormalizesMissingInput(): void
    {
        $parsed = SqlAssistant::parseResponse('{"status":"ERROR","missing_input":["SHOW CREATE TABLE","EXPLAIN"]}');

        $this->assertSame('ERROR', $parsed['status']);
        $this->assertSame(['SHOW CREATE TABLE', 'EXPLAIN'], $parsed['missing_input']);
    }

    public function testBuildAlterStatementsQuotesIdentifiersAndNamesIndex(): void
    {
        $statements = SqlAssistant::buildAlterStatements([
            ['table' => 'orders', 'columns' => ['customer_id', 'status']],
        ]);

        $this->assertSame([
            'ALTER TABLE `orders` ADD INDEX `idx_orders_customer_id_status` (`customer_id`, `status`);',
        ], $statements);
    }

    public function testInvalidIndexSuggestionsDoNotGenerateSql(): void
    {
        $statements = SqlAssistant::buildAlterStatements([
            ['table' => 'orders; DROP TABLE user', 'columns' => ['id']],
            ['table' => 'orders', 'columns' => ['bad column']],
        ]);

        $this->assertSame([], $statements);
    }
}
