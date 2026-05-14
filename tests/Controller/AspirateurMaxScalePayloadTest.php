<?php

declare(strict_types=1);

use App\Controller\Aspirateur;
use PHPUnit\Framework\TestCase;

/**
 * Issue #1226 — when MaxScale REST replies but `data` is empty, the
 * Aspirateur must preserve the last good time-series value instead of
 * overwriting it with an empty JSON. Mirrors the MySQL-offline contract.
 */
final class AspirateurMaxScalePayloadTest extends TestCase
{
    public function testNonArrayPayloadCountsAsEmpty(): void
    {
        $this->assertTrue(Aspirateur::isMaxScalePayloadEmpty(null));
        $this->assertTrue(Aspirateur::isMaxScalePayloadEmpty(''));
        $this->assertTrue(Aspirateur::isMaxScalePayloadEmpty(0));
    }

    public function testEmptyDataArrayCountsAsEmpty(): void
    {
        $this->assertTrue(Aspirateur::isMaxScalePayloadEmpty(['data' => []]));
    }

    public function testPopulatedDataArrayDoesNotCountAsEmpty(): void
    {
        $payload = [
            'data' => [
                ['id' => 'rwsplit', 'type' => 'services'],
            ],
        ];
        $this->assertFalse(Aspirateur::isMaxScalePayloadEmpty($payload));
    }

    public function testMissingDataKeyDoesNotCountAsEmpty(): void
    {
        // /v1/maxscale top-level info returns a non-JSON:API shape with no
        // `data` key — that's still useful and must be preserved.
        $this->assertFalse(Aspirateur::isMaxScalePayloadEmpty([
            'meta' => ['version' => '22.08.0'],
        ]));
    }
}
