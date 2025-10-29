<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use App\Library\EngineV4;
use \Glial\Synapse\Controller;
use \App\Library\Debug;
use \App\Library\Microsecond;
use \Glial\I18n\I18n;
use \Glial\Sgbd\Sgbd;
use \App\Library\Extraction2;

class Dns extends Controller
{
    public function check($param)
    {
        Debug::parseDebug($param);

        $default = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT id, name, port, last_resolved_ip, id_mysql_server
                FROM infra_vip_dns
                WHERE type='DNS'";
        $res = $default->sql_query($sql);

        while ($ob = $default->sql_fetch_object($res))
        {
            $id_infra = $ob->id;
            $dns_name = trim($ob->name);
            $dns_port = (int)$ob->port;
            $last_resolved_ip = trim($ob->last_resolved_ip);
            $id_mysql_server = $ob->id_mysql_server;

            // --- Résolution DNS ---
            $resolved_ip = gethostbyname($dns_name);

            if ($resolved_ip === $dns_name)
            {
                echo date('Y-m-d H:i:s')." [WARN] Impossible de résoudre {$dns_name}\n";

                $sql_update = "
                    UPDATE infra_vip_dns
                    SET status='ERROR', last_checked=NOW()
                    WHERE id={$id_infra}
                ";
                $default->sql_query($sql_update);
                continue;
            }

            // --- Si changement d’adresse IP ---
            if ($resolved_ip !== $last_resolved_ip)
            {
                echo date('Y-m-d H:i:s')." [INFO] {$dns_name}: IP changée {$last_resolved_ip} → {$resolved_ip}\n";

                $sql_update = "
                    UPDATE infra_vip_dns
                    SET last_resolved_ip='".$resolved_ip."',
                        last_checked=NOW(),
                        status='CHANGED'
                    WHERE id={$id_infra}
                ";
                $default->sql_query($sql_update);
            }
            else
            {
                // Pas de changement
                $sql_update = "
                    UPDATE infra_vip_dns
                    SET last_checked=NOW(),
                        status='OK'
                    WHERE id={$id_infra}
                ";
                $default->sql_query($sql_update);
            }

            // --- Vérification du rattachement MySQL Server ---
            $sql_find = "
                SELECT id FROM mysql_server
                WHERE ip='".$resolved_ip."' AND port=".$dns_port."
                LIMIT 1
            ";
            $res_find = $default->sql_query($sql_find);

            if ($default->sql_num_rows($res_find) > 0)
            {
                $ob_server = $default->sql_fetch_object($res_find);
                $found_id = $ob_server->id;

                if ($id_mysql_server != $found_id)
                {
                    echo date('Y-m-d H:i:s')." [LINK] {$dns_name}: associé au serveur MySQL #{$found_id} (".$resolved_ip.":".$dns_port.")\n";

                    $sql_link = "
                        UPDATE infra_vip_dns
                        SET id_mysql_server=".$found_id.",
                            updated_at=NOW()
                        WHERE id=".$id_infra;
                    $default->sql_query($sql_link);
                }
            }
            else
            {
                // Aucun serveur trouvé avec cette IP/port
                if (!empty($id_mysql_server))
                {
                    echo date('Y-m-d H:i:s')." [UNLINK] {$dns_name}: aucun serveur trouvé pour ".$resolved_ip.":".$dns_port." → id_mysql_server remis à NULL\n";

                    $sql_unlink = "
                        UPDATE infra_vip_dns
                        SET id_mysql_server=NULL,
                            updated_at=NOW()
                        WHERE id=".$id_infra;
                    $default->sql_query($sql_unlink);
                }
            }
        }

        $default->sql_close();

    }



}