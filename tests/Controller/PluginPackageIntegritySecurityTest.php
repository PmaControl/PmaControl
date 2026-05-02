<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PluginPackageIntegritySecurityTest extends TestCase
{
    private string $controller;

    protected function setUp(): void
    {
        $this->controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Plugin.php');
    }

    public function testPluginControllerUsesIntegrityHelperBeforeZipExtraction(): void
    {
        self::assertStringContainsString('use App\\Library\\Security\\PluginPackageIntegrity;', $this->controller);

        $installBody = $this->methodBody('public function install($param)');
        $integrityPosition = strpos($installBody, 'PluginPackageIntegrity::ensureZipTrusted');
        $zipOpenPosition = strpos($installBody, '$zip->open($zipPath)');
        $extractPosition = strpos($installBody, '$zip->extractTo(');

        self::assertNotFalse($integrityPosition);
        self::assertNotFalse($zipOpenPosition);
        self::assertNotFalse($extractPosition);
        self::assertLessThan($zipOpenPosition, $integrityPosition);
        self::assertLessThan($extractPosition, $integrityPosition);
    }

    public function testPluginControllerPersistsSha256AndSignatureCatalogMetadata(): void
    {
        self::assertStringContainsString("'SHA256'", $this->controller);
        self::assertStringContainsString("'Signature'", $this->controller);
        self::assertStringContainsString('sha256_zip', $this->controller);
        self::assertStringContainsString('signature_zip', $this->controller);
        self::assertStringContainsString("sha256_zip <> '' THEN sha256_zip", $this->controller);
        self::assertStringContainsString("COALESCE(signature_zip, '') <> '' THEN signature_zip", $this->controller);
    }

    public function testPluginMainSchemaIncludesSha256AndSignatureColumns(): void
    {
        $schema = (string) file_get_contents(__DIR__ . '/../../sql/plugin.sql');
        $fullSchema = (string) file_get_contents(__DIR__ . '/../../sql/full/pmacontrol.sql');
        $migration = (string) file_get_contents(__DIR__ . '/../../sql/incremental_v2/20260502_plugin_package_integrity.sql');

        foreach (array($schema, $fullSchema, $migration) as $sql) {
            self::assertStringContainsString('`sha256_zip` char(64)', $sql);
            self::assertStringContainsString('`signature_zip` text', $sql);
        }
    }

    public function testComposerRequiresSodiumForEd25519Verification(): void
    {
        $composer = json_decode((string) file_get_contents(__DIR__ . '/../../composer.json'), true);

        self::assertArrayHasKey('ext-sodium', $composer['require']);
    }

    private function methodBody(string $signature): string
    {
        $start = strpos($this->controller, $signature);
        self::assertNotFalse($start, 'Method not found: ' . $signature);

        $openBrace = strpos($this->controller, '{', (int) $start);
        self::assertNotFalse($openBrace, 'Method opening brace not found: ' . $signature);

        $depth = 0;
        $length = strlen($this->controller);
        for ($index = (int) $openBrace; $index < $length; $index++) {
            if ($this->controller[$index] === '{') {
                $depth++;
                continue;
            }

            if ($this->controller[$index] !== '}') {
                continue;
            }

            $depth--;
            if ($depth === 0) {
                return substr($this->controller, (int) $start, $index - (int) $start + 1);
            }
        }

        self::fail('Method closing brace not found: ' . $signature);
    }
}
