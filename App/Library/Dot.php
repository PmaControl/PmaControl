<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use \Glial\Sgbd\Sgbd;


/**
 * Trait responsible for dot workflows.
 *
 * This trait belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
trait Dot
{

/**
 * Handle dot state through `generateAllGraph`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for generateAllGraph.
 * @phpstan-return void
 * @psalm-return void
 * @see self::generateAllGraph()
 * @example /fr/dot/generateAllGraph
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function generateAllGraph()
    {
        foreach ($this->groups['groups'] as $group) {

            //debug($group);
            $code2D = $this->generateGraph($group);
            $view2D = $this->getRenderer($code2D);
            $this->saveCache($code2D, $view2D, $group);
        }
    }



/**
 * Handle dot state through `generateGraph`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $group Input value for `group`.
 * @phpstan-param mixed $group
 * @psalm-param mixed $group
 * @return mixed Returned value for generateGraph.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::generateGraph()
 * @example /fr/dot/generateGraph
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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
        $graph .= '}';

        return $graph;
    }

/**
 * Handle dot state through `nodeHead`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $display_name Input value for `display_name`.
 * @phpstan-param mixed $display_name
 * @psalm-param mixed $display_name
 * @param int $id_mysql_server Input value for `id_mysql_server`.
 * @phpstan-param int $id_mysql_server
 * @psalm-param int $id_mysql_server
 * @return mixed Returned value for nodeHead.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::nodeHead()
 * @example /fr/dot/nodeHead
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function nodeHead($display_name, $id_mysql_server)
    {
        $backup = '&#x2610;';
        
        
        /*if (in_array($id_mysql_server, $this->getServerBackuped($id_mysql_server))) {
            $backup = '&#x2611;';
        }*/

        $line = '<tr><td bgcolor="black" color="white" align="center"><font color="white">'.$display_name.' '.$backup.'</font></td></tr>';
        return $line;
    }

/**
 * Handle dot state through `nodeLine`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $line Input value for `line`.
 * @phpstan-param mixed $line
 * @psalm-param mixed $line
 * @return mixed Returned value for nodeLine.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::nodeLine()
 * @example /fr/dot/nodeLine
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function nodeLine($line)
    {
        $line = '<tr><td bgcolor="lightgrey" align="left">'.$line.'</td></tr>';
        return $line;
    }


/**
 * Handle dot state through `generateNode`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param int $id_mysql_server Input value for `id_mysql_server`.
 * @phpstan-param int $id_mysql_server
 * @psalm-param int $id_mysql_server
 * @return mixed Returned value for generateNode.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::generateNode()
 * @example /fr/dot/generateNode
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function generateNode($id_mysql_server)
    {
        if (in_array($id_mysql_server, $this->graph_arbitrator)) {
            $data = $this->generateArbitrator($id_mysql_server);
        } else {
            $data = $this->generateServer($id_mysql_server);
        }

        return $data;
    }

/**
 * Handle dot state through `generateRankForMM`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $group Input value for `group`.
 * @phpstan-param mixed $group
 * @psalm-param mixed $group
 * @return mixed Returned value for generateRankForMM.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::generateRankForMM()
 * @example /fr/dot/generateRankForMM
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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
