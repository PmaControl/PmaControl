<?php

declare(strict_types=1);

use phpseclib3\Net\SSH2;
use PHPUnit\Framework\TestCase;

if (!defined('IS_CLI')) {
    define('IS_CLI', true);
}
if (!defined('CRYPT_KEY')) {
    define('CRYPT_KEY', 'pmacontrol-test-key');
}
if (!defined('TMP')) {
    define('TMP', __DIR__ . '/../../tmp/');
}
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

/**
 * Couvre les fixes apportés à App\Controller\Ssh::associate :
 *  - #162 timeout SSH2 (3s vs défaut 10s)
 *  - #161 pré-check TCP fsockopen (skip rapide d'un hôte mort)
 *  - #164 helper isAssociateRunning (lock vivant / orphelin / absent)
 *  - smoke-test du SQL des candidats utilisé par associate()
 *
 * On ne ré-exécute pas l'orchestration entière (spawn de workers + queue
 * System V) : ce sont des effets globaux, validés en CLI manuellement.
 */
final class SshAssociateTest extends TestCase
{
    private const BLACKHOLE_IP   = '192.0.2.1';   // RFC 5737 TEST-NET-1, garanti injoignable
    private const BLACKHOLE_PORT = 22;

    /**
     * #162 — passer 3 comme 3e argument à SSH2 doit raccourcir le timeout
     * de connexion à ~3 s sur un hôte injoignable (vs 10 s par défaut).
     */
    public function testSsh2ConnectionTimeoutHonoursThirdArg(): void
    {
        $start = microtime(true);
        try {
            $ssh = new SSH2(self::BLACKHOLE_IP, self::BLACKHOLE_PORT, 3);
            // login sera tenté mais aura déjà échoué côté connect ; on se fiche du résultat
            @$ssh->login('root', 'whatever');
        } catch (\Throwable $e) {
            // attendu : UnableToConnectException
        }
        $elapsed = microtime(true) - $start;

        $this->assertLessThan(
            4.5,
            $elapsed,
            sprintf('SSH2 timeout=3 doit rendre la main en <4.5s, observé %.2fs', $elapsed)
        );
        $this->assertGreaterThan(
            2.5,
            $elapsed,
            sprintf('SSH2 timeout=3 ne doit pas rendre la main avant ~3s, observé %.2fs', $elapsed)
        );
    }

    /**
     * #161 — fsockopen sur un hôte injoignable avec timeout 1s doit échouer
     * en ≤ 1.5 s, ce qui justifie le pré-check avant new SSH2().
     */
    public function testFsockopenPrecheckFailsFastOnBlackholeHost(): void
    {
        $start = microtime(true);
        $fp    = @fsockopen(self::BLACKHOLE_IP, self::BLACKHOLE_PORT, $errno, $errstr, 1.0);
        $elapsed = microtime(true) - $start;

        if ($fp !== false) {
            fclose($fp);
            $this->fail('fsockopen sur 192.0.2.1 ne devrait jamais réussir');
        }

        $this->assertLessThan(
            1.5,
            $elapsed,
            sprintf('fsockopen 1s doit échouer en <1.5s, observé %.2fs', $elapsed)
        );
    }

    /**
     * #161 — fsockopen sur un service local toujours up (le démon mysql
     * écoute sur 127.0.0.1:3306 sur cet hôte) doit réussir presque
     * instantanément, prouvant qu'on ne pénalise pas les hôtes joignables.
     */
    public function testFsockopenPrecheckPassesOnReachableHost(): void
    {
        $start = microtime(true);
        $fp    = @fsockopen('127.0.0.1', 3306, $errno, $errstr, 1.0);
        $elapsed = microtime(true) - $start;

        if ($fp === false) {
            $this->markTestSkipped('Pas de service TCP joignable sur 127.0.0.1:3306, test SKIPPED');
        }
        fclose($fp);

        $this->assertLessThan(
            0.2,
            $elapsed,
            sprintf('fsockopen vers un service local doit être <0.2s, observé %.2fs', $elapsed)
        );
    }

    /**
     * #164 — pas de lock fichier ⇒ isAssociateRunning() === false.
     */
    public function testIsAssociateRunningReturnsFalseWhenNoLock(): void
    {
        $id   = 999991;
        $lock = TMP . 'lock' . DS . 'ssh_associate_' . $id . '.lock';
        @unlink($lock);

        $this->assertFalse($this->callIsAssociateRunning($id));
    }

    /**
     * #164 — lock pointant sur le PID courant (vivant) ⇒ true.
     */
    public function testIsAssociateRunningReturnsTrueForLiveLock(): void
    {
        $id   = 999992;
        $lock = TMP . 'lock' . DS . 'ssh_associate_' . $id . '.lock';
        @mkdir(dirname($lock), 0775, true);
        file_put_contents($lock, getmypid() . "\n" . date('c') . "\n");

        try {
            $this->assertTrue($this->callIsAssociateRunning($id));
        } finally {
            @unlink($lock);
        }
    }

    /**
     * #164 — lock orphelin (PID inexistant) ⇒ false ET fichier supprimé.
     */
    public function testIsAssociateRunningCleansStaleLock(): void
    {
        $id   = 999993;
        $lock = TMP . 'lock' . DS . 'ssh_associate_' . $id . '.lock';
        @mkdir(dirname($lock), 0775, true);
        // PID très haut très improbable d'exister
        file_put_contents($lock, "4194303\n" . date('c') . "\n");

        $this->assertFalse($this->callIsAssociateRunning($id));
        $this->assertFileDoesNotExist($lock);
    }

    /**
     * #164 — getAssociateStartedAt extrait l'epoch depuis le lock pour
     * permettre l'affichage "association en cours… 12s".
     */
    public function testGetAssociateStartedAtReturnsEpochFromLock(): void
    {
        $id      = 999994;
        $lock    = TMP . 'lock' . DS . 'ssh_associate_' . $id . '.lock';
        $started = time() - 17;
        @mkdir(dirname($lock), 0775, true);
        file_put_contents($lock, getmypid() . "\n" . date('c', $started) . "\n");

        try {
            $ts = $this->callGetAssociateStartedAt($id);
            $this->assertIsInt($ts);
            $this->assertEqualsWithDelta($started, $ts, 1, 'started_at doit refléter la 2e ligne du lock à 1s près');
            $this->assertGreaterThanOrEqual(16, time() - $ts, 'elapsed ≈ 17s attendu');
        } finally {
            @unlink($lock);
        }
    }

    /**
     * #164 — pas de lock ⇒ getAssociateStartedAt() === null.
     */
    public function testGetAssociateStartedAtReturnsNullWhenNoLock(): void
    {
        $id   = 999995;
        $lock = TMP . 'lock' . DS . 'ssh_associate_' . $id . '.lock';
        @unlink($lock);

        $this->assertNull($this->callGetAssociateStartedAt($id));
    }

    /**
     * #164 — lock présent mais sans 2e ligne valide ⇒ fallback sur mtime.
     */
    public function testGetAssociateStartedAtFallsBackToMtime(): void
    {
        $id   = 999996;
        $lock = TMP . 'lock' . DS . 'ssh_associate_' . $id . '.lock';
        @mkdir(dirname($lock), 0775, true);
        file_put_contents($lock, getmypid() . "\n");
        touch($lock, time() - 42);

        try {
            $ts = $this->callGetAssociateStartedAt($id);
            $this->assertIsInt($ts);
            $this->assertEqualsWithDelta(time() - 42, $ts, 1);
        } finally {
            @unlink($lock);
        }
    }

    /**
     * Smoke test : la requête SQL des candidats utilisée par associate()
     * doit être valide (parse + exécution OK) pour la clé id=1.
     */
    public function testCandidateServersSqlIsValid(): void
    {
        $cmd = ['mysql', 'pmacontrol', '-N', '-B', '-e',
            "WITH z AS (SELECT a.id FROM mysql_server a "
            . "INNER JOIN link__mysql_server__ssh_key b ON a.id = b.id_mysql_server "
            . "WHERE active=1 AND a.is_vip=0 AND a.is_deleted=0 AND b.id_ssh_key IN (1)) "
            . "SELECT b.id, b.ssh_port FROM mysql_server b, ssh_key c "
            . "WHERE c.id IN (1) AND b.id NOT IN (SELECT id FROM z) LIMIT 1"];

        $proc = proc_open(
            $cmd,
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes
        );

        if (!is_resource($proc)) {
            $this->markTestSkipped('Impossible de lancer mysql en CLI');
        }

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $rc = proc_close($proc);

        $this->assertSame(0, $rc, 'mysql a renvoyé une erreur : ' . $stderr);
    }

    private function callIsAssociateRunning(int $id): bool
    {
        $this->loadSshClass();
        return \App\Controller\Ssh::isAssociateRunning($id);
    }

    private function callGetAssociateStartedAt(int $id): ?int
    {
        $this->loadSshClass();
        $ts = \App\Controller\Ssh::getAssociateStartedAt($id);
        return $ts === null ? null : (int) $ts;
    }

    private function loadSshClass(): void
    {
        // Charger la classe à la volée pour éviter d'avoir besoin de tout
        // le bootstrap Glial (Sgbd, Controller…).
        if (!class_exists('App\\Controller\\Ssh', false)) {
            require_once __DIR__ . '/../../App/Controller/Ssh.php';
        }
    }
}
