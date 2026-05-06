<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class InstallGenerateKeyTest extends TestCase
{
    public function testGeneratedCryptKeyIsPhpSafe(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Install.php');

        $this->assertStringContainsString('$key = bin2hex(random_bytes(32));', $controller);
        $this->assertStringContainsString("define('CRYPT_KEY', \".var_export(\$key, true).\")", $controller);
        $this->assertStringNotContainsString('str_replace("\'", "", $this->rand_char(256))', $controller);
    }
}
