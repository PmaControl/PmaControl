<?php

declare(strict_types=1);

use App\Library\Bootstrap\TimezoneAligner;
use PHPUnit\Framework\TestCase;

/**
 * Regression coverage for issue #746.
 *
 * The bootstrap must converge to a single, validated timezone regardless of
 * how the underlying PHP build was provisioned. These tests pin the resolver
 * behaviour and the integration point in App/Webroot/Bootstrap.php so a
 * future PHP upgrade that drops the date.timezone ini directive cannot
 * silently re-introduce the daemons-write-UTC / Apache-reads-CEST drift.
 */
final class TimezoneAlignerTest extends TestCase
{
    private string $originalTimezone = '';

    protected function setUp(): void
    {
        $this->originalTimezone = date_default_timezone_get();
    }

    protected function tearDown(): void
    {
        if ($this->originalTimezone !== '') {
            @date_default_timezone_set($this->originalTimezone);
        }
    }

    public function testResolveConfiguredTimezoneAcceptsValidIdentifier(): void
    {
        $this->assertSame('Europe/Paris', TimezoneAligner::resolveConfiguredTimezone('Europe/Paris'));
        $this->assertSame('UTC', TimezoneAligner::resolveConfiguredTimezone('UTC'));
        $this->assertSame('America/New_York', TimezoneAligner::resolveConfiguredTimezone('America/New_York'));
    }

    public function testResolveConfiguredTimezoneTrimsSurroundingWhitespace(): void
    {
        $this->assertSame('Europe/Paris', TimezoneAligner::resolveConfiguredTimezone('  Europe/Paris '));
    }

    public function testResolveConfiguredTimezoneFallsBackOnEmptyOrInvalid(): void
    {
        $this->assertSame(TimezoneAligner::DEFAULT_TIMEZONE, TimezoneAligner::resolveConfiguredTimezone(null));
        $this->assertSame(TimezoneAligner::DEFAULT_TIMEZONE, TimezoneAligner::resolveConfiguredTimezone(''));
        $this->assertSame(TimezoneAligner::DEFAULT_TIMEZONE, TimezoneAligner::resolveConfiguredTimezone('   '));
        $this->assertSame(TimezoneAligner::DEFAULT_TIMEZONE, TimezoneAligner::resolveConfiguredTimezone('Not/A_Zone'));
        $this->assertSame(TimezoneAligner::DEFAULT_TIMEZONE, TimezoneAligner::resolveConfiguredTimezone('europe/paris'));
        $this->assertSame(TimezoneAligner::DEFAULT_TIMEZONE, TimezoneAligner::resolveConfiguredTimezone(42));
        $this->assertSame(TimezoneAligner::DEFAULT_TIMEZONE, TimezoneAligner::resolveConfiguredTimezone(['Europe/Paris']));
    }

    public function testApplyChangesProcessTimezoneAndReturnsResolvedIdentifier(): void
    {
        $applied = TimezoneAligner::apply('UTC');

        $this->assertSame('UTC', $applied);
        $this->assertSame('UTC', date_default_timezone_get());
    }

    public function testApplyFallsBackToDefaultOnInvalidInput(): void
    {
        $applied = TimezoneAligner::apply('Not/A_Zone');

        $this->assertSame(TimezoneAligner::DEFAULT_TIMEZONE, $applied);
        $this->assertSame(TimezoneAligner::DEFAULT_TIMEZONE, date_default_timezone_get());
    }

    public function testApplyAlsoHandlesNullAsDefault(): void
    {
        $applied = TimezoneAligner::apply(null);

        $this->assertSame(TimezoneAligner::DEFAULT_TIMEZONE, $applied);
    }

    public function testBootstrapWiresTimezoneAlignerEarly(): void
    {
        $bootstrap = (string) file_get_contents(__DIR__ . '/../../../App/Webroot/Bootstrap.php');

        $this->assertStringContainsString(
            'App\\Library\\Bootstrap\\TimezoneAligner::apply',
            $bootstrap,
            'Bootstrap.php must call TimezoneAligner::apply so every PmaControl process '
            . 'converges on the configured timezone (issue #746).'
        );
        $this->assertStringContainsString(
            "defined('PMACONTROL_TIMEZONE')",
            $bootstrap,
            'The configured value must come from PMACONTROL_TIMEZONE so operators can '
            . 'override it without editing core code.'
        );

        $loadOffset = strpos($bootstrap, '$config->load(CONFIG)');
        $applyOffset = strpos($bootstrap, 'TimezoneAligner::apply');
        $sgbdOffset = strpos($bootstrap, 'Sgbd::setConfig');

        $this->assertNotFalse($loadOffset);
        $this->assertNotFalse($applyOffset);
        $this->assertNotFalse($sgbdOffset);
        $this->assertGreaterThan(
            $loadOffset,
            $applyOffset,
            'TimezoneAligner::apply must run AFTER Config::load so PMACONTROL_TIMEZONE '
            . 'has been pulled in from configuration/pmacontrol.config.php.'
        );
        $this->assertLessThan(
            $sgbdOffset,
            $applyOffset,
            'TimezoneAligner::apply must run BEFORE Sgbd::setConfig so subsequent SQL '
            . 'queries (notably Extraction::extract) issue NOW()/UTC_TIMESTAMP() against '
            . 'a deterministic process timezone.'
        );
    }

    public function testSampleConfigDeclaresPmacontrolTimezoneConstant(): void
    {
        $sample = (string) file_get_contents(__DIR__ . '/../../../config_sample/pmacontrol.config.php');

        $this->assertStringContainsString(
            "defined('PMACONTROL_TIMEZONE')",
            $sample,
            'config_sample/pmacontrol.config.php must declare PMACONTROL_TIMEZONE so '
            . 'operators copying it to live config get a sensible default.'
        );
        $this->assertStringContainsString(
            "define('PMACONTROL_TIMEZONE', 'Europe/Paris')",
            $sample,
            'Sample default should match the in-code DEFAULT_TIMEZONE to avoid surprises.'
        );
    }
}
