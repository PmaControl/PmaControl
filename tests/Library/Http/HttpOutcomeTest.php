<?php

declare(strict_types=1);

use App\Library\Http\HttpOutcome;
use PHPUnit\Framework\TestCase;

final class HttpOutcomeTest extends TestCase
{
    public function testBuildKeepsHttpContractAndAddsExtraPayload(): void
    {
        $outcome = HttpOutcome::build(422, 'Invalid payload', ['Allow' => 'POST'], ['tag' => null]);

        $this->assertSame(422, $outcome['status']);
        $this->assertSame('Invalid payload', $outcome['body']);
        $this->assertSame(['Allow' => 'POST'], $outcome['headers']);
        $this->assertNull($outcome['tag']);
    }

    public function testExtraPayloadCannotOverrideHttpKeys(): void
    {
        $outcome = HttpOutcome::build(403, 'Denied', [], [
            'status' => 200,
            'body' => 'OK',
            'headers' => ['X-Bad' => '1'],
            'payload' => ['id' => 7],
        ]);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Denied', $outcome['body']);
        $this->assertSame([], $outcome['headers']);
        $this->assertSame(['id' => 7], $outcome['payload']);
    }

    public function testOkBuildsSuccessOutcome(): void
    {
        $outcome = HttpOutcome::ok(['sql' => 'SELECT 1', 'hash' => 'abc']);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('', $outcome['body']);
        $this->assertSame([], $outcome['headers']);
        $this->assertSame('SELECT 1', $outcome['sql']);
        $this->assertSame('abc', $outcome['hash']);
    }

    public function testErrorBuildsErrorOutcome(): void
    {
        $outcome = HttpOutcome::error(413, 'Too large', [], ['sql' => '', 'hash' => '']);

        $this->assertSame(413, $outcome['status']);
        $this->assertSame('Too large', $outcome['body']);
        $this->assertSame('', $outcome['sql']);
        $this->assertSame('', $outcome['hash']);
    }

    public function testFromGuardBuildsFailureOutcome(): void
    {
        $outcome = HttpOutcome::fromGuard(
            ['allowed' => false, 'status' => 405, 'body' => 'Method Not Allowed', 'headers' => ['Allow' => 'POST']],
            ['cleaner_main' => null]
        );

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('Method Not Allowed', $outcome['body']);
        $this->assertSame(['Allow' => 'POST'], $outcome['headers']);
        $this->assertNull($outcome['cleaner_main']);
    }

    public function testFromGuardBuildsSuccessOutcome(): void
    {
        $outcome = HttpOutcome::fromGuard(
            ['allowed' => true, 'status' => 200, 'body' => '', 'headers' => []],
            ['tag' => ['name' => 'prod']]
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(['name' => 'prod'], $outcome['tag']);
    }
}
