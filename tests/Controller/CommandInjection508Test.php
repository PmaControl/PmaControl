<?php

declare(strict_types=1);

use App\Controller\Docker;
use App\Library\ShellCommand;
use PHPUnit\Framework\TestCase;

final class CommandInjection508Test extends TestCase
{
    public function testGzipCommandEscapesFullPathWithoutCd(): void
    {
        $path = "/tmp/pma control/dump'; id.sql";

        $this->assertSame('nice gzip -- ' . escapeshellarg($path), ShellCommand::gzip($path));
        $this->assertSame('nice gzip -d -- ' . escapeshellarg($path), ShellCommand::gzip($path, true));
        $this->assertStringNotContainsString('cd ', ShellCommand::gzip($path));
    }

    public function testExistingShellCommandBuilderApiStillWorks(): void
    {
        $command = ShellCommand::of('tar')
            ->flag('-czf')
            ->arg('/tmp/archive path.tar.gz')
            ->arg('/srv/www/pmacontrol')
            ->redirect(2, '>>', '/tmp/tar error.log')
            ->toString();

        $this->assertSame(
            'tar -czf '
            . escapeshellarg('/tmp/archive path.tar.gz')
            . ' '
            . escapeshellarg('/srv/www/pmacontrol')
            . ' 2>> '
            . escapeshellarg('/tmp/tar error.log'),
            $command
        );
    }

    public function testDockerImagePullCommandValidatesAndEscapesReference(): void
    {
        $command = Docker::buildImagePullCommand('proxysql/proxysql', '2.7.3');

        $this->assertSame('docker image pull ' . escapeshellarg('proxysql/proxysql:2.7.3'), $command);
        $this->assertNull(Docker::buildImagePullCommand('repo/name; id', 'latest'));
        $this->assertNull(Docker::buildImagePullCommand('repo/name', 'latest;id'));
        $this->assertNull(Docker::buildImagePullCommand('-repo/name', 'latest'));
    }

    public function testSkopeoInspectCommandValidatesAndEscapesImageName(): void
    {
        $command = Docker::buildSkopeoInspectCommand('registry.local:5000/proxysql/proxysql');

        $this->assertSame(
            'skopeo inspect ' . escapeshellarg('docker://registry.local:5000/proxysql/proxysql'),
            $command
        );
        $this->assertNull(Docker::buildSkopeoInspectCommand('repo/name; id'));
        $this->assertNull(Docker::buildSkopeoInspectCommand('repo/$(id)'));
        $this->assertNull(Docker::buildSkopeoInspectCommand('-repo/name'));
    }

    public function testGitLogRangeCommandRejectsUnsafeBuildValues(): void
    {
        $this->assertSame(
            'git log ' . escapeshellarg('abc1234..HEAD') . ' --pretty=format:"%H"',
            ShellCommand::gitLogRange('abc1234')
        );

        $this->assertNull(ShellCommand::gitLogRange('abc1234; id'));
        $this->assertNull(ShellCommand::gitLogRange('$(id)'));
        $this->assertNull(ShellCommand::gitLogRange('-abc1234'));
        $this->assertNull(ShellCommand::gitLogRange('abc..def'));
        $this->assertNull(ShellCommand::gitLogRange('abc 1234'));
    }

    public function testTicket508LegacyShellPatternsAreRemoved(): void
    {
        $system = (string) file_get_contents(__DIR__ . '/../../App/Library/System.php');
        $docker = (string) file_get_contents(__DIR__ . '/../../App/Controller/Docker.php');
        $file = (string) file_get_contents(__DIR__ . '/../../App/Library/File.php');
        $cleaner = (string) file_get_contents(__DIR__ . '/../../App/Controller/Cleaner.php');
        $git = (string) file_get_contents(__DIR__ . '/../../App/Library/Git.php');

        $this->assertStringNotContainsString('dig +short ".$hostname', $system);
        $this->assertStringContainsString('dns_get_record($hostname', $system);

        $this->assertStringNotContainsString('docker image pull ".$ob->name.":".$ob->tag', $docker);
        $this->assertStringContainsString('ShellCommand::dockerImagePull($name, $tag)', $docker);
        $this->assertStringNotContainsString('skopeo inspect docker://".$ob->name', $docker);
        $this->assertStringContainsString('ShellCommand::skopeoDockerInspect($name)', $docker);

        $this->assertStringNotContainsString('shell_exec("cd ".$path." && nice gzip', $file);
        $this->assertStringNotContainsString('shell_exec("cd ".$path." && nice gzip', $cleaner);
        $this->assertStringContainsString('ShellCommand::gzip((string) $path_file)', $file);
        $this->assertStringContainsString('ShellCommand::gzip((string) $path_file)', $cleaner);

        $this->assertStringNotContainsString('git log \'.$build.\'..HEAD', $git);
        $this->assertStringContainsString('ShellCommand::gitLogRange((string) $build)', $git);
    }
}
