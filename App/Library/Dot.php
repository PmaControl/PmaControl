<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use \Glial\Sgbd\Sgbd;


trait Dot
{

    public function generateAllGraph()
    {
        foreach ($this->groups['groups'] as $group) {

            //debug($group);
            $code2D = $this->generateGraph($group);
            $view2D = $this->getRenderer($code2D);
            $this->saveCache($code2D, $view2D, $group);
        }
    }



    public function generateGraph($group)
    {

//label=\"Step 2\";
        $graph = "digraph PmaControl {";
        $graph .= "rankdir=LR; splines=line;"; //ortho  =>  Try using xlabels
        $graph .= " graph [fontname = \"helvetica\" ];
 node [fontname = \"helvetica\"];
 edge [fontname = \"helvetica\"];
 node [shape=rect style=filled fontsize=8 fontname=\"arial\" ranksep=0 concentrate=true splines=true overlap=false];\n";

        // nodesep=0



        foreach ($group as $id_mysql_server) {
            $graph .= $this->generateNode($id_mysql_server);
        }
//cas des multi master

        $graph .= $this->generateRankForMM($group);
        $graph .= $this->generateEdge($group);
        $graph .= $this->generateGaleraCluster($group);

        /*

          if ($this->sst) {
          $graph .= $this->generateEdgeSst();
          }
         */

//$graph .= $this->getHaProxy($list_id);
//        $graph .= $gg;
//        $graph .= $gg2;


        $graph .= '}';

        return $graph;
    }

    private function nodeHead($display_name, $id_mysql_server)
    {
        $backup = '&#x2610;';
        
        
        /*if (in_array($id_mysql_server, $this->getServerBackuped($id_mysql_server))) {
            $backup = '&#x2611;';
        }*/

        $line = '<tr><td bgcolor="black" color="white" align="center"><font color="white">'.$display_name.' '.$backup.'</font></td></tr>';
        return $line;
    }

    private function nodeLine($line)
    {
        $line = '<tr><td bgcolor="lightgrey" align="left">'.$line.'</td></tr>';
        return $line;
    }


    public function generateNode($id_mysql_server)
    {
        if (in_array($id_mysql_server, $this->graph_arbitrator)) {
            $data = $this->generateArbitrator($id_mysql_server);
        } else {
            $data = $this->generateServer($id_mysql_server);
        }

        return $data;
    }

    public function generateRankForMM($group)
    {
        $graph = '';

        foreach ($this->graph_master_master as $key => $mastermaster) {
            foreach ($mastermaster as $id_server) {
                if (in_array($id_server, $group)) {
                    $graph .= "{rank = same; ".implode(";", $mastermaster).";}\n";
                    break;
                }
            }
        }

        return $graph;
    }
}