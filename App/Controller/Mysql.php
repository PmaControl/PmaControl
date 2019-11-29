<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Cli\Color;
use \Glial\Cli\Table;
use \Glial\Sgbd\Sql\Mysql\MasterSlave;
use \Glial\Security\Crypt\Crypt;
use \Glial\Synapse\FactoryController;
use \Glial\I18n\I18n;
use \App\Library\Debug;
use \App\Library\Graphviz;
use \App\Library\Mysql as Mysql2;
use \Glial\Sgbd\Sgbd;


class Mysql extends Controller
{
    const DEBUG = true;

    private $table_to_purge = array();
    public $foreign_key     = array();
    public $columns         = array();

    private function generate_passswd($length)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randstring = '';
        for ($i = 0; $i < $length; $i++) {
            $randstring .= $characters[rand(0, strlen($characters))];
        }
        return $randstring;
    }

//depracated !!
    private function get_server()
    {
        $server = array();
        $handle = fopen(CONFIG."serveur.csv", "r");

        if ($handle) {
            while (($buffer = fgets($handle, 4096)) !== false) {

                $buffer = trim($buffer);

                if (filter_var($buffer, FILTER_VALIDATE_IP)) {
                    $server[] = $buffer;
                } else {
                    Throw new Exception("PTB-002 : this ip is not valid : ".$buffer);
                }
            }
            if (!feof($handle)) {
                Throw new Exception("PTB-001 : impossible to load serveur.csv");
            }
            fclose($handle);
        }

        return $server;
    }

    public function user_add($param)
    {

        $this->view = false;

        $servers = $this->get_server();

        debug($param);

        $user   = $param[0];
        $passwd = $this->generate_passswd(10);

        if (empty($user)) {
            Throw new Exception("PTB-004 : user is requested !");
        }

        echo "Password generated : ".$passwd."\n";

        $i = 1;
        foreach ($servers as $server) {
            echo "\n[".$i."] Server : ".$server."\n";

            echo str_repeat("#", 80);
//$this->create_grant_account_mysql($server, $user, $passwd, "10.%");
            $this->ssh($server, 22, $this->_ssh_user, $this->_ssh_passwd);
            $i++;
        }
    }

    private function ssh($host, $port, $user, $passwd)
    {
        $connection = ssh2_connect($host, $port);

        if (ssh2_auth_password($connection, $user, $passwd)) {
            echo "Authentication Successful!\n";

            if (!($stdio = @ssh2_shell($connection, "xterm"))) {
                echo "[FAILED]<br />";
                exit(1);
            }

            $this->shell_cmd($stdio, "whoami");
            $this->shell_cmd($stdio, "sudo su -");
            $this->shell_cmd($stdio, "whoami");
            $this->shell_cmd($stdio, "adduser mlemanissier sudo");
            $this->shell_cmd($stdio, "echo 'xfghxfgh:452452:::xgfhxfgh,fdgwdfg,,:/home/sdffdf:/bin/bash' > /tmp/users");
            $this->shell_cmd($stdio, "newusers /tmp/users");
            $this->shell_cmd($stdio, "rm /tmp/users");

            fclose($stdio);
        } else {
            echo "FAIL !!!!!!!!!!!!!!!!!\n";
//Throw new \Exception("PTB-005 : impossible to login in : " . $host);
        }
    }

    private function exec($connection, $cmd)
    {

        $stream = ssh2_exec($connection, $cmd);

        $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);

        stream_set_blocking($errorStream, true);
        stream_set_blocking($stream, true);

        echo stream_get_contents($stream)."\n";

        $error = stream_get_contents($errorStream);

        if (!empty($error)) {
            echo "[ERROR] : ".$error."\n";
        }
    }

    private function shell_cmd($stdio, $cmd)
    {
        fwrite($stdio, $cmd."\n");
        sleep(1);
        while ($line = fgets($stdio)) {
            flush();
            echo $line;
        }
    }

    public function after_edit_db()
    {
        $this->view = false;


        $db = Sgbd::sql(DB_DEFAULT);

        Crypt::$key = CRYPT_KEY;

        foreach ($this->di['db']->getAll() as $server) {

            $info_server = $this->di['db']->getParam($server);

            $data['mysql_server']['name']   = $server;
            $data['mysql_server']['ip']     = $info_server['hostname'];
            $data['mysql_server']['login']  = $info_server['user'];
            $data['mysql_server']['passwd'] = Crypt::encrypt($info_server['password']);
            $data['mysql_server']['port']   = empty($info_server['port']) ? 3306 : $info_server['port'];

            if (!$db->sql_save($data)) {
                debug($data);
                debug($db->sql_error());
                exit;
            } else {
                echo $data['mysql_server']['name'].PHP_EOL;
            }
        }
    }

    public function user($params)
    {
        $this->view = false;

        $users          = array();
        $user_to_update = array();
        $list_user      = array();



        $nbuser = 0;
        if (is_array($params)) {

            array_map("stripslashes", $params);

            $nbuser = count($params);
            if ($nbuser > 0) {
                $users = "'".implode("','", $params)."'";
            }
        }

        $table = new Table(0);
        $table->addHeader(array("Top", "Server", "IP", "User", "Host", "Password"));

        $i        = 1;
        $password = array();

        $def = Sgbd::sql(DB_DEFAULT);

        $sql  = "SELECT * FROM mysql_server WHERE error = ''";
        $res2 = $def->sql_query($sql);


        while ($ob2 = $def->sql_fetch_object($res2)) {

            $db = Sgbd::sql($ob2->name);

            $server_config = $db->getParams();

            $port = empty($server_config['port']) ? 3306 : $server_config['port'];

            $sql = "select * FROM mysql.user";

            if ($nbuser > 0) {
                $sql .= " WHERE `User` IN (".$users.")";
            }

            $res = $db->sql_query($sql);
            while ($ob  = $db->sql_fetch_object($res)) {

                $password[] = $ob->Password;
                $table->addLine(array($i, $ob2->name, $server_config['hostname'].":".$port,
                    $ob->User, $ob->Host, $ob->Password));
                $i++;
            }
        }

        echo $table->Display();

        $table2 = new Table(1);
        $table2->addHeader(array("Top", "Password", "Count"));

        $tab_passwd = array_count_values($password);
        $i          = 1;
        foreach ($tab_passwd as $password => $count) {
            $table2->addLine(array($i, $password, $count));
            $i++;
        }
        echo $table2->Display();
    }

    public function passwd($params)
    {
        $this->view        = false;
        $this->layout_name = false;

        $users          = array();
        $user_to_update = array();
        $list_user      = array();

        array_map("stripslashes", $params);

        $nbuser = count($params);
        if ($nbuser > 0) {
            $users = "'".implode("','", $params)."'";
        }

        $table = new Table(0);
        $table->addHeader(array("Top", "Server", "IP", "User", "Host", "Password"));

        $i = 1;

        $password = array();
        foreach ($this->di['db']->getAll() as $key => $db_name) {

            $db = Sgbd::sql($db_name);

            $server_config = $db->getParams();

            $port = empty($server_config['port']) ? 3306 : $server_config['port'];

            $sql = "select * FROM mysql.user";

            if ($nbuser > 0) {
                $sql .= " WHERE `Password` IN (".$users.")";
            }

            $res = $db->sql_query($sql);
            while ($ob  = $db->sql_fetch_object($res)) {

                $password[] = $ob->Password;
                $table->addLine(array($i, $db_name, $server_config['hostname'].":".$port,
                    $ob->User, $ob->Host, $ob->Password));
                $i++;

                /*
                  $sql = "DROP USER 'nagios'@'".$ob->Host."';";
                  $db->sql_query($sql);

                  debug($sql); */
            }
        }

        echo $table->Display();

        $table2 = new Table(1);
        $table2->addHeader(array("Top", "Password", "Count"));

        $tab_passwd = array_count_values($password);
        $i          = 1;
        foreach ($tab_passwd as $password => $count) {
            $table2->addLine(array($i, $password, $count));
            $i++;
        }
        echo $table2->Display();
    }

    private function extract_query($Last_Error)
    {
        $sep = "Default database: 'PRODUCTION'. Query: '";

        $pos = strpos($Last_Error, $sep);

// Note our use of ===.  Simply == would not work as expected
// because the position of 'a' was the 0th (first) character.
        if ($pos === false) {
            echo "impossible to find : ".$sep."\n";
            exit;
        } else {
            $query = substr($Last_Error, $pos + strlen($sep), -1);
        }

        return $query;
    }

    public function status()
    {
        $this->layout_name = 'default';
        $this->view        = false;

        $MS = new MasterSlave();

        echo "\n";

        foreach ($this->di['db'] as $key => $db) {

            echo $key." ";

            echo str_repeat(" ", 20 - strlen($key));
            $server_config = $db->getParams();

            $master = $MS->isMaster($db);
            $slave  = $MS->isSlave($db);


            echo $server_config['hostname'];
            echo str_repeat(" ", 16 - strlen($server_config['hostname']));

            $sql  = "SHOW GLOBAL VARIABLES LIKE 'version'";
            $res  = $db->sql_query($sql);
            $data = $db->sql_fetch_array($res, MYSQLI_ASSOC);
            echo $data['Value'];
            echo str_repeat(" ", 29 - strlen($data['Value']));


            $sql  = "show GLOBAL variables like 'character_set_database'";
            $res  = $db->sql_query($sql);
            $data = $db->sql_fetch_array($res, MYSQLI_ASSOC);
            echo $data['Value'];
            echo str_repeat(" ", 10 - strlen($data['Value']));


            $sql  = "SHOW GLOBAL VARIABLES LIKE 'version_compile_machine'";
            $res  = $db->sql_query($sql);
            $data = $db->sql_fetch_array($res, MYSQLI_ASSOC);
            echo $data['Value'];
            echo str_repeat(" ", 7 - strlen($data['Value']));


            $sql  = "SHOW GLOBAL VARIABLES LIKE 'innodb_version'";
            $res  = $db->sql_query($sql);
            $data = $db->sql_fetch_array($res, MYSQLI_ASSOC);
            echo $data['Value'];
            echo str_repeat(" ", 12 - strlen($data['Value']));



            $sql  = "SHOW GLOBAL VARIABLES LIKE 'innodb_version'";
            $res  = $db->sql_query($sql);
            $data = $db->sql_fetch_array($res, MYSQLI_ASSOC);
            echo $data['Value'];
            echo str_repeat(" ", 12 - strlen($data['Value']));



            if ($master && $slave) {
                $type = " is master and slave";
            } elseif ($master) {
                $type = " is master";
            } elseif ($slave) {
                $type = " is slave";
            } else {
                $type = " is standalone";
            }

            echo $type;
            echo str_repeat(" ", 21 - strlen($type));

            if ($slave) {

                echo $slave['Master_Host'];
                echo str_repeat(" ", 21 - strlen($slave['Master_Host']));


                if ((int) $slave['Seconds_Behind_Master'] > 100) {
                    echo Color::getColoredString("Second behind master : ".$slave['Seconds_Behind_Master'], null, "red");
                } else {

                    if (isset($slave['Seconds_Behind_Master'])) {
                        echo Color::getColoredString("Second behind master : ".$slave['Seconds_Behind_Master'], "green");
                    } else {
                        echo Color::getColoredString("Not started".$slave['Seconds_Behind_Master'], null, "red");
                    }
                }
            } else {
                echo str_repeat(" ", 42);
            }

            /*
              echo " [";
              $sql = "show databases";
              $res = $db->sql_query($sql);
              while ($data = $db->sql_fetch_array($res,MYSQLI_ASSOC))
              {
              if (! in_array( $data['Database'], array("mysql", "performance_schema","information_schema" )))
              echo $data['Database']. " ";
              }
              echo " ]";
             */


            echo "\n";
        }
    }

    public function del($param)
    {

        $this->layout_name = false;
        $this->view        = false;
        $users             = $param;

        foreach ($users as $user) {
            if (!preg_match("/[\w]+/", $user)) {
                throw new \Exception("GLI-013 : User '".$user."' invalid");
            }
        }

        $def  = Sgbd::sql(DB_DEFAULT);
        $sql  = "SELECT * FROM mysql_server WHERE error = ''";
        $res2 = $def->sql_query($sql);


        while ($ob2 = $def->sql_fetch_object($res2)) {
            $db = Sgbd::sql($ob2->name);
            foreach ($users as $user) {

                $sql = "select * from mysql.user where user = '".$user."'";
                $res = $db->sql_query($sql);

                while ($ob = $db->sql_fetch_object($res)) {

                    $sql = "set sql_log_bin = 0;";
                    $db->sql_query($sql);

                    $sql = "DROP USER '".$user."'@'".$ob->Host."';";
                    $db->sql_query($sql);

                    echo $ob2->name." : ".$sql."\n";
                }
            }
        }
    }

    public function exportUser($param)
    {

        $this->layout_name = false;
        $this->view        = false;

        $users = $param;

        foreach ($users as $user) {
            if (!preg_match("/[\w]+/", $user)) {
                throw new \Exception("GLI-013 : User '".$user."' invalid");
            }
        }

        $def = Sgbd::sql(DB_DEFAULT);

        $sql  = "SELECT * FROM mysql_server WHERE error = ''";
        $res2 = $def->sql_query($sql);

        while ($ob2 = $def->sql_fetch_object($res2)) {

            $db = Sgbd::sql($ob2->name);
            foreach ($users as $user) {

                $sql = "select * from mysql.user where user = '".$user."'";
                $res = $db->sql_query($sql);

                $i  = 1;
                while ($ob = $db->sql_fetch_object($res)) {

                    if ($i === 1) {
                        echo "--On server '".$db->host."' :\n";
                        $i++;
                    }

                    $sql  = " SHOW GRANTS FOR '".$user."'@'".$ob->Host."';";
                    $res2 = $db->sql_query($sql);

                    $grants = array();
                    while ($table  = $db->sql_fetch_array($res2, MYSQLI_NUM)) {

//$grants[] = str_replace('`', '\`', $table[0]);
                        $grants[] = $table[0];
                    }
                    $export = implode(";", $grants);

                    if (strpos($db->host, ":")) {
                        $param = explode(":", $db->host);

                        $ip   = $param[0];
                        $port = $param[1];
                    } else {
                        $ip   = $db->host;
                        $port = "3306";
                    }

                    echo $export.";\n";
                }
            }
        }
    }

    public function replication()
    {
        $this->layout_name = false;
        $this->view        = false;

        $MS  = new MasterSlave();
        $tab = new Table();

        $tab->addHeader(array("Top", "Server", "IP", "Version", "M", "S", "IO", "SQL",
            "Behind", "File", "Position", "Current file", "Position"));

        $ip      = array();
        $masters = array();
        $i       = 0;
        foreach ($this->di['db']->getAll() as $db) {
            $i++;
            $server_config = Sgbd::sql($db)->getParams();


            $MS->setInstance(Sgbd::sql($db));
            $master = $MS->isMaster();
            $slave  = $MS->isSlave();

            $is_master = ($master) ? "ÃƒÂ¢Ã¢â‚¬â€œÃ‚Â " : "";
            $is_slave  = ($slave) ? "ÃƒÂ¢Ã¢â‚¬â€œÃ‚Â " : "";

            $sql  = "SHOW GLOBAL VARIABLES LIKE 'version'";
            $res  = Sgbd::sql($db)->sql_query($sql);
            $data = Sgbd::sql($db)->sql_fetch_array($res, MYSQLI_ASSOC);

            if (strpos($data['Value'], "-")) {
                $version = strstr($data['Value'], '-', true);
            } else {
                $version = $data['Value'];
            }

            if ($slave) {
                $io          = ($slave['Slave_IO_Running'] === 'Yes') ? "OK" : Color::getColoredString("KO", "grey", "red", "bold");
                $sql         = ($slave['Slave_SQL_Running'] === 'Yes') ? "OK" : Color::getColoredString("KO", "grey", "red", "bold");
                $time_behind = $slave['Seconds_Behind_Master'];
            } else {
                $io          = "";
                $sql         = "";
                $time_behind = "";
            }

            $master_binlog  = ($slave) ? $slave['Master_Log_File'] : "";
            $master_postion = ($slave) ? $slave['Exec_Master_Log_Pos'] : "";

            $file     = ($master) ? $master['File'] : "";
            $position = ($master) ? $master['Position'] : "";

            $addr = (!empty($server_config['port']) && is_numeric($server_config['port'])) ? $server_config['hostname'].":".$server_config['port'] : $server_config['hostname'];


            $ip[$addr] = array($i, $db, $addr, $version, $is_master, $is_slave, $io,
                $sql, $time_behind,
                $file, $position, $master_binlog, $master_postion);


            if ($slave) {
                $masters[$slave['Master_Host']][] = $addr;
            }
        }


//debug($masters);


        $color = "purple";

        $i = 1;
        $j = 0;


        $colors   = array("purple", "yellow", "blue", "cyan", "red", "green");
        $nb_color = count($colors);

        foreach ($masters as $master => $slaves) {
            $color = $colors[$j];
            $j++;
            $j     = $j % $nb_color;


            $ip[$master][0] = $i;
            $ip[$master][1] = Color::getColoredString("ÃƒÂ¢Ã¢â‚¬â€œÃ‚Â  ", $color).$ip[$master][1];

            $tab->addLine($ip[$master]);
            unset($ip[$master]);

            foreach ($slaves as $id_slave => $slave) {

                $i++;

                $ip[$slave][0] = $i;

                if ($cpt = count($masters[$master]) == 1) {
                    $ip[$slave][1] = Color::getColoredString("ÃƒÂ¢Ã¢â‚¬ï¿½Ã¢â‚¬ï¿½ÃƒÂ¢Ã¢â‚¬â€œÃ‚Â  ", $color).$ip[$slave][1];
                } else {
                    $ip[$slave][1] = Color::getColoredString("ÃƒÂ¢Ã¢â‚¬ï¿½Ã…â€œÃƒÂ¢Ã¢â‚¬â€œÃ‚Â  ", $color).$ip[$slave][1];
                }

                $tab->addLine($ip[$slave]);

                unset($masters[$master][$id_slave]);
                unset($ip[$slave]);
            }

            $i++;
        }

        foreach ($ip as $server) {
            $server[1] = "ÃƒÂ¢Ã¢â‚¬â€œÃ‚Â  ".$server[1];
            $server[0] = $i;
            $tab->addLine($server);

            $i++;
        }


        /*
         *             $tab->addLine(array($i, $db, $addr, $version, $is_master, $is_slave, $io, $sql, $time_behind,
          $file, $position, $master_binlog, $master_postion, "Log space"));
         */



        /* $tab->addLine(array($server_name, $server_config['hostname'], $version, $is_master, $is_slave, $io, $sql, $time_behind,
          $file, $position, $master_binlog,$master_postion, "Log space"));
         */

        echo PHP_EOL.$tab->display();


//echo Color::printAll();
    }

    public function timezone()
    {
        $this->view = false;

        $db = Sgbd::sql("itprod-dbhistory-sa-01");
        /*
          $sql = "select * from PROD_TRACES order by date_passage DESC limit 1";


          $res = $db->sql_query($sql);

          while ($ob = $db->sql_fetch_object($res)) {
          debug($ob);
          }



          $sql = "select * from PROD_TRACES order by ID_TRACE DESC limit 1";
          $res = $db->sql_query($sql);

          while ($ob = $db->sql_fetch_object($res)) {
          debug($ob);
          }
         */
//804639125 => 804639129

        $sql = "select * from PROD_TRACES where ID_TRACE >= 804639125 and ID_TRACE <= 804639129";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            debug($ob);
        }
    }

    function uncrypt()
    {
        $this->view = false;
        Crypt::$key = CRYPT_KEY;


        $sql = "SELECT * from mysql_server";
        $db  = Sgbd::sql(DB_DEFAULT);

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            debug(Crypt::decrypt($ob->passwd));
        }
    }
    /*
      SELECT SUBSTRING_INDEX(host, ':', 1) AS host_short,
      GROUP_CONCAT(DISTINCT USER)   AS users,
      COUNT(*)
      FROM   information_schema.processlist
      GROUP  BY host_short
      ORDER  BY COUNT(*),
      host_short;
     */

    function event($param)
    {
        $default = Sgbd::sql(DB_DEFAULT);


        $where = "";
        if (!empty($param[0])) {
            $where = " WHERE name='".str_replace('-', '_', $default->sql_real_escape_string($param[0]))."' ";
        }



        $default = Sgbd::sql(DB_DEFAULT);
        $sql     = "SELECT * FROM `mysql_event` a
           INNER JOIN mysql_server b ON a.id_mysql_server = b.id
           INNER JOIN mysql_status c ON c.id = a.id_mysql_status
           ".$where."
           order by a.id desc
           LIMIT 1000";

        $data['output'] = $default->sql_fetch_yield($sql);

        $sql = "SELECT b.name , b.ip, b.port,
           count(1) as nb_event
            FROM `mysql_event` a
           INNER JOIN mysql_server b ON a.id_mysql_server = b.id
           INNER JOIN mysql_status c ON c.id = a.id_mysql_status
           GROUP BY b.id
           order by count(1) desc";

        $data['count'] = $default->sql_fetch_yield($sql);





        $this->set('data', $data);
    }

    function clusterDisplay()
    {


        $default = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT a.name
            FROM mysql_server a
            INNER JOIN mysql_cluster_node b on b.id_mysql_server = a.id
            WHERE id_mysql_cluster = 1";

        $res = $default->sql_query($sql);


        $data['WSREP']   = array();
        $data['servers'] = array();

        while ($ob = $default->sql_fetch_object($res)) {
            $dblink = Sgbd::sql($ob->name);

            $sql = "(SELECT VARIABLE_NAME,VARIABLE_VALUE FROM INFORMATION_SCHEMA.GLOBAL_VARIABLES order by VARIABLE_NAME) ";
            $sql .= " UNION (SELECT VARIABLE_NAME,VARIABLE_VALUE FROM INFORMATION_SCHEMA.GLOBAL_STATUS)  order by VARIABLE_NAME";
// $sql .= " WHERE VARIABLE_NAME not LIKE 'ws%'";


            $res2 = $dblink->sql_query($sql);

            while ($ob2 = $dblink->sql_fetch_object($res2)) {
                $data['WSREP'][$ob2->VARIABLE_NAME][$ob->name] = $ob2->VARIABLE_VALUE;
            }

            $data['servers'][] = $ob->name;
        }

        $this->set('data', $data);
    }

    public function playskool()
    {
        $data['dbs'] = $this->di['db']->getAll();

        sort($data['dbs']);

        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $data['ret'] = '';

            foreach ($_POST['db'] as $db => $on) {
                $data['ret'] .= "mysql -h $db -u ".$_POST['login']." -p".$_POST['password']." -e '".str_replace("'", "\'", $_POST['sql'])."' > $db.log<br />";
            }
        }
        $this->set('data', $data);
    }

    public function mpd($param)
    {


        $id_mysql_server = $param[0];
        $database        = $param[1];


        $default = Sgbd::sql(DB_DEFAULT);

        $this->title  = __("The Physical Schemata Panel");
        $this->ariane = " > ".__("MySQL")." > ".$this->title;


        $sql = "SELECT name FROM mysql_server WHERE id=".intval($id_mysql_server);
        $res = $default->sql_query($sql);

        while ($ob = $default->sql_fetch_object($res)) {
            $name_connect = $ob->name;
        }

        $table_to_purge = array();
        $db             = Sgbd::sql($name_connect);


        if (!empty($param[2])) {
            $table_to_purge = FactoryController::addNode("Cleaner", "getTableImpacted", array($param[2]));

            Debug::debug($table_to_purge);
        }

        $file_name    = TMP.$id_mysql_server."_".$database.".svg";
        $data['file'] = $file_name;

        $path_parts = pathinfo($file_name);

        $path = $path_parts['dirname'];
        $type = $path_parts['extension'];
        $file = $path_parts['filename'];


        $sql = "SELECT * FROM `INFORMATION_SCHEMA`.`TABLES` WHERE TABLE_SCHEMA ='".$param[1]."' AND TABLE_TYPE='BASE TABLE'";



        $sql2 = "SELECT table_name FROM `information_schema`.`KEY_COLUMN_USAGE` "
            ."WHERE `CONSTRAINT_SCHEMA` ='".$param[1]."' "
            ."AND `REFERENCED_TABLE_SCHEMA`='".$param[1]."' "
            ."AND `REFERENCED_TABLE_NAME` IS NOT NULL
                          UNION  
                          SELECT REFERENCED_TABLE_NAME as table_name FROM `information_schema`.`KEY_COLUMN_USAGE` "
            ."WHERE `CONSTRAINT_SCHEMA` ='".$param[1]."' "
            ."AND `REFERENCED_TABLE_SCHEMA`='".$param[1]."' "
            ."AND `REFERENCED_TABLE_NAME` IS NOT NULL";


        $res2 = $db->sql_query($sql2);


        $liste_table_connected = array();
        while ($ob2    = $db->sql_fetch_object($res2)) {
            $liste_table_connected[] = $ob2->table_name;
        }

        /*
          $filter = array('commande_services', 'flux_commande_acces', 'service', 'histo_etape_commande_service', 'reseau', 'dsp', 'operateur', 'adresse', 'local', 'batiment', 'equipement', 'site', 'crmad_services',
          'local', 'adresse', 'etape_commande_service', 'cr', 'crmes');
          /* */

        //$filter = array();
        // $sql .= " AND TABLE_NAME in('batiment', 'equipement','escalier', 'etage', 'local', 'site' )";


        $tables = $db->sql_fetch_yield($sql);

        $fp = fopen($path.'/'.$file.'.dot', "w");


        if ($fp) {

            fwrite($fp, "digraph Replication { rankdir=LR; splines=ortho  ".PHP_EOL); //splines=ortho;


            foreach ($tables as $table) {


                //pour retirer les tables en attente d'être éffacer
                
               
                if (substr($table['TABLE_NAME'], 0, 4) === "zzz_") {
                    continue;
                }

                // si c'est pas dans la liste des tables qui sont connecté on ne l'affiche pas
                // On affiche pas les tables qui ne sont pas lié à une autre (on retire les tables singleton)
                
                if (!in_array($table['TABLE_NAME'], $liste_table_connected)) {
                    continue;
                }


                if (count($table_to_purge) > 0) {
                    if (in_array($table['TABLE_NAME'], $table_to_purge)) {
                        $color = '#337ab7';
                    } else {
                        $color = '#5cb85c';
                    }
                } else {
                    $color = $this->getColor($table['TABLE_NAME']);
                }

                //fwrite($fp, "\t edge [color=\"".$color."\"];".PHP_EOL);
                fwrite($fp, "\t node [color=\"".$color."\" shape=circo style=filled fontsize=8 ranksep=0 concentrate=true splines=true overlap=true];".PHP_EOL);

// shape=Mrecord
                fwrite($fp,
                    '  "'.$table['TABLE_NAME'].'" [style="" penwidth="3" fillcolor="yellow" fontname="arial" label =<<table border="0" cellborder="0" cellspacing="0" cellpadding="2" bgcolor="white"><tr><td bgcolor="black" color="white" align="center"><font color="white">'.$table['TABLE_NAME'].'</font></td></tr>');
                fwrite($fp, '<tr><td bgcolor="grey" align="left">'.$table['ENGINE'].' ('.$table['ROW_FORMAT'].')</td></tr>'.PHP_EOL);
                fwrite($fp, '<tr><td bgcolor="grey" align="left">total of '.$table['TABLE_ROWS'].'</td></tr>');

                /*

                  $sql = "SELECT * FROM information_schema.`COLUMNS`
                  WHERE TABLE_SCHEMA = '".$param[1]."' AND TABLE_NAME ='".$table['TABLE_NAME']."' ORDER BY ORDINAL_POSITION";


                  $columns = $db->sql_fetch_yield($sql);
                  foreach ($columns as $column) {
                  fwrite($fp, '<tr><td bgcolor="#dddddd" align="left" title="'.$column['COLUMN_NAME'].'">'.$column['COLUMN_NAME'].'</td></tr>'.PHP_EOL);
                  }
                 */


                fwrite($fp, '</table>> ];'.PHP_EOL);
            }


            $sql = "SELECT count(1) as cpt FROM information_schema.`tables` where table_name = 'REFERENTIAL_CONSTRAINTS' and table_schema = 'information_schema'";
            $res = $db->sql_query($sql);
            $ob  = $db->sql_fetch_object($res);

            if ($ob->cpt === "1") {


                $sql = "SELECT * FROM `information_schema`.`KEY_COLUMN_USAGE` "
                    ."WHERE `CONSTRAINT_SCHEMA` ='".$param[1]."' "
                    ."AND `REFERENCED_TABLE_SCHEMA`='".$param[1]."' "
                    ."AND `REFERENCED_TABLE_NAME` IS NOT NULL  ";
                //. "AND TABLE_NAME in('batiment', 'equipement','escalier', 'etage', 'local', 'site')"
                //. " AND `REFERENCED_TABLE_NAME` in('batiment', 'equipement','escalier', 'etage', 'local', 'site')";


                /*
                  $sql        = "SELECT * FROM information_schema.`REFERENTIAL_CONSTRAINTS`
                  WHERE CONSTRAINT_SCHEMA ='".$param[1]."' AND UNIQUE_CONSTRAINT_SCHEMA ='".$param[1]."'";
                 * 
                 */
                $contraints = $db->sql_fetch_yield($sql);


                //$columns = $this->getColumns(array($id_mysql_server, $database));

                foreach ($contraints as $contraint) {


                    if (substr($contraint['TABLE_NAME'], 0, 4) === "zzz_") {
                        continue;
                    }
                    if (substr($contraint['REFERENCED_TABLE_NAME'], 0, 4) === "zzz_") {
                        continue;
                    }

                    /*
                      if (!in_array($contraint['REFERENCED_TABLE_NAME'], $filter)) {
                      continue;
                      }

                      if (!in_array($contraint['TABLE_NAME'], $filter)) {
                      continue;
                      }
                     */

                    if (count($table_to_purge) > 0) {
                        if (in_array($contraint['REFERENCED_TABLE_NAME'], $table_to_purge) && in_array($contraint['REFERENCED_TABLE_NAME'], $table_to_purge)) {
                            $color = "#337ab7";
                        } else {
                            $color = "#5cb85c";
                        }
                    } else {

                        $color = $this->getColor($contraint['TABLE_NAME']);
                    }

                    /*
                      if ($columns[$contraint['TABLE_NAME']][$contraint['COLUMN_NAME']]['IS_NULLABLE'] === "YES" $contraint['REFERENCED_TABLE_NAME'], $this->table_to_purge) && in_array($contraint['REFERENCED_TABLE_NAME'], $this->table_to_purge))) {
                      $color = "#0000ff";
                      } */
                    /*

                      if ($columns[$contraint['REFERENCED_TABLE_NAME']][$contraint['REFERENCED_COLUMN_NAME']]['IS_NULLABLE'] === "YES") {
                      $color = "#000000";
                      }

                      if ($columns[$contraint['REFERENCED_TABLE_NAME']][$contraint['REFERENCED_COLUMN_NAME']]['IS_NULLABLE'] === "YES" && $columns[$contraint['TABLE_NAME']][$contraint['COLUMN_NAME']]['IS_NULLABLE']
                      === "YES") {
                      $color = "#FF0000";
                      }
                     */

                    fwrite($fp,
                        "".$contraint['TABLE_NAME']." -> ".$contraint['REFERENCED_TABLE_NAME']
                        .'[ arrowsize="1.5" penwidth="2" fontname="arial" fontsize=8 color="'.$color.'" 
                          tooltip="'.$contraint['TABLE_NAME'].'.'.$contraint['COLUMN_NAME'].' => '.$contraint['REFERENCED_TABLE_NAME'].'.'.$contraint['REFERENCED_COLUMN_NAME'].'" edgeURL=""];'.PHP_EOL);
                }
            } else {
                $data['NO_FK'] = 1;
            }

            fwrite($fp, '}');
            fclose($fp);
            exec('dot -T'.$type.' '.$path.'/'.$file.'.dot -o '.$path.'/'.$file.'.'.$type.'');
        }

        $this->set('data', $data);
    }

    public function addnagios()
    {
//photoways
        $this->view        = false;
        $this->layout_name = false;


        $i = 1;

        $password = array();
        foreach ($this->di['db']->getAll() as $key => $db_name) {

            $db = Sgbd::sql($db_name);

            $sql = "GRANT SELECT, PROCESS, SUPER ON *.* TO 'nagios'@'127.0.0.1' IDENTIFIED BY PASSWORD '*D4E97961BFEE8EB3E2CA39A541946FB7A9208590';";
            $db->sql_query($sql);

            $sql = "GRANT SELECT, PROCESS, SUPER ON *.* TO 'nagios'@'10.%' IDENTIFIED BY PASSWORD '*D4E97961BFEE8EB3E2CA39A541946FB7A9208590';";
            $db->sql_query($sql);
        }
    }

    public function thread($param)
    {
        $db = Sgbd::sql(str_replace('-', '_', $param[0]));
        $this->di['js']->addJavascript(array('jquery-latest.min.js', 'jQplot/jquery.jqplot.min.js',
            'jQplot/plugins/jqplot.dateAxisRenderer.min.js'));

        /*

          $sql = "SELECT avg(busy_pct) as busy_pct,  avg(one_min_avg) as one_min_avg ,avg(five_min_avg) as five_min_avg,avg(fifteen_min_avg) as fifteen_min_avg
          FROM pma_cli.slave_sql_load_average
          where tstamp >= DATE_SUB(NOW(),INTERVAL 1 HOUR)
          GROUP BY HOUR(tstamp), MINUTE(tstamp)
          order by id";
         */


        $res = $db->sql_query("SHOW DATABASES like 'pma_cli';");

        if ($db->sql_num_rows($res) === 1) {

            $data['pma_cli'] = true;
        } else {
            $data['pma_cli'] = false;
        }

        if ($data['pma_cli']) {
            $sql = "SELECT busy_pct,  one_min_avg,five_min_avg,fifteen_min_avg , tstamp
		FROM pma_cli.slave_sql_load_average 
		order by id";

//where tstamp >= DATE_SUB(NOW(),INTERVAL 1 HOUR)

            $avgs = $db->sql_fetch_yield($sql);


            foreach ($avgs as $line) {
                $data['busy_pct'][]        = $line['busy_pct'];
                $data['one_min_avg'][]     = $line['one_min_avg'];
                $data['five_min_avg'][]    = $line['five_min_avg'];
                $data['fifteen_min_avg'][] = $line['fifteen_min_avg'];
                $data['tstamp'][]          = $line['tstamp'];
            }


            $this->di['js']->code_javascript("$(document).ready(function(){
  var plot1 = $.jqplot ('chart1', [[".implode(',', $data['one_min_avg'])."],[".implode(',', $data['five_min_avg'])."],[".implode(',', $data['fifteen_min_avg'])."]]
  , {title:'Avg 1 min / 5min / 15 min',  seriesDefaults: { 
        showMarker:false
        
      }} );
  
    var plot2 = $.jqplot ('chart2', [[".implode(',', $data['busy_pct'])."]]
  , {title:'% busy' , seriesDefaults: { 
        showMarker:false
        
      }} 
  );
  
});");
        }

        $MS = new MasterSlave();

        $MS->setInstance($db);

        $slave = $MS->isSlave();

        if (count($slave) > 1) {
            foreach ($slave as $thread) {
                if ($thread['Connection_name'] === $param[1]) {
                    $ret = $thread;
                }
            }
        } else {
            $ret = $slave[0];
        }

        $data['thread'] = $ret;
        $this->set('data', $data);
    }

    public function load_db($param)
    {
        $this->layout_name = false;
        $this->view        = false;

        $ip     = $param[0];
        $server = $param[1];

        $db = Sgbd::sql(str_replace("-", "_", $server)); //generate alert if not exist


        $path = '/data/backup/'.$ip;

        $database = [];

        if (!is_dir($path)) {
            throw new \Exception('PMACTRL-005 : this ip doesn\'t have backup "'.$ip.'"');
        } else {
            if (is_dir($path)) {
                if ($dh = opendir($path)) {
                    while (($file = readdir($dh)) !== false) {


                        if ($file != "." && $file != "..") {


                            if (is_dir($path."/".$file)) {
                                $database[] = $file;
                            }
                        }
                    }
                    closedir($dh);
                }
            }
        }


        echo "DATABASES FOUND : \n";
        debug($database);


        $db_order = [];


        $i = 0;
        foreach ($database as $base) {
            $path_base = $path.'/'.$base;

            if (is_dir($path_base)) {
                if ($dh = opendir($path_base)) {
                    while (($file = readdir($dh)) !== false) {
                        if ($file != "." && $file != "..") {
                            if (strpos($file, date('Y-m-d')) !== false) {


                                if (pathinfo($file)['extension'] == "gz") {
                                    echo "gzip -d ".$path_base.'/'.$file."\n";

                                    shell_exec('cd '.$path_base.'; gzip -d '.$path_base.'/'.$file);

                                    $file = substr($file, 0, -3);
                                }

                                if (file_exists($path_base.'/'.$file)) {


                                    $out = $this->getLogAndPos($path_base.'/'.$file);

                                    $file_position = $out['file']."-".$out['position']."-".$i;



                                    $db_order[$file_position]["filename"] = $path_base.'/'.$file;
                                    $db_order[$file_position]["db"]       = $base;
                                    $db_order[$file_position]["file"]     = $out['file'];
                                    $db_order[$file_position]["position"] = $out['position'];



                                    echo "$file a été modifié le : ".date("F d Y H:i:s.", filemtime($path_base.'/'.$file))."\n";
                                }
                            }
                        }
                    }
                    closedir($dh);
                }
            }

            $i++;
        }

        ksort($db_order);
        debug($db_order);



        $change_master = [];

        $tmp = $db_order;
        foreach ($tmp as $timestamp => $arr) {
            $out                  = $this->getLogAndPos($arr['filename']);
            $db_order[$timestamp] = array_merge($db_order[$timestamp], $out);
        }

        debug($db_order);

        $i = 0;

        $replicate_do_db = [];


        foreach ($db_order as $timestamp => $arr) {


            /*
              if ($arr['db'] == "PRODUCTION") {
              continue;
              } */

            $sql = "STOP SLAVE;";
            $db->sql_query($sql);
            $this->log($sql);





            if ($i !== 0) {
                $sql = "START SLAVE UNTIL MASTER_LOG_FILE='".$arr['file']."', MASTER_LOG_POS=".$arr['position'].";";
                $db->sql_query($sql);
                $this->log($sql);

// wait file and position
                $this->waitPosition($db, $arr['file'], $arr['position']);
            }

            if ($arr['db'] != "mysql") {

                $sql = "DROP DATABASE IF EXISTS `".$arr['db']."`;";
                $db->sql_query($sql);
                $this->log($sql);

                $sql = "CREATE DATABASE `".$arr['db']."`;";
                $db->sql_query($sql);
                $this->log($sql);
            } else {
                continue;
            }

            $db->sql_close(); // to prevent, MySQL has gone away !

            $db->_param['port'] = empty($db->_param['port']) ? 3306 : $db->_param['port'];


            $cmd = "pv ".$arr['filename']." | mysql -h ".$db->_param['hostname']." -u ".$db->_param['user']." -P ".$db->_param['port']." -p".$db->_param['password']." ".$arr['db']."";
            echo $cmd."\n";

            $this->cmd($cmd);

            $db = Sgbd::sql(str_replace("-", "_", $server));


            if ($i === 0) {
                $sql = "CHANGE MASTER TO MASTER_LOG_FILE='".$arr['file']."', MASTER_LOG_POS=".$arr['position'].";";
                $db->sql_query($sql);
                $this->log($sql);
            }

            $replicate_do_db[] = $arr['db'];

            $sql = "SET GLOBAL replicate_do_db = '".implode(",", $replicate_do_db)."';";
            $db->sql_query($sql);
            $this->log($sql);

            $i++;
        }


        $sql = "START SLAVE";
        $db->sql_query($sql);
        $this->log($sql);
    }

    private function getLogAndPos($filename)
    {
        $handle = fopen($filename, "r");
        if ($handle) {

            $i      = 0;
            while (($buffer = fgets($handle, 4096)) !== false) {
                if (strpos($buffer, "CHANGE MASTER") !== false) {

                    $ret = [];

                    $ret['file']     = explode("'", explode("MASTER_LOG_FILE='", $buffer)[1])[0];
                    $ret['position'] = substr(trim(explode("=", $buffer)[2]), 0, -1);

                    return $ret;
                }

                $i++;

                if ($i > 30) {
                    throw new \Exception('PMACTRL-006 Impossible to find \'CHANGE MASTER\' in header of \''.$filename.'\'');
                }
            }
            if (!feof($handle)) {
                echo "Erreur: fgets() a échoué\n";
            }
            fclose($handle);
        }
    }

    public function cmd($cmd)
    {
        $code_retour = 0;

        passthru($cmd, $code_retour);

        if ($code_retour !== 0) {
            throw new \Exception('the following command failed : "'.$cmd.'"');
        } else {
            return true;
        }
    }

    public function waitPosition($db, $file, $position)
    {
        $MS = new MasterSlave();
        $MS->setInstance($db);

        do {
            $thread_slave = $MS->isSlave();
            foreach ($thread_slave as $thread) {

                $Relay_Master_Log_File = $thread['Relay_Master_Log_File'];
                $Exec_Master_Log_Pos   = $thread['Exec_Master_Log_Pos'];
            }

            $sql = "SHOW SLAVE STATUS;";
            $this->log($sql);


            if (!empty($thread['Last_Errno'])) {
                debug($thread);

                throw new \Exception('PMACLI-037 Error : Impossible to load data !');
            }


            $tab = new Table(1);
            $tab->addHeader(array("Relay_Master_Log_File", "Exec_Master_Log_Pos"));
            $tab->addLine(array($Relay_Master_Log_File, $Exec_Master_Log_Pos));
            echo $tab->display();


            sleep(1);
        } while ($file != $Relay_Master_Log_File || $position != $Exec_Master_Log_Pos);
    }

    public function log($sql)
    {
        echo \SqlFormatter::highlight($sql);
    }

    public function after($param)
    {
        
    }

    public function generate_config()
    {
        $db               = Sgbd::sql(DB_DEFAULT);
        $this->db_default = $db;
        $this->title      = __("Configurator");
        $this->ariane     = "> ".'<a href="'.LINK.'Plugins/index/">'.__('Tools box')."</a> > ".$this->title;
    }

    public function add($param)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        if ($_SERVER['REQUEST_METHOD'] == "POST") {

            if (!empty($_POST['mysql_server']['ip']) && !empty($_POST['mysql_server']['port']) && !empty($_POST['mysql_server']['login']) && !empty($_POST['mysql_server']['password'])) {


                if (!$this->scanPort($_POST['mysql_server']['ip'], $_POST['mysql_server']['port'])) {

                    $_SESSION['ERROR']['mysql_server']['ip']   = I18n::getTranslation(__("Maybe this address is not good"));
                    $_SESSION['ERROR']['mysql_server']['port'] = I18n::getTranslation(__("Maybe this port is not good"));

                    $msg   = I18n::getTranslation(__("Impossible to reach this MySQL server"));
                    $title = I18n::getTranslation(__("Connection error"));
                    set_flash("error", $title, $msg);

                    header("location: ".LINK."mysql/add/".$this->getPost());
                    exit;
                }


                $ret = $this->testMySQL($_POST['mysql_server']['ip'], $_POST['mysql_server']['port'], $_POST['mysql_server']['login'], $_POST['mysql_server']['password']);

                if ($ret !== true) {
                    //debug($_POST);
                    //debug($ret);

                    $_SESSION['ERROR']['mysql_server']['login']    = I18n::getTranslation(__("Maybe this login is wrong"));
                    $_SESSION['ERROR']['mysql_server']['password'] = I18n::getTranslation(__("Wrong password"));

                    $msg   = $ret;
                    $title = I18n::getTranslation(__("MySQL's connection error"));
                    set_flash("error", $title, $msg);

                    header("location: ".LINK."mysql/add/".$this->getPost());
                    exit;
                }

                $table['mysql_server'] = $_POST['mysql_server'];

                $table['mysql_server']['port']                = $_POST['mysql_server']['port'] ?? 3306;
                $table['mysql_server']['ip']                  = $table['mysql_server']['ip'];
                $table['mysql_server']['display_name']        = Mysql2::getHostname($table['mysql_server']['display_name'],
                        array($table['mysql_server']['ip'], $table['mysql_server']['login'], $table['mysql_server']['password'], $table['mysql_server']['port']));
                $table['mysql_server']['name']                = "server_".uniqid();
                $table['mysql_server']['hostname']            = $table['mysql_server']['display_name'];
                $table['mysql_server']['passwd']              = Crypt::encrypt($table['mysql_server']['password'], CRYPT_KEY);
                $table['mysql_server']['database']            = $table['mysql_server']['database'] ?? "mysql";
                $table['mysql_server']['is_password_crypted'] = "1";
                $table['mysql_server']['id_environment']      = "1";

                /*
                  debug($table);
                  debug($_POST);
                  exit;
                  /** */

                $ret = $db->sql_save($table);


                if (!$ret) {


                    //debug($table);

                    $msg   = json_encode($db->sql_error());
                    $title = I18n::getTranslation(__("Error"));
                    set_flash("error", $title, $msg);

                    header("location: ".LINK."mysql/add/".$this->getPost());
                    exit;
                } else {

                    Mysql2::onAddMysqlServer(Sgbd::sql(DB_DEFAULT));


                    $msg   = I18n::getTranslation(__("Your MySQL server was successfully added !"));
                    $title = I18n::getTranslation(__("Success"));
                    set_flash("success", $title, $msg);


                    //echo "OK !!!";
                    header("location: ".LINK."mysql/add/");
                    exit;
                }
            } else {

                $msg   = I18n::getTranslation(__("IP, port, login and password are mandatory"));
                $title = I18n::getTranslation(__("User error"));
                set_flash("error", $title, $msg);

                header("location: ".LINK."mysql/add/".$this->getPost());
                exit;
            }
        }


        $sql = "SELECT * FROM client ORDER BY libelle";
        $res = $db->sql_query($sql);

        $data['client'] = [];
        while ($ob             = $db->sql_fetch_object($res)) {
            $tmp              = [];
            $tmp['id']        = $ob->id;
            $tmp['libelle']   = $ob->libelle;
            $data['client'][] = $tmp;
        }


        $sql = "SELECT * FROM environment ORDER BY libelle";
        $res = $db->sql_query($sql);

        $data['environment'] = [];
        while ($ob                  = $db->sql_fetch_object($res)) {
            $tmp                   = [];
            $tmp['id']             = $ob->id;
            $tmp['libelle']        = $ob->libelle;
            $data['environment'][] = $tmp;
        }


        if (empty($_GET['mysql_server']['login'])) {
            $_GET['mysql_server']['login'] = "root";
        }
        if (empty($_GET['mysql_server']['port'])) {
            $_GET['mysql_server']['port'] = 3306;
        }


        $this->set('data', $data);
    }

    private function testMySQL($hostname, $port, $user, $password)
    {

        $this->link = mysqli_connect($hostname.":".$port, $user, trim($password), "mysql");

        if ($this->link) {
            return true;
        } else {
            return 'Connect Error ('.mysqli_connect_errno().') '.mysqli_connect_error();
        }
    }

    private function scanPort($ip, $port, $timeOut = 1)
    {
        $connection = @fsockopen($ip, $port, $errno, $errstr, $timeOut);

        if (is_resource($connection)) {

            fclose($connection);
            return true;
        }

        return false;
    }

    private function getPost()
    {
        $ret = [];

        foreach ($_POST as $main => $elems) {
            foreach ($elems as $key => $val) {
                $ret[] = $main.":".$key.":".$val;
            }
        }

        return implode('/', $ret);
    }

    public function reverseConfig($param)
    {
        $this->view = false;

        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server a ORDER BY id_client";
        $res = $db->sql_query($sql);

        $config = "";
        while ($ob     = $db->sql_fetch_object($res)) {
            $string = "[".$ob->name."]\n";
            $string .= "driver=mysql\n";
            $string .= "hostname=".$ob->ip."\n";
            $string .= "port=".$ob->port."\n";
            $string .= "user=".$ob->login."\n";
            $string .= "password=".$ob->passwd."\n";
            $string .= "crypted=1\n";
            $string .= "database=mysql\n";

            $config .= $string."\n\n";

            Debug::debug($string);
        }

        file_put_contents(ROOT."/configuration/db.config.ini.php", $config);
    }

    public function parsecnf()
    {




        $myfile = file("/etc/mysql/my.cnf");


        $cnf         = array();
        $include_dir = array();
        $parsed      = array();


        foreach ($myfile as $line) {
            $comment_removed   = explode('#', $line)[0];
            $comment_removed_t = trim($comment_removed);

            if (!empty($comment_removed_t)) {
                if (substr($comment_removed_t, 0, 11) === "!includedir") {
                    $include_dir[] = trim(str_replace("!includedir", "", $comment_removed_t));
                }

                $cnf[] = $comment_removed_t;
            }
        }

        $pure_cnf = implode("\n", $cnf);

        $for_split = preg_replace("/\[\w+\-?\d?\.?\d?\]/s", "###$0", $pure_cnf);

        $sections = explode('###', $for_split);

        unset($sections[0]);


        foreach ($sections as $section) {
            $lines        = explode("\n", $section);
            $section_name = trim($lines[0], "[]");

            unset($lines[0]);

            foreach ($lines as $line) {
                $options = explode("=", $line);

                $var                           = trim($options[0]);
                $val                           = trim($options[1]);
                $parsed[$section_name][$var][] = $val;
            }
        }


        print_r($parsed);
    }

    public function getAlias($db)
    {

        $default = Sgbd::sql(DB_DEFAULT);

        $slaves = $db->isSlave();

        foreach ($slaves as $slave) {


            if (!filter_var($slave['Master_Host'], FILTER_VALIDATE_IP)) {


                $list_ip_destinations = trim(shell_exec("getent hosts ".$slave['Master_Host']." | awk '{print $1}'"));

                $ips = explode("\n", $list_ip_destinations);

                foreach ($ips as $ip_destination) {


                    if (!filter_var($ip_destination, FILTER_VALIDATE_IP)) {

                        $data['alias_dns']['dns']         = $slave['Master_Host'];
                        $data['alias_dns']['port']        = $slave['Master_Port'];
                        $data['alias_dns']['destination'] = $ip_destination;

                        $default->sql_save($data);
                    }
                }
            }
        }
    }
    /*
     * 
     * 
     * 
     * 
     */

    public function getRealForeignKey($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $database        = $param[1];

        $name = Mysql2::getDbLink(Sgbd::sql(DB_DEFAULT), $id_mysql_server);
        $db   = Sgbd::sql($name);

        $sql = "select TABLE_NAME as table_name,column_name, referenced_table_name, referenced_column_name
            from information_schema.KEY_COLUMN_USAGE 
            where TABLE_SCHEMA = '".$database."' AND REFERENCED_TABLE_SCHEMA = '".$database."'
            and REFERENCED_COLUMN_NAME is not null;";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $this->foreign_key[$id_mysql_server][$database][] = $ob;
        }


        return $this->foreign_key[$id_mysql_server][$database];
    }

    public function getColumns($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $database        = $param[1];

        $name = Mysql2::getDbLink(Sgbd::sql(DB_DEFAULT), $id_mysql_server);
        $db   = Sgbd::sql($name);

        $sql = "select TABLE_NAME, COLUMN_NAME, IS_NULLABLE , DATA_TYPE, CHARACTER_MAXIMUM_LENGTH from information_schema.COLUMNS WHERE TABLE_SCHEMA='".$database."';";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $this->columns[$id_mysql_server][$database][$ob['TABLE_NAME']][$ob['COLUMN_NAME']] = $ob;
        }

        return $this->columns[$id_mysql_server][$database];
    }

    public function getColor($string)
    {



        $color = Graphviz::$color[hexdec(substr(md5($string), 0, 2))];

        //echo $color ."\n";



        $h1 = substr(md5($string), 5, 2);
        $h2 = substr(md5($string), 10, 2);
        $h3 = substr(md5($string), 0, 2);


        $color = $h1.$h2.$h3;

        return "#".$color;
    }
}