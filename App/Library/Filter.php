<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use \Glial\Sgbd\Sgbd;


trait Filter
{

    static private function getFilter($id_mysql_server = array(), $alias = 'a')
    {

        $where = "";
        if (!empty($_GET['environment']['libelle'])) {
            $environment = $_GET['environment']['libelle'];
        }
        if (!empty($_SESSION['environment']['libelle']) && empty($_GET['environment']['libelle'])) {
            $environment                    = $_SESSION['environment']['libelle'];
            $_GET['environment']['libelle'] = $environment;
        }

        if (!empty($_SESSION['client']['libelle'])) {
            $client = $_SESSION['client']['libelle'];
        }
        if (!empty($_GET['client']['libelle']) && empty($_GET['client']['libelle'])) {
            $client                    = $_GET['client']['libelle'];
            $_GET['client']['libelle'] = $client;
        }

        if (!empty($environment)) {
            $where .= " AND `".$alias."`.id_environment IN (".implode(',', json_decode($environment, true)).")";
        }
        if (!empty($client)) {
            $where .= " AND `".$alias."`.id_client IN (".implode(',', json_decode($client, true)).")";
        }

        if (! empty($id_mysql_server))
        {
            $where .= " AND `".$alias."`.id IN (".implode(',', $id_mysql_server).") ";
        }

        return $where;
    }

    

    public function getServer()
    {

        $db = Sgbd::sql(DB_DEFAULT);


        $sql = "SELECT a.*,d.libelle, d.class FROM mysql_server a
            INNER JOIN environment d on d.id = a.id_environment
            WHERE 1=1 ".self::getFilter();

        $res = $db->sql_query($sql);

        $server = array();
        while ($arr    = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $server[$arr['id']] = $arr;

            $server[$arr['id']]['link'] = '<span class="label label-'.$arr['class'].'">'
                .substr($arr['libelle'], 0, 1).'</span> '
                .' <a href="">'.$arr['display_name'].'</a>';
        }

        return $server;
    }




    private function generateServer($id_mysql_server)
    {

        $server = $this->servers[$id_mysql_server];

        $node = "";

        /*
          $databases = [];
          while ($ob        = $db->sql_fetch_object($res2)) {
          $databases[$ob->id_mysql_server][$ob->id_db]['name']             = $ob->name;
          $databases[$ob->id_mysql_server][$ob->id_db]['tables']           = $ob->tables;
          $databases[$ob->id_mysql_server][$ob->id_db]['rows']             = number_format($ob->rows, 0, '.', ' ');
          $databases[$ob->id_mysql_server][$ob->id_db]['size']             = $ob->data_length + $ob->data_free + $ob->index_length;
          $databases[$ob->id_mysql_server][$ob->id_db]['binlog_do_db']     = $ob->binlog_do_db;
          $databases[$ob->id_mysql_server][$ob->id_db]['binlog_ignore_db'] = $ob->binlog_ignore_db;
          }
         */

        $data['database'] = Extraction::display(array("databases::databases", "variables::is_proxysql", "master::binlog_do_db", "master::binlog_ignore_db", "replicate_do_db",
                "replicate_ignore_db"), array($id_mysql_server));

        Debug::debug($data);

        foreach ($data['database'] as $id_mysql_server => $elems) {
            foreach ($elems as $database) {

                Debug::debug($database);

                if (empty($database['binlog_do_db'])) {
                    $database['binlog_do_db'] = '';
                }


                if (empty($database['binlog_ignore_db'])) {
                    $database['binlog_ignore_db'] = '';
                }

                $binlog_do_db     = explode(",", $database['binlog_do_db']);
                $binlog_ignore_db = explode(",", $database['binlog_ignore_db']);

                if (!empty($database['databases'])) {
                    $dbs = json_decode($database['databases'], true);
                } else {
                    $dbs = array();
                }
                //Debug::debug($dbs);

                foreach ($dbs as $schema => $db_attr) {

                    $databases[$schema]['name'] = $schema;

                    $databases[$schema]['binlog_do_db']     = 0;
                    $databases[$schema]['binlog_ignore_db'] = 0;

                    $DB[$id_mysql_server][$schema]['M'] = '-';
                    if (in_array($schema, $binlog_do_db)) {

                        $databases[$schema]['binlog_do_db'] = 1;
                        $DB[$id_mysql_server][$schema]['M'] = 'V';
                    }

                    if (in_array($schema, $binlog_ignore_db)) {

                        $databases[$schema]['binlog_ignore_db'] = 1;
                        $DB[$id_mysql_server][$schema]['M']     = 'X';
                    }


                    /*
                      foreach ($db_attr['engine'] as $engine => $row_formats) {
                      foreach ($row_formats as $row_format => $details) {




                      $total_data[]  = $details['size_data'];
                      $total_index[] = $details['size_index'];
                      $total_free[]  = $details['size_free'];
                      $total_table[] = $details['tables'];
                      $total_row[]   = $details['rows'];
                      }
                      } */
                }
            }
        }

        Debug::debug($databases, "database");

        $node .= 'node [color = "'.$this->graph_node[$id_mysql_server]['color'].'" style="'.$this->graph_node[$id_mysql_server]['style'].'"];'."\n";
        $node .= '  '.$id_mysql_server.' [penwidth="3" fontname="arial" label =<<table border="0" cellborder="0" cellspacing="0" cellpadding="2" bgcolor="white">';

        //debug($server);

        $node .= $this->nodeHead($server['hostname'], $id_mysql_server);

        $lines[] = "IP : ".$server['ip'].":".$server['port'];
        $lines[] = "Date : ".$server['date'];
        $lines[] = "Time zone : ".$server['system_time_zone'];

        $lines[] = "Binlog : ".$server['binlog_format'];
        $lines[] = $this->formatVersion($server['version']);
        $lines[] = "Server-id : ".$server['server_id'];
        $lines[] = "Auto_inc : (".$server['auto_increment_offset']." / ".$server['auto_increment_increment'].")";
        
        /* TODO replace with good filter
        if (!empty($server['wsrep_local_state_comment'])) {

            if ($server['is_available'] == "1" || $server['is_available'] == "-1") {
                $lines[] = "Galera status : ".$server['wsrep_local_state_comment'];
            } else {
                $lines[] = "Galera status : ".__("Out of order");
            }
        }*/


        foreach ($lines as $line) {
            $node .= $this->nodeLine($line);
        }


        if (!empty($databases)) {
            $node .= '<tr><td bgcolor="lightgrey"><table border="0" cellborder="0" cellspacing="0" cellpadding="2">';

            $node .= '<tr>'
                .'<td bgcolor="#A9A9A9" color="white" align="left">M</td>'
                .'<td bgcolor="#A9A9A9" color="white" align="left">S</td>'
                .'<td bgcolor="#A9A9A9" color="white" align="left">'.__("Databases").'</td>'
                //     .'<td bgcolor="#A9A9A9" color="white" align="right">'.__("Tables").'</td>'
                //     .'<td bgcolor="#A9A9A9" color="white" align="right">'.__("Row").'</td>'
                .'</tr>';

            foreach ($databases as $database) {


                Debug::debug($database, '##########');
                $node .= '<tr>'
                    .'<td bgcolor="#A9A9A9" color="white" align="left">';

                if ($database['binlog_do_db'] == "1") {

                    Debug::debug($database['binlog_do_db'], "###########################################");
                    $node .= "&#10006;";
                } else if (!empty($database['binlog_ignore_db']) && $database['binlog_ignore_db'] == "1") {
                    $node .= "&#10004;";
                } else {
                    $node .= "-";
                }

                $node .= '</td>';
                $node .= '<td bgcolor="#A9A9A9" color="white" align="left">';

                if ($database['binlog_do_db'] == "1") {
                    //$node .= "&#10006;";
                }

                $node .= '</td>'
                    .'<td bgcolor="#A9A9A9" color="white" align="left">'.$database['name'].'</td>'
                    //          .'<td bgcolor="#A9A9A9" color="white" align="right">'.$database['tables'].'</td>'
                    //          .'<td bgcolor="#A9A9A9" color="white" align="right">'.$database['rows'].'</td>'
                    .'</tr>';
            }
            $node .= '</table></td></tr>';
        }

        $node .= "</table>> ];\n";

        return $node;
    }
}