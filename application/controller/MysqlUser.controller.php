<?php

use \Glial\Synapse\Controller;
use App\Library\Mysql;

class MysqlUser extends Controller
{

    public function index($param)
    {


        if ($_SERVER['REQUEST_METHOD'] === "POST") {


            if (!empty($_POST['mysql_server']['id'])) {
                header('location: '.LINK.__CLASS__.'/'.__FUNCTION__."/mysql_server:id:[".implode(',', $_POST['mysql_server']['id'])."]");
            }
        }

        $data = array();

        //debug($_GET);

        if (!empty($_GET['mysql_server']['id'])) {
            $db = $this->di['db']->sql(DB_DEFAULT);


            $ids = substr($_GET['mysql_server']['id'], 1, -1);

            $sql = "SELECT * FROM mysql_server where id in (".$ids.")";


            $res1 = $db->sql_query($sql);

            while ($ob1 = $db->sql_fetch_object($res1)) {
                $db_link = $this->di['db']->sql($ob1->name);


                $users = Mysql::exportUserByUser($db_link);


                $data['user'][$ob1->name] = $users;



                foreach ($users as $user => $arr) {
                    foreach ($arr as $host => $bla) {
                        $data['all_user'][$user.":".$user]['user'] = $user;
                        $data['all_user'][$user.":".$user]['host'] = $host;
                    }
                }
            }
        }


        $this->set('data', $data);
    }

    public function backup()
    {
        
    }
}