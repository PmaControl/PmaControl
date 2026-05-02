<?php

declare(strict_types=1);

use App\Library\Mydumper;
use PHPUnit\Framework\TestCase;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

/**
 * Issue #572 — Mydumper::parseLog() must redact CLI passwords before
 * rendering log content on /job/index. The mydumper binary writes its
 * full command line to the log at startup, including `-p <password>`,
 * which would otherwise be exposed to any operator viewing the page.
 */
final class MydumperParseLogTest extends TestCase
{
    public function testParseLogMasksShortDashPWithSpace(): void
    {
        $log = '** (mydumper:1234): INFO: Started dump at: cmd: mydumper -h srv -u root -p Sup3rSecret! -P 3306 -B mydb';

        $out = Mydumper::parseLog($log);

        $this->assertStringNotContainsString('Sup3rSecret!', $out);
        $this->assertStringContainsString('-p ******', $out);
    }

    public function testParseLogMasksShortDashPWithEquals(): void
    {
        $log = 'cmd: mydumper -h srv -u root -p=Sup3rSecret! -P 3306';

        $out = Mydumper::parseLog($log);

        $this->assertStringNotContainsString('Sup3rSecret!', $out);
        $this->assertStringContainsString('-p=******', $out);
    }

    public function testParseLogMasksShortDashPWithoutSeparator(): void
    {
        // mysql / mydumper / xtrabackup all accept `-pPassword` (no space).
        $log = 'cmd: mysql -h srv -uroot -pSup3rSecret! -P 3306';

        $out = Mydumper::parseLog($log);

        $this->assertStringNotContainsString('Sup3rSecret!', $out);
        $this->assertStringContainsString('-p******', $out);
    }

    public function testParseLogMasksLongFormPasswordEquals(): void
    {
        $log = 'cmd: mydumper --password=Sup3rSecret! --user=root';

        $out = Mydumper::parseLog($log);

        $this->assertStringNotContainsString('Sup3rSecret!', $out);
        $this->assertStringContainsString('--password=******', $out);
    }

    public function testParseLogMasksLongFormPasswordSpace(): void
    {
        $log = 'cmd: mydumper --password Sup3rSecret! --user root';

        $out = Mydumper::parseLog($log);

        $this->assertStringNotContainsString('Sup3rSecret!', $out);
        $this->assertStringContainsString('--password ******', $out);
    }

    public function testParseLogMasksMultipleOccurrencesOnTheSameLine(): void
    {
        // Recovery scenario: source backup + target restore on one line.
        $log = 'mydumper -h src -u root -p Sup3rSrc | myloader -h dst -u root -p Sup3rDst';

        $out = Mydumper::parseLog($log);

        $this->assertStringNotContainsString('Sup3rSrc', $out);
        $this->assertStringNotContainsString('Sup3rDst', $out);
        $this->assertSame(2, substr_count($out, '-p ******'));
    }

    public function testRedactionBeforeAnsiConversionPreventsHtmlEntitySuffixLeaks(): void
    {
        $converter = new AnsiToHtmlConverter();
        $raw = 'cmd: mydumper -h srv -u root -p pa&ss --password="it\'s" -psemi;colon';

        $redacted = Mydumper::redactPasswords($raw);
        $out = Mydumper::parseLog($converter->convert($redacted));

        $this->assertStringNotContainsString('pa&ss', $out);
        $this->assertStringNotContainsString('pa&amp;ss', $out);
        $this->assertStringNotContainsString('amp;ss', $out);
        $this->assertStringNotContainsString('it&#039;s', $out);
        $this->assertStringNotContainsString('semi;colon', $out);
        $this->assertStringContainsString('-p ******', $out);
        $this->assertStringContainsString('--password=******', $out);
        $this->assertStringContainsString('-p******', $out);
    }

    public function testParseLogPreservesNonPasswordContent(): void
    {
        $log = "Started dump\n** Locking tables\n** Finished\n";

        $out = Mydumper::parseLog($log);

        // Newlines collapsed to <br>, no password to redact, content intact.
        $this->assertStringContainsString('Started dump', $out);
        $this->assertStringContainsString('Finished', $out);
        $this->assertStringContainsString('<br>', $out);
        $this->assertStringNotContainsString('******', $out);
    }

    public function testParseLogStillColorsCriticalAndWarning(): void
    {
        // Regression on the existing CRITICAL / WARNING coloring.
        $log = "** WARNING: read past end\n** CRITICAL: cannot connect";

        $out = Mydumper::parseLog($log);

        $this->assertStringContainsString('label-warning', $out);
        $this->assertStringContainsString('label-danger', $out);
        $this->assertStringContainsString('WARNING', $out);
        $this->assertStringContainsString('CRITICAL', $out);
    }

    public function testRedactPasswordsDoesNotMatchUnrelatedDashPLikeWords(): void
    {
        // tcp/foobar should NOT be touched: the negative lookbehind on
        // letters/digits/dashes prevents the regex from matching the `-p` of
        // a longer flag chain or word like `--tcp` or `drop`.
        $log = '--tcp 192.168.0.1 --no-progress drop database test';

        $out = Mydumper::redactPasswords($log);

        $this->assertSame($log, $out);
    }

    public function testRedactPasswordsMasksSpecialCharacterAndQuotedValues(): void
    {
        $log = 'mydumper -p pa&ss --password "quoted secret" --password=semi;colon -p="-eq" -p-Secret';

        $out = Mydumper::redactPasswords($log);

        $this->assertStringNotContainsString('pa&ss', $out);
        $this->assertStringNotContainsString('quoted secret', $out);
        $this->assertStringNotContainsString('semi;colon', $out);
        $this->assertStringNotContainsString('-eq', $out);
        $this->assertStringNotContainsString('-Secret', $out);
        $this->assertStringContainsString('-p ******', $out);
        $this->assertStringContainsString('--password ******', $out);
        $this->assertStringContainsString('--password=******', $out);
        $this->assertStringContainsString('-p=******', $out);
        $this->assertStringContainsString('-p******', $out);
    }

    public function testRedactPasswordsHandlesEmptyAndPlainStrings(): void
    {
        $this->assertSame('', Mydumper::redactPasswords(''));
        $this->assertSame('plain log line', Mydumper::redactPasswords('plain log line'));
    }
}
