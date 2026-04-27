<?php

declare(strict_types=1);

use App\Library\Security\ApiRequestGuard;
use PHPUnit\Framework\TestCase;

final class ApiRequestGuardTest extends TestCase
{
    public function testJsonPostWithBasicAuthIsAllowed(): void
    {
        $outcome = ApiRequestGuard::checkJsonPostBasicAuth(
            '{"mysql":[]}',
            [
                'REQUEST_METHOD' => 'POST',
                'PHP_AUTH_USER' => 'webservice',
                'PHP_AUTH_PW' => 'secret',
            ]
        );

        $this->assertTrue($outcome['allowed']);
        $this->assertSame(200, $outcome['status']);
    }

    public function testNonPostIsRejectedBeforeApiProcessing(): void
    {
        $outcome = ApiRequestGuard::checkJsonPostBasicAuth(
            '{"mysql":[]}',
            [
                'REQUEST_METHOD' => 'GET',
                'PHP_AUTH_USER' => 'webservice',
                'PHP_AUTH_PW' => 'secret',
            ]
        );

        $this->assertFalse($outcome['allowed']);
        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
    }

    public function testMalformedJsonIsRejected(): void
    {
        $outcome = ApiRequestGuard::checkJsonPostBasicAuth(
            '{bad json',
            [
                'REQUEST_METHOD' => 'POST',
                'PHP_AUTH_USER' => 'webservice',
                'PHP_AUTH_PW' => 'secret',
            ]
        );

        $this->assertFalse($outcome['allowed']);
        $this->assertSame(400, $outcome['status']);
        $this->assertSame('JSON malformed', $outcome['body']['error']);
    }

    public function testMissingBasicAuthIsRejected(): void
    {
        $outcome = ApiRequestGuard::checkJsonPostBasicAuth(
            '{"mysql":[]}',
            ['REQUEST_METHOD' => 'POST']
        );

        $this->assertFalse($outcome['allowed']);
        $this->assertSame(401, $outcome['status']);
        $this->assertArrayHasKey('WWW-Authenticate', $outcome['headers']);
    }

    public function testJsonDetectionRejectsInvalidBody(): void
    {
        $this->assertTrue(ApiRequestGuard::isJson('{"mysql":[]}'));
        $this->assertFalse(ApiRequestGuard::isJson(''));
        $this->assertFalse(ApiRequestGuard::isJson('not-json'));
    }
}
