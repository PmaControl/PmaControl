<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Security\Crypt\Crypt;
use \Glial\Sgbd\Sgbd;

//require ROOT."/application/library/Filter.php";
// ./glial control rebuildAll --debug

class Pmm extends Controller
{
   
    public function export()
    {
        $this->view = false;

        $db = SGBD::sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server ORDER BY display_name";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res))
        {

            Crypt::$key = CRYPT_KEY;
            $password   = Crypt::decrypt($ob->passwd);

            echo "pmm-admin add mysql \
  --username=$ob->login \
  --password='$password' \
  --host=$ob->ip \
  --port=$ob->port \
  --service-name=$ob->display_name\n\n";

        }

    }

}








