<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\Tree as TreeInterval;
use App\Library\Debug;
use \Glial\Sgbd\Sgbd;

/**
 * Class responsible for tunnel workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Tunnel extends Controller
{
    static array $mysql_server_cache = [];

    private static array $tunnel_cache = [];
    private static int $tunnel_cache_expire = 60;


    /*
    public static function detectSshTunnels($params)
    {
        Debug::parseDebug($params);
        $result = [];
        // évite de récupérer la ligne grep
        $cmd = "ps -eaf | grep [s]sh";
        exec($cmd, $output);

        foreach ($output as $line) {
            // On récupère la première occurrence après -L (peut y avoir plusieurs -L dans la même ligne mais
            // ce cas est rare — on peut itérer toutes si besoin).
            if (!preg_match_all('/-L\s*([^\s]+)/', $line, $allMatches)) {
                continue;
            }

            foreach ($allMatches[1] as $Lpart) {
                // $Lpart = "8902:127.0.0.1:8989"  ou "127.0.0.1:8902:127.0.0.1:8989" (bind:port:host:hostport)
                $parts = explode(':', $Lpart);

                // Normaliser les cas
                if (count($parts) === 3) {
                    // port:host:hostport
                    $local_port  = $parts[0];
                    $host_between = $parts[1];
                    $remote_port = $parts[2];
                } elseif (count($parts) === 4) {
                    // bind:port:host:hostport  -> on prend le port (parts[1]) et host (parts[2])
                    $bind = $parts[0];
                    $local_port = $parts[1];
                    $host_between = $parts[2];
                    $remote_port = $parts[3];
                } else {
                    // format inattendu, on skip
                    continue;
                }

                // si host_between est "localhost" ou "127.0.0.1" ou "0.0.0.0", on cherche la vraie ip distante
                $loopbacks = ['127.0.0.1', 'localhost', '0.0.0.0'];
                $remote_ip = null;

                if (in_array($host_between, $loopbacks, true)) {
                    // chercher toutes les IPs dans la ligne (IPv4)
                    if (preg_match_all('/\b(?:\d{1,3}\.){3}\d{1,3}\b/', $line, $ipsAll)) {
                        $ips = $ipsAll[0];
                        // parcourir à rebours pour trouver la dernière IP "utile"
                        for ($i = count($ips) - 1; $i >= 0; $i--) {
                            $cand = $ips[$i];
                            // ignorer les loopbacks évidents
                            if (in_array($cand, $loopbacks, true)) {
                                continue;
                            }
                            // valider que chaque octet est <=255 (sécurité basique)
                            $octets = explode('.', $cand);
                            $valid = true;
                            foreach ($octets as $o) {
                                if ((int)$o > 255) { $valid = false; break; }
                            }
                            if (!$valid) continue;

                            // on prend la première (en partant de la fin) qui passe les checks
                            $remote_ip = $cand;
                            break;
                        }
                    }
                    // si on n'a trouvé aucune IP "utile", on retombe sur 'unknown'
                    if ($remote_ip === null) {
                        $remote_ip = 'unknown';
                    }
                } else {
                    // si host_between est déjà une IP ou un hostname, on la prend comme remote IP
                    // (cas fréquent : -L 8001:192.168.114.104:3306)
                    $remote_ip = $host_between;
                }

                $src = "{$host_between}:{$local_port}";
                $dst = "{$remote_ip}:{$remote_port}";
                $result[$src] = $dst;
            }
        }

        Debug::debug($result);
        return $result;
    }/**** */

    public static function agent($param = []): void
    {
        if (!is_array($param)) {
            $param = $param === null || $param === '' ? [] : [$param];
        }

        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);
        $now = date('Y-m-d H:i:s');

        // 1. Récupération des tunnels actifs
        $lines = self::getTunnel($param);
        $activeTunnels = [];
        foreach ($lines as $line) {
            $parsed = self::parse([$line]);
            if ($parsed !== null) {
                $activeTunnels[$parsed['pid']] = $parsed;
            }
        }

        // 2. Récupération des tunnels existants
        $sql = "SELECT * FROM ssh_tunnel";
        $res = $db->sql_query($sql);
        $existingTunnels = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $existingTunnels[(int)$row['pid']] = $row;
        }

        Debug::debug($existingTunnels, "TUNNEL EXISTING");

        // 3. Ajouter les tunnels actifs non présents
        foreach ($activeTunnels as $pid => $tunnel) {
            $servers_jump = json_encode($tunnel['jump_hosts'], JSON_UNESCAPED_UNICODE);

            if (!isset($existingTunnels[$pid])) {
                $sqlInsert = "INSERT INTO ssh_tunnel 
                    (id_mysql_server, date_created, local_host, local_port, servers_jump, remote_host, remote_port, pid, user, command)
                    VALUES (
                        " . ($tunnel['id_mysql_server'] ?? "NULL") . ",
                        '$now',
                        '" . addslashes($tunnel['local_host']) . "',
                        {$tunnel['local_port']},
                        '" . addslashes($servers_jump) . "',
                        '" . addslashes($tunnel['remote_host']) . "',
                        {$tunnel['remote_port']},
                        {$tunnel['pid']},
                        '" . addslashes($tunnel['user']) . "',
                        '" . addslashes($tunnel['command']) . "'
                    )";

                $db->sql_query($sqlInsert);
            } else {
                $sqlUpdate = "UPDATE ssh_tunnel SET
                        local_host = '" . addslashes($tunnel['local_host']) . "',
                        local_port = {$tunnel['local_port']},
                        servers_jump = '" . addslashes($servers_jump) . "',
                        remote_host = '" . addslashes($tunnel['remote_host']) . "',
                        remote_port = {$tunnel['remote_port']},
                        user = '" . addslashes($tunnel['user']) . "',
                        command = '" . addslashes($tunnel['command']) . "',
                        date_end = NULL
                    WHERE pid = {$tunnel['pid']}";

                $db->sql_query($sqlUpdate);
            }
        }

        // 4. Clôturer les tunnels terminés
        foreach ($existingTunnels as $pid => $row) {
            if (!isset($activeTunnels[$pid])) {
                $sqlUpdate = "UPDATE ssh_tunnel SET date_end = '$now' WHERE pid = $pid";
                $db->sql_query($sqlUpdate);

                $sqlDelete = "DELETE FROM ssh_tunnel WHERE pid = $pid";
                $db->sql_query($sqlDelete);
            }
        }


        self::syncMysqlServerForTunnels($param);
    }

/**
 * Retrieve `getTunnel`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return array Returned value for getTunnel.
 * @phpstan-return array
 * @psalm-return array
 * @example getTunnel(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public static function getTunnel($param): array
    {
        Debug::parseDebug($param);

        $output = [];

        if (!empty($param['mock'])) {
            $output = [
                "root  3084410  Mon Oct 11 10:00:00 2025  ssh -i id_rsa -fN -L 8003:127.0.0.1:3306 -J user@10.10.10.1 aurelien@192.168.114.21",
                "root  3085692  Mon Oct 11 10:00:00 2025  ssh -i id_rsa -fN -R 9000:127.0.0.1:8080 -J user@10.10.10.2 aurelien@192.168.114.22",
            ];
            return $output;
        }
        
        // On ne garde que les lignes SSH contenant une redirection de port (-L ou -R)
        // - On exclut les lignes "grep", "sshd", et "ssh-agent"

        $cmd = "ps -eaf | grep '[s]sh' | grep -E -- '-L|-R' | grep -vE 'sshd|ssh-agent|grep'";
        $cmd = "ps -eo user,pid,lstart,cmd | grep '[s]sh' | grep -E -- '-L|-R' | grep -vE 'sshd|ssh-agent|grep'";        
        exec($cmd, $output);

        Debug::debug($output,"TUNNEL");

        return $output;
    }
    
/**
 * Handle `parseDateInfo`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array $param Route parameters forwarded by the router.
 * @phpstan-param array $param
 * @psalm-param array $param
 * @return array Returned value for parseDateInfo.
 * @phpstan-return array
 * @psalm-return array
 * @example parseDateInfo(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public static function parseDateInfo(array $param): array
    {
        $start_time = $param[0];

        // Exemple d'entrée : "Tue Oct 14 01:32:49 2025"
        $dt = \DateTime::createFromFormat('D M d H:i:s Y', $start_time);

        if (!$dt) {
            return [
                'datetime_sql' => null,
                'seconds_diff' => null
            ];
        }

        $now = new \DateTime('now');

        return [
            'datetime_sql' => $dt->format('Y-m-d H:i:s'),
            'seconds_diff' => $now->getTimestamp() - $dt->getTimestamp()
        ];
    }

/**
 * Handle `parse`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array $param Route parameters forwarded by the router.
 * @phpstan-param array $param
 * @psalm-param array $param
 * @return mixed Returned value for parse.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @example parse(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public static function parse(array $param)
    {
        Debug::parseDebug($param);

        $line = $param[0];
        $parts = preg_split('/\s+/', trim($line));

        Debug::debug($parts, "PREG_SPLIT");

        if (count($parts) < 10) {
            return null; // ligne incomplète
        }

        $user  = $parts[0];
        $pid   = $parts[1];

        // Reconstitution de la date complète (colonnes 2 à 6)
        $start_time = implode(' ', array_slice($parts, 2, 5));

        // Le reste de la ligne = commande SSH
        $command = implode(' ', array_slice($parts, 7));

        // Détection du type de tunnel
        $type = null;
        if (strpos($command, '-L') !== false) $type = 'L';
        elseif (strpos($command, '-R') !== false) $type = 'R';
        elseif (strpos($command, '-D') !== false) $type = 'D';
        else return null;

        // Extraction du mapping de ports
        $local_host = $remote_host = null;
        $local_port = $remote_port = null;

        if ($type === 'L') {
            $forward = self::extractForwardSpec($command, 'L');
            if ($forward === null) {
                Debug::debug("NOT FOUND");
                return null;
            }

            $local_host  = $forward['bind_host'] ?? '127.0.0.1';
            $local_port  = (int) $forward['bind_port'];
            $remote_host = $forward['target_host'] ?? null;
            $remote_port = (int) $forward['target_port'];
        } elseif ($type === 'R') {
            $forward = self::extractForwardSpec($command, 'R');
            if ($forward === null) {
                Debug::debug("NOT FOUND");
                return null;
            }

            $remote_port = (int) $forward['bind_port'];
            $remote_host = $forward['target_host'] ?? null;
            $local_port  = (int) $forward['target_port'];
        } elseif ($type === 'D' && preg_match('/-D\s*(\d+)/', $command, $m)) {
            $local_port = (int)$m[1];
            $local_host = '127.0.0.1';
        } else {
            Debug::debug("NOT FOUND");
            return null;
        }

        $jump_hosts = self::extractSshRouteHosts($command);

        Debug::debug($jump_hosts, "REBOND");

        // Extraction de toutes les IP pour déterminer la "vraie" cible s’il faut
        preg_match_all('/(\d{1,3}\.){3}\d{1,3}/', $command, $ips);
        $all_ips = array_values(array_unique($ips[0]));

        $last_ip = !empty($all_ips) ? end($all_ips) : null;

        // Règle principale :
        // Si aucune IP valide avant le port distant OU si elle est 127.0.0.1 → on prend la dernière IP (la cible réelle)
        if (empty($remote_host) || $remote_host === '127.0.0.1' || $remote_host === 'localhost') {
            $remote_host = $last_ip;
        }

        // 🕒 Conversion date + calcul durée
        $date = self::parseDateInfo([$start_time]);
        $start_time = $date['datetime_sql'];
        $seconds    = $date['seconds_diff'];

        // 🔙 Résultat final
        $return = [
            'type'          => $type,
            'user'          => $user,
            'pid'           => $pid,
            'start_time'    => $start_time,
            'seconds'       => $seconds,
            'local_host'    => $local_host,
            'local_port'    => $local_port,
            'remote_host'   => $remote_host,
            'remote_port'   => $remote_port,
            'jump_hosts'    => $jump_hosts,
            'command'       => $command
        ];

        Debug::debug($return, "RETURN");
        return $return;
    }

    private static function extractForwardSpec(string $command, string $flag): ?array
    {
        if (!preg_match('/-' . preg_quote($flag, '/') . '\s+(\S+)/', $command, $match)) {
            return null;
        }

        return self::parseForwardSpecToken($match[1]);
    }

    private static function parseForwardSpecToken(string $spec): ?array
    {
        $parts = explode(':', trim($spec));
        $count = count($parts);

        if ($count === 3) {
            return array(
                'bind_host'   => '127.0.0.1',
                'bind_port'   => self::normalizePortValue($parts[0]),
                'target_host' => $parts[1],
                'target_port' => self::normalizePortValue($parts[2]),
            );
        }

        if ($count === 4) {
            return array(
                'bind_host'   => $parts[0],
                'bind_port'   => self::normalizePortValue($parts[1]),
                'target_host' => $parts[2],
                'target_port' => self::normalizePortValue($parts[3]),
            );
        }

        return null;
    }

    private static function normalizePortValue(string $port): ?int
    {
        $port = trim($port);

        if ($port === '' || !ctype_digit($port)) {
            return null;
        }

        return (int) $port;
    }

    private static function extractSshRouteHosts(string $command): array
    {
        $jump_hosts = [];

        if (preg_match('/-J\s+([^\s]+)/', $command, $jm)) {
            $jump_parts = explode(',', $jm[1]);
            foreach ($jump_parts as $j) {
                $host = self::parseSshHostToken($j);
                if ($host !== null) {
                    $jump_hosts[] = $host;
                }
            }

            $destination = self::extractSshDestinationHost($command);
            if ($destination !== null) {
                $last = end($jump_hosts);
                if ($last === false || $last['ip'] !== $destination['ip'] || $last['port'] !== $destination['port']) {
                    $jump_hosts[] = $destination;
                }
            }
        }

        return $jump_hosts;
    }

    private static function extractSshDestinationHost(string $command): ?array
    {
        $tokens = preg_split('/\s+/', trim($command));
        if (empty($tokens)) {
            return null;
        }

        $lastToken = end($tokens);
        if ($lastToken === false || str_starts_with($lastToken, '-')) {
            return null;
        }

        return self::parseSshHostToken($lastToken);
    }

    private static function parseSshHostToken(string $token): ?array
    {
        $token = trim($token);
        if ($token === '') {
            return null;
        }

        if (preg_match('/^(?:[^@\s]+@)?([\d\.]+)(?::(\d+))?$/', $token, $match)) {
            return [
                'ip' => $match[1],
                'port' => isset($match[2]) ? (int) $match[2] : 22,
            ];
        }

        return null;
    }

/**
 * Retrieve `getIdMysqlServerByIpPort`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array $param Route parameters forwarded by the router.
 * @phpstan-param array $param
 * @psalm-param array $param
 * @return ?int Returned value for getIdMysqlServerByIpPort.
 * @phpstan-return ?int
 * @psalm-return ?int
 * @example getIdMysqlServerByIpPort(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public static function getIdMysqlServerByIpPort(array $param): ?int
    {
        $ip = $param[0];
        $port = $param[1];

        if (empty(self::$mysql_server_cache)) {
            self::preloadMysqlServerCache();
        }

        $key = "$ip:$port";
        return self::$mysql_server_cache[$key] ?? null;
        
    }

/**
 * Handle `preloadMysqlServerCache`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for preloadMysqlServerCache.
 * @phpstan-return void
 * @psalm-return void
 * @example preloadMysqlServerCache(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public static function preloadMysqlServerCache(): void
    {
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT id, ip, port FROM mysql_server";
        $res = $db->sql_query($sql);

        self::$mysql_server_cache = [];

        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $key = $row['ip'] . ':' . $row['port'];
            self::$mysql_server_cache[$key] = (int)$row['id'];
        }
    }

/**
 * Render `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @example index(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index($param)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $mysqlServerIpById = [];
        $mysqlServerIps = [];

        $sqlMysql = "SELECT id, ip FROM mysql_server WHERE is_deleted = 0";
        $resMysql = $db->sql_query($sqlMysql);
        while ($rowMysql = $db->sql_fetch_array($resMysql, MYSQLI_ASSOC)) {
            $mysqlServerIpById[(int)$rowMysql['id']] = (string)$rowMysql['ip'];
            $mysqlServerIps[(string)$rowMysql['ip']] = true;
        }

        // Récupérer tous les tunnels
        $sql = "SELECT t.*, m.display_name AS mysql_display_name
                FROM ssh_tunnel t
                LEFT JOIN mysql_server m ON t.id_mysql_server = m.id
                ORDER BY t.remote_host, t.remote_port DESC";
        $res = $db->sql_query($sql);

        $data = [];
        $doublon = [];
        $duplicateGroups = [];


        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $row['servers_jump'] = json_decode($row['servers_jump'], true) ?: [];
            $row['is_reachable'] = self::isEndpointReachable([
                $row['local_host'],
                (int) $row['local_port'],
                0.3,
            ]);

            $key = crc32(trim($row['remote_host']).":".trim($row['remote_port']));
            
            $doublon[$key] = ($doublon[$key] ?? 0) + 1;
            $duplicateGroups[$key][] = $row;
            $row['duplicate_status'] = 'unique';
            $row['duplicate_reason'] = '';
            $row['duplicate_action'] = '';
            $row['command_source'] = self::extractTunnelCommandSource((string)($row['command'] ?? ''));

            $data[] = $row;
        }

        $duplicateStatusById = [];
        $pidsToPurge = [];

        foreach ($duplicateGroups as $rows) {
            if (count($rows) <= 1) {
                continue;
            }

            $keepId = self::chooseTunnelToKeep($rows, $mysqlServerIpById, $mysqlServerIps);

            foreach ($rows as $row) {
                $id = (int)$row['id'];
                $localHost = (string)$row['local_host'];
                $reason = isset($mysqlServerIps[$localHost])
                    ? "local_host matches mysql_server.ip"
                    : "duplicate remote endpoint";

                if ($id === $keepId) {
                    $duplicateStatusById[$id] = [
                        'status' => 'keep',
                        'reason' => $reason,
                        'action' => self::buildDuplicateAction($row, 'keep'),
                    ];
                    continue;
                }

                $duplicateStatusById[$id] = [
                    'status' => 'purge',
                    'reason' => $reason,
                    'action' => self::buildDuplicateAction($row, 'purge'),
                ];

                if (!empty($row['pid'])) {
                    $pidsToPurge[] = (int)$row['pid'];
                }
            }
        }

        foreach ($data as &$row) {
            $id = (int)$row['id'];
            if (isset($duplicateStatusById[$id])) {
                $row['duplicate_status'] = $duplicateStatusById[$id]['status'];
                $row['duplicate_reason'] = $duplicateStatusById[$id]['reason'];
                $row['duplicate_action'] = $duplicateStatusById[$id]['action'];
            }
        }
        unset($row);




        // Envoyer à la vue
        $this->set('doublon', $doublon);
        $this->set('pids_to_purge', array_values(array_unique($pidsToPurge)));
        $this->set('data', $data);
    }

    private static function chooseTunnelToKeep(array $rows, array $mysqlServerIpById, array $mysqlServerIps): int
    {
        usort($rows, static function (array $left, array $right) use ($mysqlServerIpById, $mysqlServerIps): int {
            return self::scoreTunnelDuplicateCandidate($right, $mysqlServerIpById, $mysqlServerIps)
                <=> self::scoreTunnelDuplicateCandidate($left, $mysqlServerIpById, $mysqlServerIps);
        });

        return (int)$rows[0]['id'];
    }

    private static function scoreTunnelDuplicateCandidate(array $row, array $mysqlServerIpById, array $mysqlServerIps): int
    {
        $score = 0;
        $localHost = (string)($row['local_host'] ?? '');
        $idMysqlServer = (int)($row['id_mysql_server'] ?? 0);

        if ($idMysqlServer > 0 && !empty($mysqlServerIpById[$idMysqlServer]) && $mysqlServerIpById[$idMysqlServer] === $localHost) {
            $score += 1000;
        }

        if (isset($mysqlServerIps[$localHost])) {
            $score += 500;
        }

        if ($idMysqlServer > 0) {
            $score += 100;
        }

        if (!empty($row['is_reachable'])) {
            $score += 20;
        }

        if (!empty($row['date_created'])) {
            $score += strtotime((string)$row['date_created']) ?: 0;
        }

        return $score;
    }

    private static function extractTunnelCommandSource(string $command): array
    {
        $source = [
            'script' => '',
            'config' => '',
        ];

        if ($command === '') {
            return $source;
        }

        if (preg_match('~(^|/)(tunnel\.sh)\b~', $command, $match)) {
            $source['script'] = $match[2];
        }

        if (preg_match('~(\.tunnel\.[A-Za-z0-9._-]+)~', $command, $match)) {
            $source['config'] = $match[1];
        }

        return $source;
    }

    private static function buildDuplicateAction(array $row, string $status): string
    {
        $source = $row['command_source'] ?? ['script' => '', 'config' => ''];
        $parts = [];

        if ($status === 'keep') {
            $parts[] = 'Keep this tunnel as the reference.';
        } else {
            $parts[] = 'Kill this PID and purge the duplicate tunnel.';
        }

        if (!empty($source['script']) || !empty($source['config'])) {
            $target = trim(($source['script'] ?: '').' '.($source['config'] ?: ''));
            $parts[] = 'Then fix '.$target.' to stop recreating it.';
        } else {
            $parts[] = 'Then fix the launcher that recreates this SSH tunnel.';
        }

        return implode(' ', $parts);
    }

/**
 * Check whether a TCP endpoint is reachable.
 *
 * @param array<int,mixed> $param [host, port, timeout?]
 * @return bool
 */
    public static function isEndpointReachable(array $param): bool
    {
        $host = $param[0] ?? '';
        $port = isset($param[1]) ? (int) $param[1] : 0;
        $timeout = isset($param[2]) ? (float) $param[2] : 0.5;
        $connector = $param[3] ?? null;

        if ($host === '' || $port <= 0) {
            return false;
        }

        if (is_callable($connector)) {
            return (bool) $connector($host, $port, $timeout);
        }

        $errno = 0;
        $errstr = '';
        $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);

        if (!is_resource($socket)) {
            return false;
        }

        $seconds = (int) $timeout;
        $microseconds = (int) (($timeout - $seconds) * 1000000);

        stream_set_timeout($socket, $seconds, $microseconds);

        $read = [$socket];
        $write = null;
        $except = null;
        $changed = @stream_select($read, $write, $except, $seconds, $microseconds);

        if ($changed === false) {
            fclose($socket);
            return false;
        }

        if ($changed > 0) {
            $probe = @fread($socket, 1);
            $meta  = stream_get_meta_data($socket);
            fclose($socket);

            if ($probe !== false && $probe !== '') {
                return true;
            }

            return empty($meta['eof']);
        }

        $meta = stream_get_meta_data($socket);
        fclose($socket);

        return empty($meta['eof']);
    }


/**
 * Handle `assignMysqlServerToTunnel`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array $param Route parameters forwarded by the router.
 * @phpstan-param array $param
 * @psalm-param array $param
 * @return ?int Returned value for assignMysqlServerToTunnel.
 * @phpstan-return ?int
 * @psalm-return ?int
 * @example assignMysqlServerToTunnel(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public static function assignMysqlServerToTunnel(array $param): ?int
    {
        // $param = [ip, port, pid (optionnel)]
        $ip   = $param[0];
        $port = $param[1];
        $pid  = $param[2] ?? null;

        // Récupère l'ID MySQL correspondant
        $id_mysql_server = self::getIdMysqlServerByIpPort([$ip, $port]);

        if ($id_mysql_server === null) {
            return null; // rien à assigner
        }

        $db = Sgbd::sql(DB_DEFAULT);

        // On met à jour la table ssh_tunnel
        $where = "local_port = $port AND local_host = '".$db->sql_real_escape_string($ip)."'";
        if ($pid !== null) {
            $where .= " AND pid = ".intval($pid);
        }

        $sql = "UPDATE ssh_tunnel 
                SET id_mysql_server = ".intval($id_mysql_server)."
                WHERE $where
                AND (id_mysql_server IS NULL OR id_mysql_server != ".intval($id_mysql_server).")";

        $db->sql_query($sql);
        return $id_mysql_server;
    }


/**
 * Handle `syncMysqlServerForTunnels`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for syncMysqlServerForTunnels.
 * @phpstan-return void
 * @psalm-return void
 * @example syncMysqlServerForTunnels(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public static function syncMysqlServerForTunnels($param): void
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        // Récupérer tous les tunnels sans id_mysql_server
        $sql = "SELECT id, local_host, local_port, pid, remote_host, remote_port 
                FROM ssh_tunnel 
                WHERE id_mysql_server IS NULL";
        $res = $db->sql_query($sql);

        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $ip   = $row['local_host'];
            $port = (int)$row['local_port'];
            $pid  = isset($row['pid']) ? (int)$row['pid'] : null;


            // Appelle la fonction assignMysqlServerToTunnel
            $id_mysql_server = self::assignMysqlServerToTunnel([$ip, $port, $pid]);

            Debug::debug([$ip, $port, $pid], "TEST");

            if ($id_mysql_server !== null) {

                // add alias pour le mapping maxscale & co
                Alias::upsertAliasDns([$row['remote_host'], $row['remote_port'], $id_mysql_server]);

                //echo "Tunnel ID {$row['id']} updated with MySQL server ID $id_mysql_server\n";

                
            } else {
                //echo "Tunnel ID {$row['id']} has no matching MySQL server\n";
            }
        }
    }


    //only for maxscale 
/**
 * Retrieve `getFinalRemoteByLocal`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array $param Route parameters forwarded by the router.
 * @phpstan-param array $param
 * @psalm-param array $param
 * @return ?string Returned value for getFinalRemoteByLocal.
 * @phpstan-return ?string
 * @psalm-return ?string
 * @example getFinalRemoteByLocal(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public static function getFinalRemoteByLocal(array $param): ?string
    {
        Debug::parseDebug($param);

        $local_ip = $param[0] ?? null;
        $local_port = $param[1] ?? null;

        if (empty($local_ip) || empty($local_port)) {
            return null;
        }

        $key = "$local_ip:$local_port";

        // Rafraîchit le cache si expiré
        if (time() > self::$tunnel_cache_expire) {
            self::preloadTunnelCache();
        }

        if (!isset(self::$tunnel_cache[$key])) {
            return null;
        }

        $tunnel = self::$tunnel_cache[$key];

        // Si des rebonds existent, on prend le dernier
        $jump_hosts = $tunnel['jump_hosts'] ?? [];
        if (!empty($jump_hosts)) {
            $last_jump = end($jump_hosts);
            $jump_host = $last_jump['remote_host'] ?? ($last_jump['ip'] ?? null);
            $jump_port = $last_jump['remote_port'] ?? ($last_jump['port'] ?? null);
            if (!empty($jump_host) && !empty($jump_port)) {
                return $jump_host . ':' . $jump_port;
            }
        }

        Debug::debug($tunnel['remote_host'] . ':' . $tunnel['remote_port'], "FINAL HOST");
        // Sinon, on retourne le remote du tunnel principal
        return $tunnel['remote_host'] . ':' . $tunnel['remote_port'];
    }

    /**
     * Charge le cache des tunnels actifs
     */
    private static function preloadTunnelCache(): void
    {
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM ssh_tunnel WHERE date_end IS NULL";
        $res = $db->sql_query($sql);

        self::$tunnel_cache = [];

        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $key = $row['local_host'] . ':' . $row['local_port'];
            $row['jump_hosts'] = json_decode($row['servers_jump'], true) ?: [];
            self::$tunnel_cache[$key] = $row;
        }

        self::$tunnel_cache_expire = time() + 60; // expire dans 1 min
    }

    /**
     * Vide le cache manuellement
     */
    public static function clearTunnelCache(): void
    {
        self::$tunnel_cache = [];
        self::$tunnel_cache_expire = 0;
    }



/**
 * Retrieve `getTunnelsMapping`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array $param Route parameters forwarded by the router.
 * @phpstan-param array $param
 * @psalm-param array $param
 * @return array Returned value for getTunnelsMapping.
 * @phpstan-return array
 * @psalm-return array
 * @example getTunnelsMapping(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public static function getTunnelsMapping(array $param = []): array
    {
        Debug::parseDebug($param);

        $date_request = $param[0] ?? null;


        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT local_host, local_port, remote_host, remote_port FROM ssh_tunnel";

        if (!empty($date_request)) {
            $escaped_date = $db->sql_real_escape_string($date_request);
            $sql .= " FOR SYSTEM_TIME AS OF TIMESTAMP '{$escaped_date}'";
            $sql .= " WHERE 
            '{$escaped_date}' BETWEEN row_start AND row_end";
        }

        Debug::sql($sql);
        $res = $db->sql_query($sql);

        $mapping = [];

        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $local = $row['local_host'] . ':' . $row['local_port'];
            $remote = $row['remote_host'] . ':' . $row['remote_port'];
            $mapping[$local] = $remote;
        }

        Debug::debug($mapping, "MAPPING TUNNEL");

        return $mapping;
    }


/**
 * Handle `syncServersForTunnelsGeneric`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array $param Route parameters forwarded by the router.
 * @phpstan-param array $param
 * @psalm-param array $param
 * @return void Returned value for syncServersForTunnelsGeneric.
 * @phpstan-return void
 * @psalm-return void
 * @example syncServersForTunnelsGeneric(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public static function syncServersForTunnelsGeneric(array $param = []): void
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);
        $now = date('Y-m-d H:i:s');

        // === Table de configuration générique ===
        $mappings = [
            [
                'name' => 'mysql',
                'table' => 'mysql_server',
                'fk' => 'id_mysql_server',
                'match' => 'local', // correspondance sur local_host:local_port
                'fields' => ['ip', 'port'],
                'post_action' => function ($row, $id_server) {
                    // ajout alias DNS pour MySQL
                    Alias::upsertAliasDns([$row['remote_host'], $row['remote_port'], $id_server]);
                }
            ],
            [
                'name' => 'maxscale',
                'table' => 'maxscale_server',
                'fk' => 'id_maxscale_server',
                'match' => 'remote', // correspondance sur remote_host:remote_port
                'fields' => ['hostname', 'port']
            ],
            [
                'name' => 'proxysql',
                'table' => 'proxysql_server',
                'fk' => 'id_proxysql_server',
                'match' => 'remote', // correspondance sur remote_host:remote_port
                'fields' => ['hostname', 'port']
            ]
        ];

        // === Sélection des tunnels à traiter ===
        $sql = "SELECT id, local_host, local_port, remote_host, remote_port, pid
                FROM ssh_tunnel
                WHERE id_mysql_server IS NULL 
                AND id_maxscale_server IS NULL 
                AND id_proxysql_server IS NULL";
        $res = $db->sql_query($sql);

        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $pid = (int)($row['pid'] ?? 0);

            foreach ($mappings as $map) {
                $fk = $map['fk'];

                // on saute si déjà rempli
                if (!empty($row[$fk])) {
                    continue;
                }

                // détermine ip:port selon le mode de correspondance
                if ($map['match'] === 'local') {
                    $ip = $row['local_host'];
                    $port = (int)$row['local_port'];
                } else {
                    $ip = $row['remote_host'];
                    $port = (int)$row['remote_port'];
                }

                $sqlFind = sprintf(
                    "SELECT id FROM %s WHERE CONCAT(%s, ':', %s) = '%s'",
                    $map['table'],
                    $map['fields'][0],
                    $map['fields'][1],
                    $db->sql_real_escape_string("$ip:$port")
                );

                $resFind = $db->sql_query($sqlFind);
                $found = $db->sql_fetch_array($resFind, MYSQLI_ASSOC);
                if (empty($found['id'])) {
                    continue;
                }

                $id_server = (int)$found['id'];
                $sqlUpdate = sprintf(
                    "UPDATE ssh_tunnel SET %s = %d WHERE id = %d AND (%s IS NULL OR %s != %d)",
                    $fk,
                    $id_server,
                    (int)$row['id'],
                    $fk,
                    $fk,
                    $id_server
                );
                $db->sql_query($sqlUpdate);

                // éventuelle action additionnelle (DNS pour MySQL)
                if (isset($map['post_action']) && is_callable($map['post_action'])) {
                    $map['post_action']($row, $id_server);
                }

                echo sprintf(
                    "✅ Tunnel %d linked to %s server ID %d via %s:%d\n",
                    $row['id'],
                    strtoupper($map['name']),
                    $id_server,
                    $ip,
                    $port
                );
            }
        }
    }

}
