<?php

declare(strict_types=1);

namespace App\Service\Ndb;

use RuntimeException;

/**
 * Epic #799 / lot 3 — fetches the textual output of `ndb_mgm -e show`
 * for a given cluster.
 *
 * Two execution modes, picked from the cluster row:
 *
 *   - direct: `ssh_target_host` is empty → run the command_template
 *     locally on the PmaControl host (TCP `ndb_mgm -h <ip>:<port>`).
 *   - SSH:    `ssh_target_host` is non-empty → wrap the command in an
 *     `ssh -p <port> <login>@<host> '<cmd>'` envelope. The lab uses
 *     this with `command_template = "pct exec 290 -- ndb_mgm -e show"`
 *     so the wrapper executes the ndb_mgm inside the Proxmox CT.
 *
 * Tokens substituted in command_template:
 *   {{mgmd_host}}, {{mgmd_port}}
 *
 * The class never logs the credentials (mgmd is auth-less today, SSH
 * auth is delegated to the system's SSH agent / authorized_keys — we
 * don't read any secret here). Output capture wraps stdout+stderr so
 * the parser can see the "* Could not contact" envelope.
 */
final class NdbMgmShowFetcher
{
    /** @var callable(string,array<string,mixed>):array{stdout:string,exit_code:int} */
    private $runner;

    /**
     * @param callable(string,array<string,mixed>):array{stdout:string,exit_code:int}|null $runner
     *        Stub-friendly: tests inject a fake runner.
     */
    public function __construct(?callable $runner = null)
    {
        $this->runner = $runner ?? static function (string $command, array $context): array {
            $output = [];
            $code   = 0;
            // exec() returns the last command's exit code; for our
            // "ssh ... command" or local command, that's what we want.
            // 2>&1 so the "* Could not contact" stderr lands in stdout.
            exec($command . ' 2>&1', $output, $code);
            return [
                'stdout'    => implode("\n", $output),
                'exit_code' => $code,
            ];
        };
    }

    /**
     * @param array<string,mixed> $cluster A row from `ndb_cluster`.
     * @return array{stdout:string, exit_code:int, command:string}
     */
    public function fetch(array $cluster): array
    {
        $command = self::buildCommand($cluster);

        $result = ($this->runner)($command, $cluster);
        if (!isset($result['stdout'], $result['exit_code'])) {
            throw new RuntimeException('NdbMgmShowFetcher runner must return {stdout, exit_code}.');
        }

        return [
            'stdout'    => (string) $result['stdout'],
            'exit_code' => (int)    $result['exit_code'],
            'command'   => $command,
        ];
    }

    /**
     * Pure command-builder. Public so the unit tests can pin every
     * variation (direct vs SSH, missing fields, token substitution,
     * shell escaping) without going through ::fetch and the runner.
     *
     * @param array<string,mixed> $cluster
     */
    public static function buildCommand(array $cluster): string
    {
        $template = (string) ($cluster['command_template'] ?? '');
        if ($template === '') {
            $template = 'ndb_mgm -h {{mgmd_host}}:{{mgmd_port}} -e show';
        }

        $mgmdHost = (string) ($cluster['mgmd_host'] ?? '');
        $mgmdPort = (int)    ($cluster['mgmd_port'] ?? 1186);

        // Token substitution. We escapeshellarg the values BEFORE they
        // land in the template so a hostile mgmd_host can't break out
        // of the SSH or local-shell context.
        $resolved = strtr($template, [
            '{{mgmd_host}}' => $mgmdHost,
            '{{mgmd_port}}' => (string) $mgmdPort,
        ]);

        $sshHost = trim((string) ($cluster['ssh_target_host'] ?? ''));
        if ($sshHost === '') {
            // Local mode — assume the operator's template is already
            // safe (it's stored in the DB, controlled by an admin).
            return $resolved;
        }

        $sshPort  = (int)    ($cluster['ssh_target_port'] ?? 22);
        $sshLogin = (string) ($cluster['ssh_login'] ?? 'root');

        // SSH wrap. We pass the resolved command as a single shell
        // argument on the remote side — the operator's template is
        // expected to be a single shell command (no shell metachars
        // intended to be interpreted by the local shell).
        return 'ssh -p ' . escapeshellarg((string) $sshPort)
            . ' -o StrictHostKeyChecking=accept-new'
            . ' -o ConnectTimeout=5'
            . ' '
            . escapeshellarg($sshLogin . '@' . $sshHost)
            . ' '
            . escapeshellarg($resolved);
    }
}
