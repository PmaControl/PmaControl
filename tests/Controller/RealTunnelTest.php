<?php
use PHPUnit\Framework\TestCase;
use App\Controller\Tunnel; 

class RealTunnelTest extends TestCase
{
    private Tunnel $parser;
    private ?int $tunnelPid = null;
    private int $localPort;

    protected function setUp(): void
    {
        $this->localPort = $this->findFreePort();
        $this->tunnelPid = $this->startSshTunnel($this->localPort);

        if ($this->tunnelPid === null) {
            $this->markTestSkipped('Impossible de démarrer un tunnel SSH vers localhost (probablement pas de clé SSH ou accès refusé). Test SKIPPED.');
        }

        // attendre jusqu'à 5s pour que le tunnel apparaisse dans ps
        $ok = false;
        $tries = 0;
        while ($tries++ < 10) {
            usleep(5000);
            $lines = Tunnel::getTunnel([]);
            foreach ($lines as $line) {
                if (strpos($line, (string)$this->localPort) !== false && strpos($line, '-L') !== false) {
                    $ok = true;
                    break 2;
                }
            }
        }

        if (! $ok) {
            $this->tearDown();
            $this->markTestSkipped('Le tunnel SSH n\'est pas apparu dans ps malgré un démarrage OK. Test SKIPPED.');
        }
    }

    public function testParseSshLineOnRealTunnel(): void
    {
        // récupérer les lignes réelles - uniquement celles avec -L ou -R
        $lines = Tunnel::getTunnel([]);

        // Vérifie que le retour est un tableau
        $this->assertIsArray($lines);

        $found = false;
        foreach ($lines as $line) {
            if (strpos($line, '-L') === false && strpos($line, '-R') === false && strpos($line, '-D') === false) {
                continue;
            }
            // repérer la ligne qui contient notre port local
            if (strpos($line, (string)$this->localPort) !== false) {

                $parsed = Tunnel::parse([$line]); // ta fonction d'analyse
                $this->assertIsArray($parsed, 'parseSshLine doit retourner un tableau');
                $this->assertArrayHasKey('local_host', $parsed);
                $this->assertArrayHasKey('local_port', $parsed);
                $this->assertArrayHasKey('remote_host', $parsed);
                $this->assertArrayHasKey('remote_port', $parsed);
                $this->assertArrayHasKey('pid', $parsed);
                $this->assertArrayHasKey('user', $parsed);
                $this->assertArrayHasKey('command', $parsed);

                $this->assertEquals((int)$this->localPort, (int)$parsed['local_port']);
                // local_host devrait être 127.0.0.1 dans notre commande
                $this->assertContains($parsed['local_host'], ['127.0.0.1', 'localhost', '0.0.0.0']);

                // remote_host should be an IP or hostname (non empty)
                $this->assertNotEmpty($parsed['remote_host']);
                $this->assertGreaterThan(0, (int)$parsed['remote_port']);

                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'La ligne du tunnel réel doit être trouvée dans ps (contenant le port local).');
    }

    // ----------------------
    // Helpers
    // ----------------------
    private function findFreePort(): int
    {
        // ouvre un socket sur port 0 pour laisser le kernel attribuer un port libre
        $sock = @stream_socket_server("tcp://127.0.0.1:0", $errno, $errstr);
        if ($sock === false) {
            // fallback port raisonnable
            return 22222;
        }
        $name = stream_socket_get_name($sock, false);
        fclose($sock);
        if (preg_match('/:(\d+)$/', $name, $m)) {
            return (int)$m[1];
        }
        return 22222;
    }

    private function startSshTunnel(int $localPort): ?int
    {
        // commande ssh: forward localPort -> 127.0.0.1:22 (localhost)
        // -o BatchMode=yes => échoue si mot de passe requis (permet de skip)
        // -o ExitOnForwardFailure=yes => échoue si forwarding échoue
        // -fN => fork en arrière-plan (nécessite auth non interactive)
        $cmd = sprintf(
            "ssh -4 -fN -L %d:127.0.0.1:22 -o ExitOnForwardFailure=yes -o BatchMode=yes localhost 2>&1 & echo $!",
            $localPort
        );

        // exécution : on récupère le PID de la commande shell (pas toujours le PID du process ssh)
        $output = null;
        $pid = trim(shell_exec($cmd));

        usleep(500);
        // si la commande retourne une erreur (chaîne contenant 'Permission denied' etc.), on skip
        // Vérifier si ssh a échoué
        if ($pid === '' || !ctype_digit($pid)) {
            return null;
        }

        $pidInt = (int)$pid;

        // donner un peu de temps au process pour s'initialiser
        usleep(500);

        // vérifier que le PID correspond bien à un processus ssh
        $psLine = shell_exec("ps -p {$pidInt} -o comm=");
        if ($psLine === null || trim($psLine) === '') {
            // parfois le PID retourné est celui du shell wrapper ; essayons de repérer notre tunnel via 'ps' grep
            $lines = Tunnel::getTunnel([]);
            foreach ($lines as $line) {
                if (strpos($line, (string)$localPort) !== false && strpos($line, '-L') !== false) {
                    // tenter d'extraire pid depuis la ligne ps
                    $parts = preg_split('/\s+/', trim($line));
                    if (isset($parts[1]) && ctype_digit($parts[1])) {
                        return (int)$parts[1];
                    }
                }
            }
            return null;
        }

        return $pidInt;
    }

    protected function tearDown(): void
    {
        if ($this->tunnelPid > 0) {
            exec("kill {$this->tunnelPid}");
        }
    }
}