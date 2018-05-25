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


        $sql = "WITH a as (SELECT bg,bd FROM menu where `class`='".$this->class."' AND `method` = '".$this->method."' AND group_id = 1 LIMIT 1)
            SELECT * FROM menu b,a WHERE b.active = 1 AND b.bg <= a.bg AND b.bd >= a.bg AND group_id = 1 ORDER by b.bg";

        //en attendant de migrer tout le monde en 10.2
        $sql = "
            SELECT * FROM menu b WHERE b.active = 1 AND b.bg <= (SELECT bg FROM menu where `class`='".$this->class."' AND `method` = '".$this->method."' AND group_id = 1 LIMIT 1) "
            ."AND b.bd >= (SELECT bd FROM menu where `class`='".$this->class."' AND `method` = '".$this->method."' AND group_id = 1 LIMIT 1) AND group_id = 1 ORDER by b.bg";

        $res = $this->db->sql_query($sql);

        $ariane = array();
        while ($ob     = $this->db->sql_fetch_object($res)) {
            $ariane[] = '<a href="'.str_replace("{LINK}", LINK, $ob->url).'">'.$ob->icon.' '.$ob->title.'</a>';
        }

        return $ariane;
    }
}