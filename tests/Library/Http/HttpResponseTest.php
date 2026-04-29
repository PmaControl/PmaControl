<?php

declare(strict_types=1);

use App\Library\Http\HttpResponse;
use PHPUnit\Framework\TestCase;

final class HttpResponseTest extends TestCase
{
    public function testErrorAddsPlainTextContentTypeByDefault(): void
    {
        $response = HttpResponse::error(403, 'Invalid CSRF token', ['Allow' => 'POST']);

        $this->assertSame(403, $response['status']);
        $this->assertSame('Invalid CSRF token', $response['body']);
        $this->assertSame(
            ['Allow' => 'POST', 'Content-Type' => 'text/plain; charset=UTF-8'],
            $response['headers']
        );
    }

    public function testErrorKeepsExistingContentTypeCaseInsensitively(): void
    {
        $response = HttpResponse::error(
            422,
            'payload rejected',
            ['content-type' => 'application/json; charset=UTF-8'],
            'text/plain; charset=UTF-8'
        );

        $this->assertSame(['content-type' => 'application/json; charset=UTF-8'], $response['headers']);
    }

    public function testErrorCanDisableDefaultContentType(): void
    {
        $response = HttpResponse::error(503, 'worker unavailable', [], null);

        $this->assertSame(['status' => 503, 'body' => 'worker unavailable', 'headers' => []], $response);
    }

    public function testFromOutcomeNormalizesStatusBodyAndHeaders(): void
    {
        $response = HttpResponse::fromOutcome([
            'status' => '405',
            'body' => 12,
            'headers' => ['Allow' => 'POST'],
        ]);

        $this->assertSame(405, $response['status']);
        $this->assertSame('12', $response['body']);
        $this->assertSame(
            ['Allow' => 'POST', 'Content-Type' => 'text/plain; charset=UTF-8'],
            $response['headers']
        );
    }

    public function testFromOutcomeDropsMalformedHeadersAndBody(): void
    {
        $response = HttpResponse::fromOutcome([
            'status' => 400,
            'body' => ['unexpected'],
            'headers' => ['X-Good' => 7, 'X-Bad' => ['nope'], 0 => 'ignored'],
        ]);

        $this->assertSame(400, $response['status']);
        $this->assertSame('', $response['body']);
        $this->assertSame(
            ['X-Good' => '7', 'Content-Type' => 'text/plain; charset=UTF-8'],
            $response['headers']
        );
    }
}
