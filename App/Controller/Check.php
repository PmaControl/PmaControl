<?php

namespace App\Controller;


use \App\Library\Extraction2;
use \App\Library\Debug;
use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;

class Check extends Controller
{
    /**
     * Détecte un redémarrage MySQL via une baisse de uptime
     * Et insère un évènement dans event_log
     */
    public static function uptimeDecrease($servers = [])
    {
        $db = Sgbd::sql(DB_DEFAULT);

        // 1. Récupère les 5 dernières valeurs d'uptime
        $data = Extraction2::getLast5Value(["uptime"], $servers);

        foreach ($data as $id_mysql_server => $vars) {

            // pas de données uptime pour ce serveur
            if (!isset($vars["uptime"])) {
                continue;
            }

            // On récupère les valeurs dans l'ordre chronologique
            $values = array_reverse($vars["uptime"]);

            $prev = null;

            foreach ($values as $line) {

                $current = intval($line["value"]);
                $date    = $line["date"]; // datetime(6)

                if ($prev !== null && $current < $prev) {

                    // Détection : uptime redescend → restart
                    $message = "Uptime decreased from {$prev} to {$current} (possible MySQL restart).";

                    // Vérifie si un évènement identique existe déjà avec date_end IS NULL
                    $sql_check = "
                        SELECT id 
                        FROM event_log
                        WHERE id_mysql_server = {$id_mysql_server}
                        AND type = 'mysql.uptime.decrease'
                        AND date_end IS NULL
                        LIMIT 1;
                    ";

                    $res_check = $db->sql_query($sql_check);

                    // Pas d'incident ouvert → on en crée un nouveau
                    if ($db->sql_num_rows($res_check) == 0) {

                        $sql_insert = "
                            INSERT INTO event_log
                            SET
                                id_mysql_server = {$id_mysql_server},
                                type = 'mysql.uptime.decrease',
                                message = '" . $db->sql_real_escape_string($message) . "',
                                date_start = '" . $db->sql_real_escape_string($date) . "',
                                date_end = NULL;
                        ";

                        $db->sql_query($sql_insert);

                        Debug::debug([
                            "server" => $id_mysql_server,
                            "prev"   => $prev,
                            "curr"   => $current,
                            "date"   => $date,
                            "msg"    => "MySQL uptime decrease detected → event_log inserted"
                        ], "UPTIME-DECREASE");
                    }
                }

                $prev = $current;
            }
        }
    }
}