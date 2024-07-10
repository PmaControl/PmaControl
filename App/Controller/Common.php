<?php

namespace App\Controller;

use App\Library\Extraction;
use App\Library\Debug;
use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;

//use \Glial\Cli\Color;


class Common extends Controller
{

    use \App\Library\Filter;
    //list des tag pour eviter de faire la requete a chaque fois
    static $tags = array();

    //dba_source

    public function index()
    {
        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM mysql_database";
    }
    /*
      @author: Aurélien LEQUOY
      Obtenir la liste dans un select des server MySQL operationels
     */

    public function displayClientEnvironment($param)
    {

        $this->di['js']->addJavascript(array('bootstrap-select.min.js'));

        $db = Sgbd::sql(DB_DEFAULT);


        if ($_SERVER['REQUEST_METHOD'] == "POST") {


            if (!empty($_POST['client_environment'])) {
                $ret = "";
                if (!empty($_POST['client']['libelle']) || !empty($_POST['environment']['libelle'])) {

                    /* header("location: ".LINK."".\Glial\Synapse\FactoryController::$controller."/".\Glial\Synapse\FactoryController::$method."/client:libelle:"
                      .$_POST['client']['libelle']."/environment:libelle:".$_POST['environment']['libelle']); */


                    if (!empty($_POST['client']['libelle'])) {
                        $_SESSION['client']['libelle'] = json_encode($_POST['client']['libelle']);
                        $ret                           .= "/client:libelle:".json_encode($_POST['client']['libelle']);
                    } else {
                        unset($_SESSION['client']['libelle']);
                    }

                    if (!empty($_POST['environment']['libelle'])) {
                        $_SESSION['environment']['libelle'] = json_encode($_POST['environment']['libelle']);
                        $ret                                .= "/environment:libelle:".json_encode($_POST['environment']['libelle']);
                    } else {
                        unset($_SESSION['environment']['libelle']);
                    }
                } elseif (!empty($_POST['client_environment'])) {
                    unset($_SESSION['client']['libelle']);
                    unset($_SESSION['environment']['libelle']);
                }

                header("location: ".LINK."".$this->remove(array("client:libelle", "environment:libelle")).$ret);
            }
        }

        if (empty($_GET['environment']['libelle']) && !empty($_SESSION['environment']['libelle'])) {
            $_GET['environment']['libelle'] = $_SESSION['environment']['libelle'];
        }

        if (empty($_GET['client']['libelle']) && !empty($_SESSION['client']['libelle'])) {
            $_GET['client']['libelle'] = $_SESSION['client']['libelle'];
        }


        $sql = "SELECT * from client order by libelle";
        $res = $db->sql_query($sql);


        $data['client'] = array();

        /*
          $tmp = [];
          $tmp['id'] = "";
          $tmp['libelle'] = __("All");
          $data['environment'][] = $tmp;
         */

        while ($ob = $db->sql_fetch_object($res)) {
            $tmp            = [];
            $tmp['id']      = $ob->id;
            $tmp['libelle'] = $ob->libelle;

            $data['client'][] = $tmp;
        }

        $sql = "SELECT * from environment order by libelle";
        $res = $db->sql_query($sql);


        $data['environment'] = array();

        /*
          $tmp = [];
          $tmp['id'] = "";
          $tmp['libelle'] = __("All");
          $data['environment'][] = $tmp;
         */


        while ($ob = $db->sql_fetch_object($res)) {
            $tmp            = [];
            $tmp['id']      = $ob->id;
            $tmp['libelle'] = $ob->libelle;

            $data['environment'][] = $tmp;
        }

        $this->set('data', $data);
    }

    public function remove($array)
    {

        $params = explode("/", $_GET['url']);
        foreach ($params as $key => $param) {
            foreach ($array as $var) {
                if (strstr($param, $var)) {
                    unset($params[$key]);
                }
            }
        }

        $ret = implode('/', $params);

        $ret = trim($ret, "/");

        return $ret;
    }
    /*
     * @test : http://localhost/pmacontrol/en/common/getDatabaseByServer/35/ajax>true/
     *
     *
     */

    function getDatabaseByServer($param)
    {

        $this->di['js']->addJavascript(array('bootstrap-select.min.js', 'Common/getDatabaseByServer.js'));


        $data['ajax'] = false;
        if (IS_AJAX) {
            $this->layout_name = false;
            $data['ajax']      = true;
        }

        if (!empty($param[2]) && !empty($param[1]) && !empty($param[0])) {
            $data['table']   = $param[0];
            $data['field']   = $param[1];
            $id_mysql_server = $param[2];
        } else {
            $id_mysql_server = $param[0];
        }

        $options = array();
        if (!empty($param[3])) {

            $options = (array) $param[3];
        }

        $data['options'] = $options;

        //$data['width'] = $param[2] ?? "auto";
        //pour restreindre la liste des serveurs a ceux spécifier

        $mysql_server_specify = array();
        foreach ($data['options'] as $key => $val) {
            if ($key === "mysql_server_specify") {
                $mysql_server_specify = $val;

                unset($data['options'][$key]);
            }
        }

        if (!empty($id_mysql_server)) {
            $db_to_get_db = $this->getDbLinkFromId($id_mysql_server);

            $sql  = "SHOW DATABASES";
            $res2 = $db_to_get_db->sql_query($sql);

            $data['databases'] = [];
            while ($ob                = $db_to_get_db->sql_fetch_object($res2)) {
                $tmp                 = [];
                $tmp['id']           = $ob->Database;
                $tmp['libelle']      = $ob->Database;
                $data['databases'][] = $tmp;
            }
        } else {
            $data['databases'] = array();
        }

        //debug($data['databases']);

        $this->set("data", $data);
        return $data;
    }

    private function getDbLinkFromId($id_db)
    {
        if (IS_AJAX) {
            $this->layout_name = false;
        }

        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT id,name FROM mysql_server WHERE id = '".$db->sql_real_escape_string($id_db)."';";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $db_link = Sgbd::sql($ob->name);
        }

        if (empty($db_link)) {
            throw new \Exception('PMACTRL-478 : impossible to find DB link with mysql_server.id = "'.$id_db.'".', 478);
        }

        return $db_link;
    }

    function getTableByServerAndDatabase($param)
    {
        if (IS_AJAX) {
            $this->layout_name = false;
        }

        $id_mysql_server = $param[0];
        $database        = $param[1];

        $db_to_get_db = $this->getDbLinkFromId($id_mysql_server);

        $sql = "use ".$database.";";
        $db_to_get_db->sql_query($sql);

        $tables = $db_to_get_db->getListTable();


        $data['tables'] = [];
        foreach ($tables['table'] as $table) {
            $tmp              = [];
            $tmp['id']        = $table;
            $tmp['libelle']   = $table;
            $data['tables'][] = $tmp;
        }

        $this->set("data", $data);
        return $data;
    }
    /*
     *
     *
     * params
     * 1 => table_name
     * 2 => id
     * 3 => array (options for select)
     */

    public function getSelectServerAvailable($param = array())
    {
        if (!empty($param[0])) {
            $data['table'] = $param[0];
        } else {
            $data['table'] = "mysql_server";
        }

        if (!empty($param[1])) {
            $data['field'] = $param[1];
        } else {
            $data['field'] = "id";
        }

        $options = array();
        if (!empty($param[2])) {

            $options = (array) $param[2];
        }

        $data['list_server'] = array();

        $data['options'] = $options;

        //$data['width'] = $param[2] ?? "auto";
        //pour restreindre la liste des serveurs a ceux spécifier

        //debug($param);
        
        
        $all_selectable = false;
        foreach ($data['options'] as $key => $val) {
            if ($key === "all_selectable") {
                $all_selectable = true;

                unset($data['options'][$key]);
            }
        }



        /* check if need to remove */
        $mysql_server_specify = array();
        foreach ($data['options'] as $key => $val) {
            if ($key === "mysql_server_specify") {
                $mysql_server_specify = $val;

                unset($data['options'][$key]);
            }
        }

        $servers_not_available_disabled = true;
        foreach ($data['options'] as $key => $val) {
            if ($key === "all_server") {
                 $val =(bool) $val;
                 $servers_not_available_disabled = !$val;

                unset($data['options'][$key]);
            }
        }
        /** end to remove */

        
        

        $this->di['js']->addJavascript(array('bootstrap-select.min.js'));

        $db = Sgbd::sql(DB_DEFAULT);

        $available = self::getAvailable();

        $sql = "SELECT ".$available['case'].", a.id, a.display_name,a.ip,a.port, b.letter, b.class, b.libelle
            FROM mysql_server a
            INNER JOIN environment b ON a.id_environment = b.id
            WHERE 1 ".self::getFilter($mysql_server_specify)." ORDER by b.libelle,a.name";

        //debug($_GET);

        $res = $db->sql_query($sql);

        
        while ($ob                   = $db->sql_fetch_object($res)) {
            $tmp            = [];
            $tmp['id']      = $ob->id;
            $tmp['error']   = $ob->error;

            if ($servers_not_available_disabled)   {
                if ($ob->error !== "0"){
                    $tmp['disabled']   = "1";
                }
                else{
                    $tmp['disabled']   = "0";
                }
            }
            else{
                $tmp['disabled']   = "1";
            }
            
            $tmp['libelle'] = $ob->display_name." (".$ob->ip.")";

            //$tmp['extra'] = array("data-content" => "<span title='" . $ob->libelle . "' class='label label-" . $ob->class . "'>" . $ob->letter . "</span> " . $ob->display_name . " <small class='text-muted'>" . $ob->ip . "</small>");

            $pretty_server = str_replace('"', "'", \App\Library\Display::srv($ob->id));
            $remove_tag_a  = strip_tags($pretty_server, '<span><small>');
            $tmp['extra']  = array("data-content" => $remove_tag_a);

            if ($ob->error !== "0" && ! $all_selectable ) {
                //$tmp['extra']["disabled"] = "disabled";
            }

            $data['list_server'][] = $tmp;
        }

        $this->set('data', $data);

        return $data['list_server'];
    }

    function getTsVariables($param = array())
    {
        

        if (!empty($param[0])) {
            $data['table'] = $param[0];
        } else {
            $data['table'] = "ts_variable";
        }

        if (!empty($param[1])) {
            $data['field'] = $param[1];
        } else {
            $data['field'] = "id";
        }

        $options = array();
        if (!empty($param[2])) {

            $options = (array) $param[2];
        }

        $data['options'] = $options;


        $db = Sgbd::sql(DB_DEFAULT);




        $sql = "SELECT * FROM ts_variable order by `from`, `name`;";

        $res = $db->sql_query($sql);

        $data['variable'] = array();
        while ($ob               = $db->sql_fetch_object($res)) {
            $tmp            = [];
            $tmp['id']      = $ob->from.'::'.$ob->name;
            //$tmp['error']   = $ob->error;
            $tmp['libelle'] = $ob->from.'::'.$ob->name."";

            $tmp['extra'] = array("data-content" => "<small class='text-muted'>".$ob->from."</small> ".$ob->name);
            $data['variable'][] = $tmp;
        }

        $this->di['js']->addJavascript(array('bootstrap-select.min.js'));
        $this->set('data', $data);
    }



    public function getTsVariableJson($param)
    {
        $db = Sgbd::sql(DB_DEFAULT);
        $this->di['js']->addJavascript(array('bootstrap-select.min.js'));


        $data['options'] = array();

        if (!empty($param[0])) {
            $data['table'] = $param[0];
        } else {
            $data['table'] = "ts_variable";
        }

        if (!empty($param[1])) {
            $data['field'] = $param[1];
        } else {
            $data['field'] = "id";
        }

        $options = array();
        if (!empty($param[2])) {

            $options = (array) $param[2];
        }

        $data['options'] = $options;




        $sql = "SELECT * from ts_variable WHERE type ='JSON';";

        $res = $db->sql_query($sql);


        while ($ob = $db->sql_fetch_object($res))
        {
            $tmp = array();
            $tmp['id'] = $ob->id;
            $tmp['libelle'] = $ob->from."::".$ob->name;
            $tmp['extra'] = array("data-content" => "<small class='text-muted'>".$ob->from."</small> ".$ob->name);

            $data['variable'][] = $tmp;

        }


        $this->set('data', $data);


    }




    function getTagByServer($param)
    {

        $db = Sgbd::sql(DB_DEFAULT);

        $this->di['js']->addJavascript(array('bootstrap-select.min.js', 'Common/getDatabaseByServer.js'));


        $data['ajax'] = false;
        if (IS_AJAX) {
            $this->layout_name = false;
            $data['ajax']      = true;
        }

        $data['table'] = $param[0];
        $data['field'] = $param[1];
        $data['tags']  = $param[2];


        $options = array();
        if (!empty($param[3])) {

            $options = (array) $param[3];
        }

        $data['options'] = $options;

        //$data['width'] = $param[2] ?? "auto";
        //pour restreindre la liste des serveurs a ceux spécifier

        $mysql_server_specify = array();
        foreach ($data['options'] as $key => $val) {
            if ($key === "mysql_server_specify") {
                $mysql_server_specify = $val;

                unset($data['options'][$key]);
            }
        }

        $data['tag'] = self::getTagArray($db);

        $this->set("data", $data);
        return $data;
    }

    static public function getTagArray($db)
    {
        if (empty(self::$tags)) {
            //$db = Sgbd::sql(DB_DEFAULT);

            $sql = "SELECT * FROM tag order by name";

            $res = $db->sql_query($sql);

            $data['tag'] = array();
            while ($ob          = $db->sql_fetch_object($res)) {
                $tmp            = array();
                $tmp['id']      = $ob->id;
                $tmp['libelle'] = $ob->name;
                $tmp['extra']   = array("data-content" => "<span title='".$ob->name."' class='label' style='color:".$ob->color."; background:".$ob->background."'>".$ob->name."</span>");

                $data['tag'][] = $tmp;
            }

            self::$tags = $data['tag'];
        }

        return self::$tags;
    }


    static public function getAvailable($param = array())
    {

        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);
        $servers = Extraction::display(array('mysql_server::mysql_available'));


        $data =array();
        $available = array();
        $down = array();
        $warning = array();
        

        if (!empty($servers))
        {
            foreach($servers as $id_mysql_server => $server)
            {
                $data['id_mysql_server'][$id_mysql_server] = $server['']['mysql_available'];
                if ($server['']['mysql_available'] === "1"){
                    $available[] = $id_mysql_server;
                }elseif ($server['']['mysql_available'] === "0"){
                    $down[] = $id_mysql_server;
                }elseif ($server['']['mysql_available'] === "2"){
                    $warning[] = $id_mysql_server;
                }
                
            }

            $caseArray = array();
            
            foreach ($data['id_mysql_server'] as $id_mysql_server => $value) {
                if ($value == 1) {
                    $caseArray[] = "WHEN a.id = $id_mysql_server THEN 0\n";
                } elseif($value == 0) {
                    $caseArray[] = "WHEN a.id = $id_mysql_server THEN 1\n";
                }
                elseif($value == 2) {
                    $caseArray[] = "WHEN a.id = $id_mysql_server THEN 2\n";
                }
            }
            $caseArray[] = "ELSE 3\n";

            $data['case'] = "CASE " . implode(' ', $caseArray) . " END AS error";

            $data['available'] = implode(",", $available);
            $data['down'] = implode(",", $down);
            $data['warning'] = implode(",", $warning);
        }
        else{
            $data['case'] = " 0 as error";
            $data['available'] ='';
            $data['down'] = '';
            $data['warning'] = '';
        }

        Debug::debug($data);

        return $data;

    }
}