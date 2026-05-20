<?php

declare(strict_types=1);

use App\Library\Format;
use PHPUnit\Framework\TestCase;

if (!defined('IMG')) {
    define('IMG', '/assets');
}

final class FormatTest extends TestCase
{
    public function testBytesFormatsHumanReadableSize(): void
    {
        $this->assertSame('1.00 Ko', Format::bytes(1024));
        $this->assertSame('1.50 Ko', Format::bytes(1536));
    }

    public function testBytesHandlesEmptyAndInvalidValues(): void
    {
        $this->assertSame('', Format::bytes(null));
        $this->assertSame('', Format::bytes('invalid'));
        $this->assertSame('', Format::bytesOrEmpty(0));
        $this->assertSame('n/a', Format::bytesOrNa(null));
        $this->assertSame('0 o', Format::bytesZero(null));
    }

    public function testBytesSupportsUnitStylesAndMinimumUnits(): void
    {
        $this->assertSame('1000.00 o', Format::bytes(1000));
        $this->assertSame('1.00 MB', Format::bytes(1024 * 1024, 2, 'si'));
        $this->assertSame('1.00 MiB', Format::bytes(1024 * 1024, 2, 'iec'));
        $this->assertSame('0.00 KB', Format::bytes(0, 2, 'si', ['min_unit' => 1]));
        $this->assertSame('0.00 MB', Format::bytes(1024, 2, 'si', ['min_unit' => 2]));
        $this->assertSame('1 KiB', Format::bytes(1024, 2, 'iec', ['trim_trailing_zeros' => true]));
    }

    public function testBytePartsKeepsMysqlUnitSuffixes(): void
    {
        $parts = Format::byteParts(1024 * 1024, 'mysql');

        $this->assertSame(2, $parts['factor']);
        $this->assertSame('M', $parts['unit']);
        $this->assertSame(1.0, $parts['value']);
        $this->assertSame(1024 * 1024, (int)$parts['arrondi']);
    }

    public function testDurationHelpersKeepExistingDisplayContracts(): void
    {
        $this->assertSame('NULL', Format::duration(null));
        $this->assertSame('59s', Format::duration(59));
        $this->assertSame('1m 1s', Format::duration(61));
        $this->assertSame('1h 1m 1s', Format::duration(3661));
        $this->assertSame('1m 01s', Format::elapsedCompact(61));
        $this->assertSame('1h 01m', Format::elapsedCompact(3661));
        $this->assertSame('1d 01h', Format::elapsedCompact(90000));
        $this->assertSame('1 days, 1 hours, 1 minutes and 1 seconds', Format::durationLong(90061));
    }

    public function testPingSwitchesToSecondsAboveOneSecond(): void
    {
        $this->assertSame('500 ms', Format::ping(0.5, 0));
        $this->assertSame('1.5 s', Format::ping(1.5, 1));
    }

    public function testGetMySQLNumVersionDetectsEnterpriseMariadb(): void
    {
        $version = Format::getMySQLNumVersion('10.6.19-15-MariaDB-enterprise-log', 'MariaDB Enterprise Server');

        $this->assertSame('10.6.19', $version['number']);
        $this->assertSame('MariaDB', $version['fork']);
        $this->assertTrue($version['enterprise']);
    }

    public function testGetMySQLNumVersionCommentOverridesFork(): void
    {
        $version = Format::getMySQLNumVersion('8.0.35', 'Percona Server (GPL), Release 35');

        $this->assertSame('8.0.35', $version['number']);
        $this->assertSame('Percona', $version['fork']);
        $this->assertFalse($version['enterprise']);
    }

    public function testGetMySQLNumVersionHandlesPerconaVersionSuffixWithoutWarning(): void
    {
        $version = Format::getMySQLNumVersion('8.4.8-8', "Percona Server (GPL), Release '8', Revision '1c288264'");

        $this->assertSame('8.4.8', $version['number']);
        $this->assertSame('Percona', $version['fork']);
        $this->assertTrue($version['enterprise']);
    }

    public function testMysqlVersionRendersProxySqlLabel(): void
    {
        $label = Format::mysqlVersion('2.5.5', 'ProxySQL');

        $this->assertStringContainsString('ProxySQL', $label);
        $this->assertStringContainsString('/assets/icon/proxysql.svg', $label);
        $this->assertStringContainsString('2.5.5', $label);
    }

    public function testGetLogoUsesSvgProxyAndMaxScaleIcons(): void
    {
        $this->assertStringContainsString('/assets/icon/proxysql.svg', Format::getLogo('proxysql'));
        $this->assertStringContainsString('/assets/icon/maxscale.svg', Format::getLogo('maxscale'));
    }
}
