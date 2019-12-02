<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use Glial\Cli\Table;
use \Glial\Sgbd\Sgbd;


//require ROOT."/application/library/Filter.php";

class CompareData extends Controller {

    public function index() {

        $db1 = Sgbd::sql("hb03_middletac01_zm");
        $db2 = Sgbd::sql("preprod_mariatac01");

        $db1->sql_select_db('BOUTIQUE');
        $db2->sql_select_db('BOUTIQUE');



        $table1 = $db1->getListTable()['table'];
        $table2 = $db1->getListTable()['table'];


        $cmp = array();
        foreach ($table1 as $table) {
            $sql = "SELECT count(1) as cpt FROM BOUTIQUE." . $table;
            $res = $db1->sql_query($sql);

            while ($ob = $db1->sql_fetch_object($res)) {

                $cmp[$table]['hb03_middletac01'] = $ob->cpt;
            }
        }


        foreach ($table2 as $table) {
            $sql = "SELECT count(1) as cpt FROM BOUTIQUE." . $table;
            $res = $db2->sql_query($sql);

            while ($ob = $db2->sql_fetch_object($res)) {

                $cmp[$table]['preprod_mariatac01'] = $ob->cpt;
            }
        }


        $table = new Table(2);


        $table->addHeader(array("table", "hb03_middletac01", "preprod_mariatac01", "diff"));



        foreach ($cmp as $table_name => $gg) {

            if ($gg["hb03_middletac01"] !== $gg["preprod_mariatac01"]) {
                $table->addLine(array($table_name, $gg["hb03_middletac01"], $gg["preprod_mariatac01"], $gg["hb03_middletac01"] - $gg["preprod_mariatac01"]));
            }
        }

        echo $table->display();



        $data['cmp'] = $cmp;




        //debug($cmp);


        $this->set('data', $data);
    }

}
