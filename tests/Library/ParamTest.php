<?php

declare(strict_types=1);

use App\Library\Param;
use PHPUnit\Framework\TestCase;

final class ParamTest extends TestCase
{
    public function testOptionRemovesMatchingFlagFromArray(): void
    {
        $params = ['--force', '--debug'];

        $result = Param::option($params, '--force');

        $this->assertTrue($result);
        $this->assertSame([1 => '--debug'], $params);
    }

    public function testOptionMatchesScalar(): void
    {
        $param = '--force';

        $this->assertTrue(Param::option($param, '--force'));
    }

    public function testOptionReturnsFalseWhenValueIsMissing(): void
    {
        $params = ['--debug'];

        $this->assertFalse(Param::option($params, '--force'));
        $this->assertSame(['--debug'], $params);
    }
}
