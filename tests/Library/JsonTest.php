<?php

declare(strict_types=1);

use App\Library\Json;
use PHPUnit\Framework\TestCase;

final class JsonTest extends TestCase
{
    public function testGetDataFromFileReturnsDecodedArray(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'json-test-');
        file_put_contents($file, '{"name":"pmacontrol","enabled":true}');

        try {
            $data = Json::getDataFromFile($file);

            $this->assertSame('pmacontrol', $data['name']);
            $this->assertTrue($data['enabled']);
        } finally {
            @unlink($file);
        }
    }

    public function testGetDataFromFileThrowsWhenFileDoesNotExist(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("doesn't exit");

        Json::getDataFromFile('/tmp/this-file-should-not-exist-anymore.json');
    }

    public function testIsJsonThrowsOnMalformedPayload(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Syntax error, malformed JSON');

        Json::isJson('{"broken":}');
    }
}
