<?php
/*
 * j'ai ajouté un mail automatique en cas d'erreur ou de manque sur une PK
 *
 * 15% moins bien lors du chargement par rapport à une sauvegarde générer avec mysqldump
 * le temps de load peut être optimisé
 */

namespace App\Controller;

use \App\Library\Debug;
use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;

class Deploy extends Controller
{

    public function index()
    {

        $this->title = '<i class="fa fa-arrows-alt" aria-hidden="true"></i> '.__("Deploy");

        //$this->di['js']->addJavascript(array("bootstrap-tooltip.js"));
        $this->di['js']->code_javascript('$(function () {  $(\'[data-toggle="popover"]\').popover({trigger:"hover"}) });');
        $this->di['js']->code_javascript('
            $(\'[data-toggle="popover"]\').each(function(index, element) {
    var contentElementId = $(element).data().target;
    var contentHtml = $(contentElementId).html();
    $(element).popover({
        content: contentHtml,
        trigger:"hover",
        html:true
    });
});');
    }

    public function execute($param)
    {
        Debug::parseDebug($param);

        $execute = implode(" ", $param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT name, display_name, ip, port FROM mysql_server where is_available=1;";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {


            echo "Connected to ".$ob->display_name." (".$ob->ip.":".$ob->port.")\n";
            $remote = Sgbd::sql($ob->name);

            $remote->sql_query("set sql_log_bin=0");
            $res2 = $remote->sql_query($execute);

            if ($res2) {
                if ($remote->sql_num_rows($res2) > 0) {
                    while ($arr = $remote->sql_fetch_array($res2, MYSQLI_ASSOC)) {
                        print_r($arr);
                    }
                } else {
                    echo "SUCCESS\n";
                }
            } else {
                Debug::debug("We exited because we found one error", "[ERROR]");
                exit;
            }

            $remote->sql_close();
        }
    }
}