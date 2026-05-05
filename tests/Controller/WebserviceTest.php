<?php

declare(strict_types=1);

if (!defined('IS_CLI')) {
    define('IS_CLI', true);
}

use App\Controller\Webservice;
use PHPUnit\Framework\TestCase;

final class WebserviceTest extends TestCase
{
    public function testImportAliasDelegatesToPlainImporter(): void
    {
        $controller = new TestableWebservice('Controller', 'View', []);

        $controller->import(['/root/monfichier.json']);

        $this->assertSame(['/root/monfichier.json'], $controller->importCalledWith);
    }

    public function testNormalizeMysqlServerImportPayloadAcceptsMysqlWrapper(): void
    {
        $controller = new TestableWebservice('Controller', 'View', []);
        $payload = ['mysql' => [['hostname' => '10.0.0.1']]];

        $normalized = $controller->exposeNormalizeMysqlServerImportPayload($payload);

        $this->assertSame([['hostname' => '10.0.0.1']], $normalized);
    }

    public function testNormalizeMysqlServerImportPayloadAcceptsFlatArray(): void
    {
        $controller = new TestableWebservice('Controller', 'View', []);
        $payload = [['hostname' => '10.0.0.2']];

        $normalized = $controller->exposeNormalizeMysqlServerImportPayload($payload);

        $this->assertSame($payload, $normalized);
    }

    public function testAssertCliRootOnlyAllowsRootUser(): void
    {
        $controller = new TestableWebservice('Controller', 'View', []);
        $controller->effectiveUserId = 0;

        $controller->exposeAssertCliRootOnly();

        $this->assertTrue(true);
    }

    public function testAssertCliRootOnlyRejectsNonRootUser(): void
    {
        $controller = new TestableWebservice('Controller', 'View', []);
        $controller->effectiveUserId = 1000;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('PMACTRL-WS-CLI-002');

        $controller->exposeAssertCliRootOnly();
    }

    public function testPushServerUsesApiGuardForMachineToMachineCsrfExemption(): void
    {
        $source = file_get_contents(__DIR__ . '/../../App/Controller/Webservice.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('use App\\Library\\Security\\ApiRequestGuard;', $source);
        $this->assertStringContainsString('ApiRequestGuard::checkJsonPostBasicAuth($jsonData, $_SERVER)', $source);
        $this->assertStringNotContainsString('CsrfGuard::', $source);
    }

    public function testCheckCredentialsUsesMutualizedConstantTimeComparison(): void
    {
        $source = file_get_contents(__DIR__ . '/../../App/Controller/Webservice.php');

        $this->assertIsString($source);
        $method = self::extractCheckCredentialsSource($source);

        $this->assertStringContainsString('use App\\Library\\Security\\SecretComparison;', $source);
        $this->assertStringContainsString(
            'SecretComparison::equals((string) $pw_from_db, (string) $password)',
            $method
        );
        $this->assertStringNotContainsString('$pw_from_db === $password', $method);
    }

    public function testCheckCredentialsDoesNotLeakPasswordOrReturnInsideLoop(): void
    {
        $source = file_get_contents(__DIR__ . '/../../App/Controller/Webservice.php');

        $this->assertIsString($source);
        $method = self::extractCheckCredentialsSource($source);

        $this->assertStringNotContainsString('Debug::debug($ob', $method);
        $this->assertStringNotContainsString('Debug::debug($pw_from_db', $method);
        $this->assertStringNotContainsString('Debug::debug($password', $method);
        $this->assertStringNotContainsString('return true;', $method);
        $this->assertStringContainsString('return $isAuthenticated;', $method);

        $loopPosition = strpos($method, 'while ($ob = $db->sql_fetch_object($res))');
        $returnPosition = strrpos($method, 'return $isAuthenticated;');

        $this->assertIsInt($loopPosition);
        $this->assertIsInt($returnPosition);
        $this->assertLessThan($returnPosition, $loopPosition);
    }

    public function testPushServerDoesNotDumpRawJsonPayload(): void
    {
        $source = file_get_contents(__DIR__ . '/../../App/Controller/Webservice.php');

        $this->assertIsString($source);
        $method = self::extractMethodSource($source, 'public function pushServer');

        $this->assertStringNotContainsString('Debug::debug($jsonData', $method);
    }

    public function testParseServerDoesNotDumpServerPayload(): void
    {
        $source = file_get_contents(__DIR__ . '/../../App/Controller/Webservice.php');

        $this->assertIsString($source);
        $method = self::extractMethodSource($source, 'private function parseServer');

        $this->assertStringNotContainsString('Debug::debug($data', $method);
        $this->assertStringNotContainsString('Debug::debug($server', $method);
    }

    public function testSaveHistoryRedactsBasicAuthPasswordAndPayload(): void
    {
        $source = file_get_contents(__DIR__ . '/../../App/Controller/Webservice.php');

        $this->assertIsString($source);
        $method = self::extractMethodSource($source, 'private function saveHistory');

        $this->assertStringContainsString('use App\\Library\\Security\\SecretRedactor;', $source);
        $this->assertStringContainsString('SecretRedactor::redactedValue()', $method);
        $this->assertStringContainsString('SecretRedactor::jsonPayload((string)$json)', $method);
        $this->assertStringNotContainsString("['PHP_AUTH_PW']", $method);
        $this->assertStringNotContainsString('Debug::debug($data', $method);
    }

    public function testWebserviceHistoryMigrationPurgesLegacyPasswordValues(): void
    {
        $migration = file_get_contents(__DIR__ . '/../../sql/incremental_v2/20260430_webservice_history_redact.sql');
        $schema = file_get_contents(__DIR__ . '/../../sql/full/pmacontrol.sql');

        $this->assertIsString($migration);
        $this->assertStringContainsString("UPDATE `webservice_history_main`", $migration);
        $this->assertStringContainsString("SET `password` = '[redacted]'", $migration);
        $this->assertStringContainsString("WHERE `password` <> '[redacted]'", $migration);
        $this->assertStringContainsString("SET `message` = '[redacted legacy payload]'", $migration);
        $this->assertStringContainsString("WHERE `message` REGEXP", $migration);

        $this->assertIsString($schema);
        $this->assertStringContainsString(
            "COMMENT 'redacted legacy field; Basic Auth password is never stored'",
            $schema
        );
    }

    public function testLegacyJsonCheckDelegatesToApiGuard(): void
    {
        $controller = new TestableWebservice('Controller', 'View', []);

        $this->assertTrue($controller->isJson('{"mysql":[]}'));
        $this->assertFalse($controller->isJson('{bad json'));
    }

    public function testPushServerWritesTemporaryImportFileAfterCredentialCheck(): void
    {
        $source = file_get_contents(__DIR__ . '/../../App/Controller/Webservice.php');

        $this->assertIsString($source);
        $credentialCheck = strpos($source, '$id_user_main = $this->checkCredentials');
        $temporaryWrite = strpos($source, 'file_put_contents($finale_name');

        $this->assertIsInt($credentialCheck);
        $this->assertIsInt($temporaryWrite);
        $this->assertLessThan($temporaryWrite, $credentialCheck);
    }

    private static function extractCheckCredentialsSource(string $source): string
    {
        return self::extractMethodSource($source, 'private function checkCredentials');
    }

    private static function extractMethodSource(string $source, string $signature): string
    {
        $start = strpos($source, $signature);
        self::assertIsInt($start);

        $openBrace = strpos($source, '{', $start);
        self::assertIsInt($openBrace);

        $depth = 0;
        $length = strlen($source);
        for ($i = $openBrace; $i < $length; $i++) {
            if ($source[$i] === '{') {
                $depth++;
            } elseif ($source[$i] === '}') {
                $depth--;
                if ($depth === 0) {
                    return substr($source, $start, $i - $start + 1);
                }
            }
        }

        self::fail('Unable to extract method source for ' . $signature);
    }
}

final class TestableWebservice extends Webservice
{
    /** @var array<int,mixed>|null */
    public ?array $importCalledWith = null;

    public int $effectiveUserId = 0;
    public function importMysqlServerPlain($param)
    {
        $this->importCalledWith = $param;
    }

    public function exposeNormalizeMysqlServerImportPayload(array $payload): array
    {
        return $this->normalizeMysqlServerImportPayload($payload);
    }

    public function exposeAssertCliRootOnly(): void
    {
        $this->assertCliRootOnly();
    }

    protected function getEffectiveUserId(): int
    {
        return $this->effectiveUserId;
    }
}
