<?php

declare(strict_types=1);

use App\Library\Chiffrement;
use PHPUnit\Framework\TestCase;

if (!defined('CRYPT_KEY')) {
    define('CRYPT_KEY', 'pmacontrol-test-key');
}

final class ChiffrementTest extends TestCase
{
    public function testStaticEncryptAndDecryptRoundTrip(): void
    {
        $encrypted = Chiffrement::encrypt('secret-value', 'custom-password');

        $this->assertNotSame('secret-value', $encrypted);
        $this->assertSame('secret-value', Chiffrement::decrypt($encrypted, 'custom-password'));
    }

    public function testInstanceConstructorCurrentlyFailsWithPhpseclibArgumentMismatch(): void
    {
        $this->expectException(ArgumentCountError::class);

        new Chiffrement('instance-password');
    }

    public function testDecryptThrowsOnEmptyPassword(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Empty password');

        Chiffrement::decrypt('anything', '');
    }
}
