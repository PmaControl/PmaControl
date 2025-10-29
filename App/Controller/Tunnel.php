<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\Tree as TreeInterval;
use App\Library\Debug;
use \Glial\Sgbd\Sgbd;

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

    public static function agent(array $param = []): void
    {
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

        // 3. Ajouter les tunnels actifs non présents
        foreach ($activeTunnels as $pid => $tunnel) {
            if (!isset($existingTunnels[$pid])) {
                $servers_jump = json_encode($tunnel['jump_hosts'], JSON_UNESCAPED_UNICODE);

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

    public static function parse(array $param)
    {
        Debug::parseDebug($param);

        $line = $param[0];
        $parts = preg_split('/\s+/', trim($line));

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

        if ($type === 'L' && preg_match('/-L\s*(\d+):([^:]+):(\d+)/', $command, $m)) {
            $local_port  = (int)$m[1];
            $possible_host = $m[2];
            $remote_port = (int)$m[3];
            $local_host  = '127.0.0.1'; // par défaut, on écoute localement

            // Si l'hôte indiqué dans -L n'est pas 127.0.0.1, c'est notre vraie cible
            if ($possible_host !== '127.0.0.1') {
                $remote_host = $possible_host;
            }
        } elseif ($type === 'R' && preg_match('/-R\s*(\d+):([^:]+):(\d+)/', $command, $m)) {
            $remote_port = (int)$m[1];
            $remote_host = $m[2];
            $local_port  = (int)$m[3];
        } elseif ($type === 'D' && preg_match('/-D\s*(\d+)/', $command, $m)) {
            $local_port = (int)$m[1];
            $local_host = '127.0.0.1';
        } else {
            return null;
        }

        // Extraction des rebonds (-J)
        $jump_hosts = [];
        if (preg_match('/-J\s+([^\s]+)/', $command, $jm)) {
            $jump_parts = explode(',', $jm[1]);
            foreach ($jump_parts as $j) {
                if (preg_match('/@([\d\.]+)(?::(\d+))?/', $j, $m2)) {
                    $jump_hosts[] = [
                        'ip'   => $m2[1],
                        'port' => isset($m2[2]) ? (int)$m2[2] : 22
                    ];
                }
            }
        }

        // Extraction de toutes les IP pour déterminer la "vraie" cible s’il faut
        preg_match_all('/(\d{1,3}\.){3}\d{1,3}/', $command, $ips);
        $all_ips = array_values(array_unique($ips[0]));

        $last_ip = !empty($all_ips) ? end($all_ips) : null;

        // Règle principale :
        // Si aucune IP valide avant le port distant OU si elle est 127.0.0.1 → on prend la dernière IP (la cible réelle)
        if (empty($remote_host) || $remote_host === '127.0.0.1') {
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

    public function index($param)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        // Récupérer tous les tunnels
        $sql = "SELECT t.*, m.display_name AS mysql_display_name
                FROM ssh_tunnel t
                LEFT JOIN mysql_server m ON t.id_mysql_server = m.id
                ORDER BY t.date_created DESC";
        $res = $db->sql_query($sql);

        $tunnels = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $row['servers_jump'] = json_decode($row['servers_jump'], true) ?: [];
            $tunnels[] = $row;
        }

        // Récupérer tous les mysql_server pour le select
        $sql2 = "SELECT id, display_name FROM mysql_server ORDER BY display_name";
        $res2 = $db->sql_query($sql2);
        $servers = [];
        while ($row = $db->sql_fetch_array($res2, MYSQLI_ASSOC)) {
            $servers[$row['id']] = $row['display_name'];
        }

        // Préparer le data array pour la vue
        $data = [];
        foreach ($tunnels as $t) {
            $data[] = [
                'id'              => $t['id'],
                'local'           => $t['local_host'] . ':' . $t['local_port'],
                'remote'          => $t['remote_host'] . ':' . $t['remote_port'],
                'mysql_display'   => $t['mysql_display_name'] ?: null,
                'id_mysql_server' => $t['id_mysql_server'] ?: null,
                'servers_jump'    => $t['servers_jump'],
                'command'         => $t['command'],
                'date_created'    => $t['date_created'],
                'date_end'        => $t['date_end'] === '0000-00-00 00:00:00' ? null : $t['date_end']
            ];
        }

        // Envoyer à la vue
        $this->set('data', $data);
        $this->set('servers', $servers); // pour les select dans la vue
    }


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

                echo "Tunnel ID {$row['id']} updated with MySQL server ID $id_mysql_server\n";

                
            } else {
                echo "Tunnel ID {$row['id']} has no matching MySQL server\n";
            }
        }
    }


    //only for maxscale 
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
            if (!empty($last_jump['remote_host']) && !empty($last_jump['remote_port'])) {
                return $last_jump['remote_host'] . ':' . $last_jump['remote_port'];
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




}