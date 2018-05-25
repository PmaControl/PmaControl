<?php

use \Glial\Synapse\Controller;



//require ROOT."/application/library/Filter.php";

class Architecture extends Controller {

    use \App\Library\Filter;

    public function index() {

        $this->title = '<i class="fa fa-object-group"></i> ' . __("Architecture");
        $this->ariane = ' > <a href⁼"">' . '<span class="glyphicon glyphicon glyphicon-home" style="font-size:12px"></span> '
                . __("Dashboard") . '</a> > <i class="fa fa-object-group" style="font-size:14px"></i> ' . __("Architecture");


        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT c.id,c.display,c.height,c.`date`, b.id_architecture FROM mysql_server a
            INNER JOIN link__architecture__mysql_server b ON a.id = b.id_mysql_server
            INNER JOIN architecture c ON c.id = b.id_architecture
            WHERE 1 ".$this->getFilter()." AND c.height > 8 GROUP BY c.id ORDER BY c.height DESC,c.width DESC ";


        //@TODO c.height > 8   => to fix on register table architecture on Dot

        /***
         * SELECT c.id,c.display FROM mysql_server a
            INNER JOIN link__architecture__mysql_server b ON a.id = b.id_mysql_server
            INNER JOIN architecture c ON c.id = b.id_architecture
            WHERE 1 GROUP BY c.id
         */
        
        $data['graphs'] = $db->sql_fetch_yield($sql);
        
        
        $this->set('data', $data);
    }



}
