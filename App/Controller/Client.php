<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\I18n\I18n;
use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;

class Client extends Controller
{

    public function index()
    {
        $this->title  = '<span class="glyphicon glyphicon glyphicon-user"></span> '.__("Clients");
        $this->ariane = ' > <a href⁼"'.LINK.'">'.'<span class="glyphicon glyphicon glyphicon-cog" style="font-size:12px">'
            .'</span> '.__("Settings").'</a> >'.$this->title;

        $this->di['js']->addJavascript(array('bootstrap-editable.min.js', 'Tree/index.js', 'Client/index.js'));

        $db             = Sgbd::sql(DB_DEFAULT);
        $sql            = "SELECT c.*, 
                COALESCE(stats.total_servers, 0) AS total_servers,
                COALESCE(stats.online_servers, 0) AS online_servers,
                COALESCE(stats.offline_servers, 0) AS offline_servers,
                COALESCE(stats.non_monitored_servers, 0) AS non_monitored_servers
            FROM client c
            LEFT JOIN (
                SELECT ms.id_client,
                    COUNT(*) AS total_servers,
                    SUM(
                        CASE
                            WHEN (CASE WHEN c2.is_monitored = 0 THEN 0 ELSE ms.is_monitored END) = 1
                                 AND COALESCE(avail.mysql_available, 0) = 1 THEN 1
                            ELSE 0
                        END
                    ) AS online_servers,
                    SUM(
                        CASE
                            WHEN (CASE WHEN c2.is_monitored = 0 THEN 0 ELSE ms.is_monitored END) = 1
                                 AND COALESCE(avail.mysql_available, 0) <> 1 THEN 1
                            ELSE 0
                        END
                    ) AS offline_servers,
                    SUM(
                        CASE
                            WHEN (CASE WHEN c2.is_monitored = 0 THEN 0 ELSE ms.is_monitored END) = 0 THEN 1
                            ELSE 0
                        END
                    ) AS non_monitored_servers
                FROM mysql_server ms
                INNER JOIN client c2 ON c2.id = ms.id_client
                LEFT JOIN (
                    SELECT tmd.id_mysql_server,
                        tvi.value AS mysql_available
                    FROM ts_variable tv
                    INNER JOIN ts_max_date tmd ON tmd.id_ts_file = tv.id_ts_file
                    LEFT JOIN ts_value_general_int tvi
                        ON tvi.id_mysql_server = tmd.id_mysql_server
                        AND tvi.id_ts_variable = tv.id
                        AND tvi.date = tmd.date
                    WHERE tv.name = 'mysql_available'
                        AND tv.`from` = 'mysql_server'
                ) avail ON avail.id_mysql_server = ms.id
                GROUP BY ms.id_client
            ) stats ON stats.id_client = c.id
            ORDER BY c.libelle";
        $data['client'] = $db->sql_fetch_yield($sql);

        $this->set('data', $data);
    }

    public function add()
    {
        $this->title  = '<span class="glyphicon glyphicon glyphicon-plus"></span> '.__("Add a new client");
        $this->ariane = ' > <a href⁼"'.LINK.'">'.'<span class="glyphicon glyphicon glyphicon-cog" style="font-size:12px">'
            .'</span> '.__("Settings").'</a> >'.
            '<span class="glyphicon glyphicon glyphicon-user"></span> '.__("Clients").' > '
            .$this->title;

        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            if (!empty($_POST['client']['libelle'])) {
                $db = Sgbd::sql(DB_DEFAULT);

                $client                      = [];
                $client['client']['libelle'] = $_POST['client']['libelle'];
                $client['client']['date']    = date('Y-m-d H:i:s');

                $res = $db->sql_save($client);

                if (!$res) {
                    debug($client);
                    debug($db->sql_error());
                    //die();

                    $msg   = I18n::getTranslation(__("Impossible to find the daemon with the id : ")."'".$id_daemon."'");
                    $title = I18n::getTranslation(__("Error"));
                    set_flash("error", $title, $msg);
                    header("location: ".LINK."client/add");

                    exit;
                } else {
                    $msg   = I18n::getTranslation(__("Client add"));
                    $title = I18n::getTranslation(__("Success"));
                    set_flash("success", $title, $msg);
                    header("location: ".LINK.'client/index');
                }
            }
        }
    }

    public function update($param)
    {
        $this->view        = false;
        $this->layout_name = false;

        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $db = Sgbd::sql(DB_DEFAULT);

            $sql = "UPDATE client SET `".$_POST['name']."` = '".$_POST['value']."' WHERE id = ".$db->sql_real_escape_string($_POST['pk'])."";
            $db->sql_query($sql);

            if ($db->sql_affected_rows() === 1) {
                echo "OK";
            } else {
                header("HTTP/1.0 503 Internal Server Error");
            }
        }
    }

    public function toggleMonitoring($param)
    {

        $this->view        = false;
        $this->layout_name = false;

        if ($param[1] === "true") {
            $result = 1;
        } else {
            $result = 0;
        }


        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "UPDATE client SET `is_monitored` = '".$result."' WHERE id = ".intval($param[0])."";
        $db->sql_query($sql);
    }
}