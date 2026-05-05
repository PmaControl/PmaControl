<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;

/**
 * Class responsible for about workflows.
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
class About extends Controller
{

/**
 * Render about state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/about/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index()
    {

        $name         = __("About");
        $this->title  = '<i class="fa fa-info-circle" style="font-size:32px"></i> '.$name;
        $this->ariane = '> <i class="fa fa-question" style="font-size:16px" aria-hidden="true"></i> Help > <i class="fa fa-info-circle" style="font-size:16px"></i> '
            .$name;

        $data['graphviz'] = shell_exec("dot -V 2>&1");   //bin oui le numéro de version s'affiche dans le flux d'errreur !
        $data['php']      = phpversion();
        $data['mysql']    = shell_exec("mysql --version");
        $data['kernel']   = shell_exec("uname -a");
        $data['os']       = shell_exec("lsb_release -ds");
        $data['build']    = shell_exec("git rev-parse HEAD");
        //$data['mysql'] = shell_exec("mysql --version");

       $data["time_zone"] = $this->getResult("SELECT @@session.time_zone;");
       $data["global_time_zone"] = $this->getResult("SELECT @@global.time_zone;");
       $data["system_time_zone"] = $this->getResult("SELECT @@global.system_time_zone;");
       $data["now"] = $this->getResult("SELECT NOW();");
       $data['date_php'] = date('Y-m-d H:i:s');

        $this->set('data', $data);
    }

/**
 * Retrieve about state through `getResult`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param string $sql Input value for `sql`.
 * @phpstan-param string $sql
 * @psalm-param string $sql
 * @return mixed Returned value for getResult.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getResult()
 * @example /fr/about/getResult
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getResult(string $sql)
    {
        $db= Sgbd::sql(DB_DEFAULT);
        
        $res = $db->sql_query($db->sql_real_escape_string($sql));

        while($arr = $db->sql_fetch_array($res,MYSQLI_NUM)) {
            return $arr[0];
        }

    }
}
