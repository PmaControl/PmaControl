<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;

//require ROOT."/application/library/Filter.php";

class Architecture extends Controller
{

    use \App\Library\Filter;

    public function index()
    {

        $this->title  = '<i class="fa fa-object-group"></i> '.__("Architecture");
        $this->ariane = ' > <a href⁼"">'.'<span class="glyphicon glyphicon glyphicon-home" style="font-size:12px"></span> '
            .__("Dashboard").'</a> > <i class="fa fa-object-group" style="font-size:14px"></i> '.__("Architecture");


        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT c.id,c.display,c.height,c.`date`, b.id_architecture FROM mysql_server a
            INNER JOIN link__architecture__mysql_server b ON a.id = b.id_mysql_server
            INNER JOIN architecture c ON c.id = b.id_architecture
            WHERE 1 ".$this->getFilter()." AND c.height > 8 GROUP BY c.id ORDER BY c.height DESC,c.width DESC ";


        $sql = "WITH LatestDot3Information AS (
    SELECT MAX(id_dot3_information) AS max_id_dot3_information
    FROM dot3_cluster
)
SELECT dg.*
FROM dot3_graph dg
JOIN dot3_cluster dc ON dg.id = dc.id_dot3_graph
JOIN LatestDot3Information ldi ON dc.id_dot3_information = ldi.max_id_dot3_information;";

        //@TODO c.height > 8   => to fix on register table architecture on Dot

        /*         * *
         * SELECT c.id,c.display FROM mysql_server a
          INNER JOIN link__architecture__mysql_server b ON a.id = b.id_mysql_server
          INNER JOIN architecture c ON c.id = b.id_architecture
          WHERE 1 GROUP BY c.id
         */
        
        $data['graphs'] = $db->sql_fetch_yield($sql);

        $this->set('data', $data);


        debug($data);
    }
}