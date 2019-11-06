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

        $res = $this->db->sql_query($sql);

        $ariane  = array();
        $ariane2 = array();
        $count   = 0;

        while ($ob = $this->db->sql_fetch_object($res)) {


            if (!empty($ob->url)) {
                $ariane[] = '<a href="'.str_replace("{LINK}", LINK, $ob->url).'">'.$ob->icon.' '.$ob->title.'</a>';
            } else {
                $ariane[] = $ob->icon.' '.$ob->title;
            }
            $ariane2[] = $ob->icon.' '.$ob->title;
            $title     = $ob->icon.' '.$ob->title;
            $count++;
        }

        if ($method != 'error_web::error404')
        {
            if ($count == 0) {
                //TODO add to log
                //set_flash("error", "Error 501", "Menu error : No menu entry for ".$method.". ");
            } else {
                $ariane[$count - 1] = $ariane2[$count - 1];
            }
        }

        $elems['ariane'] = "";
        if (count($ariane) > 1) {
            $elems['ariane'] = implode(" > ", $ariane);
        }

        $elems['title'] = preg_replace('/style="font-size:[0-9]+px"/', '', $title);

        return $elems;
    }
}