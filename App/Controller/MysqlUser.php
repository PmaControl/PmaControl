<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\Mysql;
use \Glial\Sgbd\Sgbd;
use \App\Library\Debug;

class MysqlUser extends Controller
{

    public function index($param)
    {

        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            if (!empty($_POST['mysql_server']['id'])) {
                header('location: '.LINK.$this->getClass().'/'.__FUNCTION__."/mysql_server:id:[".implode(',', $_POST['mysql_server']['id'])."]");
            }
        }

        $data = array();

        //debug($_GET);

        if (!empty($_GET['mysql_server']['id'])) {
            $db  = Sgbd::sql(DB_DEFAULT);
            $ids = substr($_GET['mysql_server']['id'], 1, -1);

            $sql = "SELECT * FROM mysql_server where id in (".$ids.")";

            $res1 = $db->sql_query($sql);

            while ($ob1 = $db->sql_fetch_object($res1)) {
                $db_link = Sgbd::sql($ob1->name);

                $users = Mysql::exportUserByUser($db_link);

                $sql152 = "SHOW DATABASES;";
                $res152 = $db_link->sql_query($sql152);

                $all_databases = array();
                while ($ob            = $db_link->sql_fetch_object($res152)) {
                    $all_databases[] = $ob->Database;
                }

                $data['user'][$ob1->name] = $users;

                foreach ($users as $user => $arr) {
                    foreach ($arr as $host => $bla) {


                        foreach ($bla as $grant) {

                            $pos = strpos($grant, "GRANT PROXY ON");

                            if ($pos !== false) {
                                continue;
                            }

                            // périmètre
                            preg_match_all('/\sON\s([^,]+)\sTO/', $grant, $output_array);

                            //debug($grant);
                            //preg_match_all('/ON\s(.*)\sTO/', $grant, $output_array);
                            $data['all_user'][$user][$host][$ob1->name]['database'][] = $output_array[1][0];

                            $output_array2 = array();
                            preg_match_all('/`([^`]+)`\./', $output_array[1][0], $output_array2);

                            if (!empty($output_array2[1][0])) {
                                $data['all_user'][$user][$host][$ob1->name]['match'][] = $output_array2[1][0];
                            } else {
                                $data['all_user'][$user][$host][$ob1->name]['match'][] = "";
                            }


                            // liste des droits
                            $output_array                                          = array();
                            preg_match('/GRANT\s(.*)\sON/', $grant, $out_grant);
                            $data['all_user'][$user][$host][$ob1->name]['grant'][] = explode(", ", $out_grant[1]);

                            //le droit de créé des users ?
                            $pos = strpos($grant, "WITH GRANT OPTION");
                            if ($pos !== false) {
                                $data['all_user'][$user][$host][$ob1->name]['create'] = true;
                            } else {
                                $data['all_user'][$user][$host][$ob1->name]['create'] = false;
                            }


                            //password
                            preg_match('/BY\sPASSWORD\s\'(.*)\'/', $grant, $output_array);

                            if (!empty($output_array[1])) {
                                $data['all_user'][$user][$host][$ob1->name]['password'] = $output_array[1];
                            }
                        }
                    }
                }
            }
        }


        $options         = array('SELECT', 'USAGE', 'SHOW VIEW');
        $black_list_user = array('root', 'debian-sys-maint', 'dba', 'pmacontrol', 'grafana_check', 'replicant', 'replicantssl', 'NagiosCheck', 'replication', 'sst');

        $data['revokes']      = array();
        $data['grant']        = array();
        $data['switch_to_ro'] = array();
        $data['revoke_all']   = array();

        if (!empty($data['all_user'])) {

            foreach ($data['all_user'] as $user => $hosts) {

                if (in_array($user, $black_list_user)) {
                    continue;
                }

                foreach ($hosts as $host => $servers) {


                    $data['revoke_all'][] = "DROP USER '".$user."'@'".$host."';";

                    foreach ($servers as $server_name => $server) {

                        $i = 0;
                        foreach ($server['database'] as $db) {


                            $all_right = $server['grant'][$i];

                            foreach ($server['grant'][$i] as $key => $grant) {
                                if (!in_array($grant, $options)) {
                                    unset($server['grant'][$i][$key]);
                                }


                                if ($grant === "ALL PRIVILEGES") {
                                    $server['grant'][$i][$key] = "SELECT";
                                }
                            }

                            if (count($server['grant'][$i]) === 0) {
                                if ($i === 0) {
                                    $server['grant'][$i][] = "USAGE";
                                } else {
                                    continue;
                                }
                            }

                            //user proxy without password
                            if (empty($server['password'])) {
                                $server['password'] = '';
                            }

                            $extra = "";
                            if ($i === 0) {
                                $extra = " IDENTIFIED BY PASSWORD '".$server['password']."'";
                            }

                            $revoke = false;
                            if (!empty($server['match'][$i])) {
                                if (!in_array($server['match'][$i], $all_databases)) {
                                    $data['revokes'][$server_name][] = "REVOKE ".implode(",", $all_right)." ON ".$db." FROM '".$user."'@'".$host."';";
                                    $revoke                          = true;
                                }
                            }

                            if (!$revoke) {
                                $data['grants'][] = "GRANT ".implode(",", $server['grant'][$i])." ON ".$db." TO '".$user."'@'".$host."'".$extra.";";
                            }

                            $i++;
                        }

                        //debug($server);
                    }
                }
            }
        }

        if (!empty($data['grants'])) {
            $data['grants'] = array_unique($data['grants']);
        }

        $this->set('data', $data);
    }

    public function backup()
    {

    }

    public function cmpHost($param)
    {
        Debug::parseDebug($param);
        Debug::debug($param);

        $id_mysql_server = $param[0];
        $host1           = $param[1];
        $host2           = $param[2];
        $remote          = Mysql::getDbLink($id_mysql_server);

        $sql = "select distinct user from mysql.user where host in('".$host1."', '".$host2."');";
        //$sql ="SELECT @@hostname as user;";
        Debug::sql($sql);

        $data = array();
        $res1 = $remote->sql_query($sql);

        //Debug::debug($remote);

        $hosts = array($host1);
        Debug::debug($hosts);

        while ($ob1 = $remote->sql_fetch_object($res1)) {

            //Debug::debug($ob1);
            //Debug::debug($hosts);

            foreach ($hosts as $host) {

                if (empty($host)) {
                    continue;
                }

                $sql4 = "SHOW GRANTS FOR '".$ob1->user."'@'".$host."';";
                //Debug::sql($sql4);
                $res4 = $remote->sql_query($sql4);

                while ($ob4 = $remote->sql_fetch_array($res4, MYSQLI_NUM)) {

                    $data[$host][$ob1->user][md5($ob4[0])] = $ob4[0];

                    echo $ob4[0]."\n";
                }
            }
        }
        exit;
        print_r($data);
    }

    public function security($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server WHERE is_available = 1";
        $res = $db->sql_query($sql);

        $data = array();
        while ($ob   = $db->sql_fetch_object($res)) {
            $link = Mysql::getDbLink($ob->id);

            $sql2 = "SELECT user as User, host as Host, password as Password,Super_priv,plugin as Plugin  from mysql.user ORDER BY user, host, password";
            $res2 = $link->sql_query($sql2);

            $data[$ob->id]['display_name'] = $ob->display_name;
            $data[$ob->id]['ip']           = $ob->ip;
            $data[$ob->id]['port']         = $ob->port;

            $data[$ob->id]['account'] = array();

            while ($arr2 = $link->sql_fetch_array($res2, MYSQLI_ASSOC)) {
                $data[$ob->id]['account'][] = $arr2;
            }
        }

        Debug::debug($data);
        /*
          echo '<pre>';
          print_r($data);
          echo '</pre>';
          /* */
        $this->set('data', $data);
    }
    /*
     * Get user never connected since start of the server
     * The goal is to effectuate a purge to remove all unused user
     *
     */

    public function getUserNeverConnected($param)
    {

        Debug::parseDebug($param);

        $id_mysql_server = $param[0];

        $db = Mysql::getDbLink($id_mysql_server);

        Debug::debug($id_mysql_server, 'id_mysql_server');

        $sql2 = "SELECT count(1) as cpt from performance_schema.users;";

        $res2 = $db->sql_query($sql2);
        while ($ob   = $db->sql_fetch_object($res2)) {
            $count = intval($ob->cpt);
        }

        $data['id_mysql_server'] = $id_mysql_server;
        $data['accounts']        = array();

        Debug::debug($count, 'performance_schema.users');

        if ($count > 0) {

            $sql = <<<SQL
            SELECT
              DISTINCT mu.user as user, mu.host as host
            FROM
              mysql.user mu
              LEFT JOIN performance_schema.users psu ON mu.user = psu.user
            WHERE
              psu.user IS NULL
              AND mu.user NOT IN (
                'mysql.infoschema', 'mysql.session', 'mysql.sys', 'mariadb.sys'
              )
            ORDER BY
              mu.user, mu.host;
SQL;

            $res = $db->sql_query($sql);

            while ($ob = $db->sql_fetch_object($res)) {
                $data['accounts'][] = $ob;
                if (IS_CLI) {


                    echo "'".$ob->user."'@'".$ob->host."';\t-- no connection since opening of server the \n";
                }
            }
        }

        $this->set('data', $data);
    }
}