<?php

declare(strict_types=1);

use App\Library\Security\SecretComparison;
use PHPUnit\Framework\TestCase;

final class SecretComparisonTest extends TestCase
{
    public function testEqualsAcceptsIdenticalSecret(): void
    {
        $this->assertTrue(SecretComparison::equals('correct horse battery staple', 'correct horse battery staple'));
    }

    public function testEqualsRejectsPrefixOnlySecret(): void
    {
        $this->assertFalse(SecretComparison::equals('correct horse battery staple', 'correct horse'));
    }

    public function testEqualsHandlesBinarySecret(): void
    {
        $this->assertTrue(SecretComparison::equals("abc\0def", "abc\0def"));
        $this->assertFalse(SecretComparison::equals("abc\0def", "abc\0deg"));
    }
}
