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
