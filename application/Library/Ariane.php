<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

class Ariane
{
    private $db;
    private $class;
    private $method;
    private $title;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function buildAriane($method, $title = "")
    {

        $ret = explode('::', $method);

        $this->class  = strtolower($ret[0]);
        $this->method = strtolower($ret[1]);
        $this->title  = $title;


        $sql = "WITH a as (SELECT bg,bd, group_id FROM menu where `class`='".$this->class."' AND `method` = '".$this->method."' LIMIT 1)
            SELECT * FROM menu b,a WHERE b.bg <= a.bg AND b.bd >= a.bg AND a.group_id = b.group_id ORDER by b.bg";

        //en attendant de migrer tout le monde en 10.2
        /*$sql = "
            SELECT * FROM menu b WHERE b.active = 1 AND b.bg <= (SELECT bg FROM menu where `class`='".$this->class."' AND `method` = '".$this->method."' AND group_id = 1 LIMIT 1) "
            ."AND b.bd >= (SELECT bd FROM menu where `class`='".$this->class."' AND `method` = '".$this->method."' AND group_id = 1 LIMIT 1) AND group_id = 1 ORDER by b.bg";
        */
        $res = $this->db->sql_query($sql);

        $ariane = array();
        $ariane2 = array();
        $count=0;
        
        while ($ob     = $this->db->sql_fetch_object($res)) {
            $ariane[] = '<a href="'.str_replace("{LINK}", LINK, $ob->url).'">'.$ob->icon.' '.$ob->title.'</a>';
            $ariane2[] = $ob->icon.' '.$ob->title;
            $count++;
        }

        if ($count==0)
        {
            throw new \Exception("PMACTRL-500 : Menu error (".$method.") ");
        }
        
        $ariane[$count-1]=$ariane2[$count-1];
        return $ariane;
    }
    
    /*
    public function cleanupArianeLastEntry($Ariane)
    {
        $LastKey = "";
        $LastValue = "";
        foreach($Ariane AS $Key => $Value)
        {
            $LastKey = $Key;
            $LastValue = $Value;
        }

        if (strpos($Value,'>')) {
            $Ariane[$LastKey] = substr($Value,strpos($Value,'>')+1,strpos($Value,'>')+1-strpos($Value,'<',1));
        }

        return $Ariane;
    }

     */
}