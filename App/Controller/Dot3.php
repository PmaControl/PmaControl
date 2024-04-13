<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\Extraction2;
use App\Library\Extraction;
use App\Library\Mysql;
use \App\Library\Debug;

use \Glial\Sgbd\Sgbd;

// ""	&#9635;   ▣
// "□"	&#9633;	&#x25A1;
// ""	&#9679;  ●
//"○"	&#9675;
//"◇"	&#9671;	&#x25C7;
//"◈"	&#9672;	&#x25C8;
// Joining: receiving State Transfer   => IST change color
//add virtual_ip
// ha proxy
// https://renenyffenegger.ch/notes/tools/Graphviz/examples/index  <= to check for GTID (nice idea)
class Dot3 extends Controller
{

    /*
    * récupére toutes les infomations du serveur à un date t   
    */
    use \App\Library\Filter;
    use \App\Library\Dot;

    public function getInformation($param)
    {
	    Debug::parseDebug($param);

        $date_request = $param[0] ?? "";
        $versioning = "";
        $versioning2 = "";
        if ( ! empty($date_request))
        {
            $versioning = " WHERE '".$date_request."' between a.row_start and a.row_end ";
            $versioning2 = " WHERE '".$date_request."' between b.row_start and b.row_end AND '".$date_request."' between c.row_start and c.row_end ";
            $date_request = array($date_request);
        }

        //Debug::debug($date_request, "Date");

        $db  = Sgbd::sql(DB_DEFAULT);
        $all = Extraction2::display(array("variables::hostname", "variables::binlog_format", "variables::time_zone", "variables::version",
                "variables::system_time_zone", "variables::wsrep_desync", "variables::port", "variables::is_proxysql", "variables::wsrep_cluster_address",
                "variables::wsrep_cluster_name", "variables::wsrep_provider_options", "variables::wsrep_on", "variables::wsrep_sst_method",
                "variables::wsrep_desync", "status::wsrep_cluster_status", "status::wsrep_local_state", "status::wsrep_local_state_comment",
                "status::wsrep_incoming_addresses", "variables::wsrep_patch_version","mysql_server::available", "mysql_server::ping", "mysql_server::error",
                "status::wsrep_cluster_size", "status::wsrep_cluster_state_uuid", "status::wsrep_gcomm_uuid", "status::wsrep_local_state_uuid",
                "slave::master_host", "slave::master_port", "slave::seconds_behind_master", "slave::slave_io_running",
                "slave::slave_sql_running", "slave::replicate_do_db", "slave::replicate_ignore_db", "slave::last_io_errno", "slave::last_io_error",
                "slave::last_sql_error", "slave::last_sql_errno", "slave::using_gtid", "variables::is_proxysql"),array() , $date_request);

        $sql = "SELECT id as id_mysql_server, ip, port, display_name  
                FROM mysql_server a ".$versioning."
                UNION select b.id_mysql_server, b.dns as ip, b.port, c.display_name  
                from alias_dns b INNER JOIN mysql_server c ON b.id_mysql_server =c.id
                ".$versioning2.";";

        //Debug::sql($sql);

        $res = $db->sql_query($sql);

        $server_mysql = array();
        //$mapping_master = array();

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $server_mysql[$arr['id_mysql_server']] = $arr;

            //TODO add alias_dns and virtual_ip
            $data['mapping'][$arr['ip'].':'.$arr['port']] = $arr['id_mysql_server'];
        }

        $data['servers'] = array_replace_recursive($all, $server_mysql);

        //echo json_encode($data, JSON_PRETTY_PRINT);

        //$proxy = Extraction2::display(array("proxysql_main_var::mysql-interfaces", "proxysql_main_var::admin-web_port", "proxysql_main_var::admin-version"));
        //Debug::debug($proxy, "proxysql");

	    Debug::debug($data, "DATA");
        

        //insert to DB
        $sql = "INSERT INTO";


        $dot3 = array();
        $dot3['dot3']['date_generated'] = date('Y-m-d H:i:s');
        $dot3['dot3']['information'] = json_encode($data);

        $db->sql_save($dot3);


        return $data;
    }


    public function generateGroupMasterSlave($information)
    {
        $id_group = 1;
        $tmp_group = array();

        foreach($information['servers'] as $server)
        {
            Debug::debug($server, "SERVER");

            if (empty($server['@slave']))
            {
                continue;
            }

            foreach($server['@slave']  as $slave)
            {
                $tmp_group[$id_group] = array();
                $tmp_group[$id_group][] = $server['id_mysql_server'];
                $master = $slave['master_host'].":" .$slave['master_port'];

                if (! empty($information['mapping'][$master]))
                {
                    $tmp_group[$id_group][] = $information['mapping'][$master];
                }
                else 
                {
                    echo "This master was not found : ".$master."\n";
                }
                
                $id_group++;
            }   
        }
        return $tmp_group;
    }

    public function run($param)
    {
        Debug::parseDebug($param);

        $information = $this->getInformation($param);
 
        $master_slave = $this->generateGroupMasterSlave($information);

        $group = $this->array_merge_group($master_slave);

        Debug::debug($master_slave);
        Debug::debug($group, "GROUP");
    }

    private function array_merge_group($array)
    {
        $all_values  = $this->array_values_recursive($array);
        $group_merge = [];
        foreach ($all_values as $value) {
            $tmp = [];
            foreach ($array as $key => $sub_group) {
                if (in_array($value, $sub_group)) {
                    $tmp = array_merge($sub_group, $tmp);
                    unset($array[$key]);
                }
            }
            $array[] = array_unique($tmp);
        }
        //@TODO : Improvement because we parse all_value and we delete all array from orgin no need to continue;
        return $array;
    }

    private function array_values_recursive($ary)
    {
        $lst = array();
        foreach (array_keys($ary) as $k) {
            $v = $ary[$k];
            if (is_scalar($v)) {
                $lst[] = $v;
            } elseif (is_array($v)) {
                $lst = array_merge($lst, $this->array_values_recursive($v)
                );
            }
        }
        return $lst;
    }



    /*
    Legend pour les topologies
    */



    public function legend()
    {
        $sql = "SELECT * FROM `architecture_legend` WHERE `type`= 'REPLICATION' order by `order`;";
        $db  = Sgbd::sql(DB_DEFAULT);
        $res = $db->sql_query($sql);

        $edges = array();
        while ($arr   = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $edges[] = $arr;
        }

        $legend = 'digraph {
	    rankdir=LR
	    graph [fontname = "helvetica"];
	    node [fontname = "helvetica"];
	    edge [fontname = "helvetica"];
	    node [shape=plaintext fontsize=12];
	    subgraph cluster_01 {
	    label = "Replication : Legend";
	    key [label=<<table border="0" cellpadding="2" cellspacing="0" cellborder="0">';

        $i = 1;
        foreach ($edges as $edge) {
            $legend .= '<tr><td align="right" port="i'.$i.'">'.$edge['name'].'&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>'."\n";
            $i++;
        }

        // GTID
        $legend .= '<tr><td align="right" port="j'.$i.'"> '." ".' </td></tr>'."\n";
        $legend .= '<tr><td align="right" port="i'.$i.'"> '."GTID".' </td></tr>'."\n";
        $i++;
        $legend .= '<tr><td align="right" port="i'.$i.'"> '."Standard".' </td></tr>'."\n";
        $legend .= '</table>>]
		    key2 [label=<<table border="0" cellpadding="2" cellspacing="0" cellborder="0">'."\n";

        $i = 1;
        foreach ($edges as $edge) {
            $legend .= '<tr><td port="i'.$i.'">&nbsp;</td></tr>'."\n";
            $i++;
        }

        $legend .= '<tr><td port="j'.$i.'">&nbsp;</td></tr>'."\n";
        $legend .= '<tr><td port="i'.$i.'">&nbsp;</td></tr>'."\n";
        $i++;
        $legend .= '<tr><td port="i'.$i.'">&nbsp;</td></tr>'."\n";
        $legend .= '</table>>]'."\n";

        $i = 1;
        foreach ($edges as $edge) {
            $legend .= 'key:i'.$i.':e -> key2:i'.$i.':w [color="'.$edge['color'].'" arrowsize="1.5" style='.$edge['style'].',penwidth="2"]'."\n";
            $i++;
        }

        $edge['color'] = "#000000";

        $legend .= 'key:i'.$i.':e -> key2:i'.$i.':w [color="'.$edge['color'].':#ffffff:'.$edge['color'].'" arrowsize="1.5" style='.$edge['style'].',penwidth="2"]'."\n";
        $i++;
        $legend .= 'key:i'.$i.':e -> key2:i'.$i.':w [color="'.$edge['color'].'" arrowsize="1.5" style='.$edge['style'].',penwidth="2"]'."\n";
        $i++;

        /*
          key:i1:e -> key2:i1:w [color=blue]
          key:i2:e -> key2:i2:w [color=gray]
          key:i3:e -> key2:i3:w [color=peachpuff3]
          key:i4:e -> key2:i4:w [color=turquoise4, style=dotted]
         */
        $legend .= '
  }
}';

        //echo str_replace("\n", "<br />",htmlentities($legend));

        file_put_contents(TMP."/legend", $legend);

        $data['legend'] = $this->getRenderer($legend);

        $this->set('data', $data);

        //https://dreampuf.github.io/GraphvizOnline/
    }



    public function getRenderer($dot)
    {
        $file_id = uniqid();

        $tmp_in  = "/tmp/dot_".$file_id.".dot";
        $tmp_out = "/tmp/svg_".$file_id.".svg";

        file_put_contents($tmp_in, $dot);

        $cmd = "dot ".$tmp_in." -Tsvg -o ".$tmp_out." 2>&1";

        $ret = shell_exec($cmd);

        if (!empty($ret)) {
            throw new \Exception('PMACTRL-842 : Dot2/getRenderer '.trim($ret), 70);
        }

        $svg = file_get_contents($tmp_out);

        unlink($tmp_in);
        unlink($tmp_out);

        return $svg;
    }
    public function generateAllGraph($groups, $information)
    {
        foreach ($groups as $group) {
            //debug($group);
            $code2D = $this->generateGraph($group);
            $view2D = $this->getRenderer($code2D);
            $this->saveCache($code2D, $view2D, $group);
        }
    }



    public function saveCache($code2D, $view2D, $group)
    {



        $servers = implode(",", $group);

//Debug::debug($servers);


        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "BEGIN";
        $db->sql_query($sql);

        $sql = "DELETE b FROM `link__architecture__mysql_server` a
        INNER JOIN `architecture` b ON b.id = a.id_architecture
        WHERE a.`id_mysql_server` IN (".$servers.");";
        $db->sql_query($sql);

        preg_match_all("/width=\"([0-9]+)pt\"\sheight=\"([0-9]+)pt\"/", $view2D, $output);

        $sql = "INSERT INTO `architecture` (`date`, `data`, `display`,`height`,`width`)
            VALUES ('".date('Y-m-d H:i:s')."','".$db->sql_real_escape_string($code2D)."','".$db->sql_real_escape_string($view2D)."',"
            .$output[2][0]." ,".$output[1][0].")";
        $res = $db->sql_query($sql);

        $sql = "SELECT max(id) as last FROM architecture;";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $id_architecture = $ob->last;
        }

        foreach ($group as $id_mysql_server) {

            if (!in_array($id_mysql_server, $this->graph_arbitrator)) {
                $sql = "INSERT INTO `link__architecture__mysql_server` (`id_architecture`, `id_mysql_server`) VALUES ('".$id_architecture."','".$id_mysql_server."');";
                $db->sql_query($sql);
            }
        }


        $sql = "COMMIT;";
        $db->sql_query($sql);
    }

}
