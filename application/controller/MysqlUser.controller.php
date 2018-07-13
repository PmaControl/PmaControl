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
            $db  = $this->di['db']->sql(DB_DEFAULT);
            $ids = substr($_GET['mysql_server']['id'], 1, -1);

            $sql = "SELECT * FROM mysql_server where id in (".$ids.")";

            $res1 = $db->sql_query($sql);

            while ($ob1 = $db->sql_fetch_object($res1)) {
                $db_link = $this->di['db']->sql($ob1->name);


                $users = Mysql::exportUserByUser($db_link);


                $data['user'][$ob1->name] = $users;


                foreach ($users as $user => $arr) {
                    foreach ($arr as $host => $bla) {


                        foreach ($bla as $grant) {

                            $pos = strpos($grant, "GRANT PROXY ON");

                            if ($pos !== false) {
                                continue;
                            }

                            // périmètre
                            preg_match_all('/ON\s(.*)\sTO/', $grant, $output_array);
                            $data['all_user'][$user][$host][$ob1->name]['database'][] = $output_array[1][0];



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


        $this->set('data', $data);
    }

    public function backup()
    {
        
    }
}