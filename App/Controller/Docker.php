<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use Glial\Synapse\Controller;
use App\Library\Extraction;
use App\Library\Mysql;
use App\Library\Available;
use App\Library\Debug;
use Glial\Sgbd\Sgbd;
use App\Controller\Common;

use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;


class Docker extends Controller
{

    

    public function install()
    {
        //require apt install jq skopeo docker kubs ?

    }


    public function uninstall()
    {




    }


    public function getTag($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM docker_software ORDER by name";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res))
        {
            $cmd = "skopeo inspect docker://".$ob->name." | jq '.RepoTags'";
            $json = trim(shell_exec($cmd));

            $tags = json_decode($json);

            foreach($tags as $tag)
            {
                //echo "$tag \n";
                preg_match('/^(\d+\.\d+\.\d+)$/', $tag, $output_array);

                if (count($output_array) > 0)
                {
                    echo $ob->name." : $tag\n";

                    $sql = "INSERT IGNORE INTO docker_image (`id_docker_software`, `tag`) VALUES (".$ob->id.", '".$tag."');";
                    $db->sql_query($sql);
                }
            }
        }
    }


    public function getImage($param)
    {

        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT a.name, b.tag FROM docker_software a
        INNER JOIN docker_image b ON a.id=b.id_docker_software ORDER by a.name, b.tag";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res))
        {
            $ret = shell_exec("docker image pull ".$ob->name.":".$ob->tag);
            echo $ret."\n";

        }

    }



    public function index($param)
    {
        Debug::parseDebug($param);
        $data = array();
        $db = Sgbd::sql(DB_DEFAULT);

        $sql ="SELECT a.display_name, a.name, b.tag FROM docker_software a
        INNER JOIN docker_image b ON a.id=b.id_docker_software 
        ORDER by a.name,  
        CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(tag, '.', 1), '.', -1) AS UNSIGNED) ASC,
        CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(tag, '.', 2), '.', -1) AS UNSIGNED) ASC,
        CAST(SUBSTRING_INDEX(tag, '.', -1) AS UNSIGNED) ASC; ";


        //derniere version
        $sql = "SELECT 
        a.name,
        CONCAT(
          SUBSTRING_INDEX(SUBSTRING_INDEX(tag, '.', 2), '.', -2), 
          '.', 
          MAX(CAST(SUBSTRING_INDEX(tag, '.', -1) AS UNSIGNED))
        ) AS latest_patch
        FROM docker_software a
        INNER JOIN docker_image b ON a.id=b.id_docker_software 
      GROUP BY 
        a.name,
        SUBSTRING_INDEX(SUBSTRING_INDEX(tag, '.', 2), '.', -2)
        ORDER by a.name,  
        CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(tag, '.', 1), '.', -1) AS UNSIGNED) ASC,
        CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(tag, '.', 2), '.', -1) AS UNSIGNED) ASC,
        CAST(SUBSTRING_INDEX(tag, '.', -1) AS UNSIGNED) ASC;";


        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res))
        {
            $ret = shell_exec("docker image pull ".$ob->name.":".$ob->tag);
            echo $ret."\n";
            
        }


        $this->set('data', $data);

    }


    public function createInstance($param)
    {


        // 
    }


}