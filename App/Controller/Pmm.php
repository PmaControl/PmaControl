<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Security\Crypt\Crypt;
use \Glial\Sgbd\Sgbd;

//require ROOT."/application/library/Filter.php";
// ./glial control rebuildAll --debug

/**
 * Class responsible for pmm workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Pmm extends Controller
{
   
/**
 * Handle pmm state through `export`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @return void Returned value for export.
 * @phpstan-return void
 * @psalm-return void
 * @see self::export()
 * @example /fr/pmm/export
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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

        /*
        pmm-admin add mysql --environment=test --custom-labels='source=slowlog' --username=root --password=password 
        --tls --tls-skip-verify --tls-ca=pathtoca.pem --tls-cert=pathtocert.pem --tls-key=pathtocertkey.pem 
        --query-source=slowlog MySQLSlowLog localhost:3306

        */
    }

}









