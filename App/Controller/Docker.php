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
        Debug::parseDebug(param: $param);

        $db = Sgbd::sql(DB_DEFAULT);


        $sql = "SELECT * FROM docker_software ORDER by name DESC";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res))
        {
          

            $cmd = "skopeo inspect docker://".$ob->name." | jq '.RepoTags'";
            Debug::debug($cmd, "cmd");
            $json = trim(shell_exec($cmd));

            $tags = json_decode($json);

            Debug::debug(string: $ob, var: "Elems");

            foreach($tags as $tag)
            {
               
                //echo "$tag \n";
                preg_match('/^(\d+\.\d+\.\d+)$/', $tag, $output_array);

                if (count($output_array) > 0)
                {
                    echo $ob->name." : $tag\n";

                    $sql2 = "SELECT count(1) as cpt FROM docker_image WHERE id_docker_software=".$ob->id." AND tag =  '".$tag."'";
                    Debug::sql($sql2);
                    $res2 = $db->sql_query($sql2);

                    while($ob2 = $db->sql_fetch_object($res2))
                    {
                        if ($ob2->cpt == "0")
                        {
                            $sql = "INSERT INTO docker_image (`id_docker_software`, `tag`) VALUES (".$ob->id.", '".$tag."');";
                            Debug::sql($sql);
                            $db->sql_query($sql);
                        }
                    }
                }
            }
        }
    }


    public function getImage($param)
    {

        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT a.name, b.tag FROM docker_software a
        INNER JOIN docker_image b ON a.id=b.id_docker_software WHERE b.sha256 != '' ORDER by a.name DESC, b.tag";
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
        a.display_name, a.name,b.tag,a.color, a.background,
        CONCAT(
          SUBSTRING_INDEX(SUBSTRING_INDEX(tag, '.', 2), '.', -2), 
          '.', 
          MAX(CAST(SUBSTRING_INDEX(tag, '.', -1) AS UNSIGNED))
        ) AS latest_version, 
        SUBSTRING_INDEX(SUBSTRING_INDEX(tag, '.', 2), '.', -2) as main,
        
        GROUP_concat(tag) as all_version

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

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC))
        {
            $data['image'][] = $arr;
        }

        $sql = "SELECT name, tag, sha256 FROM docker_software a
        INNER JOIN docker_image b ON a.id=b.id_docker_software";
        $res = $db->sql_query($sql);

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC))
        {
            $data['tag'][strtolower($arr['name'])][$arr['tag']] = $arr['sha256'];
        }

        $this->set('data', $data);

    }


    public function createInstance($param)
    {


        // 
    }


    public function linkTagAndImage($param)
    {
        
        Debug::parseDebug($param);
    
        $db = Sgbd::sql(DB_DEFAULT);

        $ls = "docker image ls";


        $result = shell_exec($ls);

        $lines = explode("\n", $result);

        Debug::debug($lines);

        foreach($lines as $input_line )
        {
            $output_array = array();
            preg_match('/(\S+)\s+(\d+\.\d+\.\d+)\s+([a-z0-9]{12}).*\s+(\d+[KGMB]{2})$/', $input_line, $output_array);

            Debug::debug($output_array);

            if (count($output_array) > 0)
            {

                $sql ="UPDATE docker_image a
                INNER JOIN docker_software b ON a.id_docker_software = b.id SET sha256='".$output_array[3]."', size='".$output_array[4]."' 
                WHERE b.name='".$output_array[1]."' AND a.tag='".$output_array[2]."'";

                Debug::sql($sql);

                $db->sql_query($sql);
            }
        }
    }

}