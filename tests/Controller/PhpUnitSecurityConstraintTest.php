<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Issue #489 - PHPUnit <= 11.5.49 is affected by CVE-2026-24765.
 * The project intentionally does not version composer.lock, so composer.json
 * must carry the patched minimum directly.
 */
final class PhpUnitSecurityConstraintTest extends TestCase
{
    public function testPhpUnitRequiresPatchedCve202624765Version(): void
    {
        $composer = json_decode(
            (string) file_get_contents(__DIR__ . '/../../composer.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $constraint = $composer['require-dev']['phpunit/phpunit'] ?? '';

        $this->assertNotSame('', $constraint, 'phpunit/phpunit must be declared in require-dev');
        $this->assertMatchesRegularExpression(
            '/(?:^|[\\s,|])(?:[~^><= ]*)\\d+\\.\\d+(?:\\.\\d+)?/',
            $constraint,
            'phpunit/phpunit constraint must contain an explicit numeric version'
        );

        preg_match('/\\d+\\.\\d+(?:\\.\\d+)?/', $constraint, $matches);
        $version = $matches[0] ?? '0.0.0';
        if (substr_count($version, '.') === 1) {
            $version .= '.0';
        }

        $this->assertGreaterThanOrEqual(
            0,
            version_compare($version, '11.5.50'),
            'phpunit/phpunit must be constrained to 11.5.50+ to avoid CVE-2026-24765'
        );
    }
}
