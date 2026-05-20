<?php

declare(strict_types=1);

use App\Service\Ndb\NdbMgmShowFetcher;
use PHPUnit\Framework\TestCase;

final class NdbMgmShowFetcherTest extends TestCase
{
    public function testBuildsLocalCommandWithTokenSubstitution(): void
    {
        $cmd = NdbMgmShowFetcher::buildCommand([
            'mgmd_host'        => '10.68.68.244',
            'mgmd_port'        => 1186,
            'ssh_target_host'  => '',
            'command_template' => 'ndb_mgm -h {{mgmd_host}}:{{mgmd_port}} -e show',
        ]);

        $this->assertSame('ndb_mgm -h 10.68.68.244:1186 -e show', $cmd);
    }

    public function testWrapsInSshWhenSshTargetHostIsSet(): void
    {
        $cmd = NdbMgmShowFetcher::buildCommand([
            'mgmd_host'        => '10.68.68.244',
            'mgmd_port'        => 1186,
            'ssh_target_host'  => '10.68.68.17',
            'ssh_target_port'  => 22,
            'ssh_login'        => 'root',
            'command_template' => 'pct exec 290 -- ndb_mgm -e show',
        ]);

        $this->assertStringStartsWith("ssh -p '22' ", $cmd);
        $this->assertStringContainsString("'root@10.68.68.17'", $cmd);
        // The remote command is single-quoted as one shell argument.
        $this->assertStringContainsString("'pct exec 290 -- ndb_mgm -e show'", $cmd);
        // Hardening flags applied on every SSH invocation.
        $this->assertStringContainsString('StrictHostKeyChecking=accept-new', $cmd);
        $this->assertStringContainsString('ConnectTimeout=5', $cmd);
    }

    public function testFallsBackToDefaultTemplateWhenEmpty(): void
    {
        $cmd = NdbMgmShowFetcher::buildCommand([
            'mgmd_host'        => '127.0.0.1',
            'mgmd_port'        => 1186,
            'ssh_target_host'  => '',
            'command_template' => '',
        ]);

        $this->assertSame('ndb_mgm -h 127.0.0.1:1186 -e show', $cmd);
    }

    public function testFetchInvokesRunnerAndReturnsStructuredResult(): void
    {
        $stub = static function (string $command, array $context): array {
            return [
                'stdout'    => "Connected to Management Server at: 10.68.68.244:1186\n[ndbd(NDB)] 0 node(s)\n",
                'exit_code' => 0,
            ];
        };

        $fetcher = new NdbMgmShowFetcher($stub);
        $result  = $fetcher->fetch([
            'mgmd_host'        => '10.68.68.244',
            'mgmd_port'        => 1186,
            'ssh_target_host'  => '',
            'command_template' => '',
        ]);

        $this->assertSame(0, $result['exit_code']);
        $this->assertStringContainsString('Connected to Management Server', $result['stdout']);
        $this->assertSame('ndb_mgm -h 10.68.68.244:1186 -e show', $result['command']);
    }

    public function testRejectsMalformedRunnerReturn(): void
    {
        $bad = static function (): array {
            return ['stdout' => 'oops']; // missing exit_code
        };

        $this->expectException(RuntimeException::class);
        (new NdbMgmShowFetcher($bad))->fetch(['mgmd_host' => 'h', 'mgmd_port' => 1186]);
    }
}
