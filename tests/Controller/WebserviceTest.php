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
        $start = strpos($source, 'private function checkCredentials');

        self::assertIsInt($start);

        $end = strpos($source, "\n/**", $start);

        self::assertIsInt($end);

        return substr($source, $start, $end - $start);
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
