<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

class Tree
{

    use \App\Library\Debug;
    var $db;
    var $table_name;
    var $fields  = array("id" => "id", "id_parent" => "id_parent", "bg" => "bg", "bd" => "bd");
    var $options = array(); // extra mapping

    public function __construct($db_link, $table_name, $fields = array(), $options)
    {
        $this->db         = $db_link;
        $this->table_name = $table_name;

        $keys = array_keys($fields);

        foreach ($keys as $key) {

            if (array_key_exists($key, $fields) === false) {
                throw new \Exception("PMACTRL-061 : This key is not valid : '".$key."'");
            }
        }

        $this->fields = array_replace($this->fields, $fields);

        $this->options = $options;
    }

    public function delete($id)
    {
        $sql = "SELECT * FROM `".$this->table_name."` WHERE `".$this->fields['id']."`='".$id."'";

        $ob       = $this->db->sql_fetch_object($this->db->sql_query($sql));
        $interval = $ob->{$this->fields['bd']} - $ob->{$this->fields['bg']} + 1;

        $sql2 = "DELETE FROM `".$this->table_name."` WHERE `".$this->fields['bg']."` >= ".$ob->{$this->fields['bg']}." 
            AND `".$this->fields['bd']."` <= '".$ob->{$this->fields['bd']}."'".$this->extraWhere();

        $this->db->sql_query($sql2);


        $sql3 = "UPDATE `".$this->table_name."` SET `".$this->fields['bd']."` = `".$this->fields['bd']."` - ".$interval."
            WHERE `".$this->fields['bd']."` >= ".$ob->{$this->fields['bd']}.$this->extraWhere();

        $this->db->sql_query($sql3);


        $sql4 = "UPDATE `".$this->table_name."` SET `".$this->fields['bg']."` = `".$this->fields['bg']."` - ".$interval."
            WHERE `".$this->fields['bg']."` >= ".$ob->{$this->fields['bg']}.$this->extraWhere();

        $this->db->sql_query($sql4);
    }

    private function extraWhere()
    {

        $extra = array();
        foreach ($this->options as $key => $val) {
            $extra[] = "`".$key."` = '".$val."'";
        }

        return " AND ".implode(" AND ", $extra);
    }

    public function add($leaf, $id_parent = NULL)
    {

        if ($id_parent === "NULL") {
            $bg        = 1;
            $bd        = 2;
            $id_parent = NULL;
        } else {
            $sql = "SELECT * FROM `".$this->table_name."` WHERE `".$this->fields['id']."` = ".$this->db->sql_real_escape_string($id_parent);

            $res = $this->db->sql_query($sql);

            while ($ob = $this->db->sql_fetch_object($res)) {
                $bg        = $ob->{$this->fields['bg']};
                $bd        = $ob->{$this->fields['bd']};
                $id_parent = $ob->{$this->fields['id']};
            }

            $sql2 = "UPDATE `".$this->table_name."` SET `".$this->fields['bd']."` = `".$this->fields['bd']."` + 2 WHERE `".$this->fields['bd']."` >= '".$bd."'";
            $this->db->sql_query($sql2);

            $sql3 = "UPDATE `".$this->table_name."` SET `".$this->fields['bg']."` = `".$this->fields['bg']."` + 2 WHERE `".$this->fields['bg']."` >= '".$bd."'";
            $this->db->sql_query($sql3);


            $bg = $bd;
            $bd = $bd + 1;
        }

        $menu[$this->table_name][$this->fields['bg']] = $bg;
        $menu[$this->table_name][$this->fields['bd']] = $bd;

        if ($id_parent !== NULL) {
            $menu[$this->table_name][$this->fields['id_parent']] = $id_parent;
        }

        foreach ($leaf as $key => $val) {
            $menu[$this->table_name][$key] = $val;
        }

        $id_menu = $this->db->sql_save($menu);


        if (!$id_menu) {
            debug($this->db->sql_error());
            debug($menu);
            exit;
        }
    }

    public function up($id) // remonte d'un cran un item dans le menu sans effet dans l'arbre recursif
    {
        $bornes = $this->getInterval($id);

        $sql2 = "WITH a as (select `".$this->fields['bg']."` from `".$this->table_name."` where `".$this->fields['id']."`=".$id.") "
            ."SELECT b.`".$this->fields['bg']."`, b.`".$this->fields['bd']."` from `".$this->table_name."` b INNER JOIN a ON b.`".$this->fields['bd']."`=a.`".$this->fields['bg']."`-1;";


        $res2 = $this->db->sql_query($sql2);

        while ($ob2 = $this->db->sql_fetch_object($res2)) {
            $bg_d = $ob2->{$this->fields['bg']};
            $bd_d = $ob2->{$this->fields['bd']};
        }

        $ofset     = $bornes['bd'] - $bornes['bg'] + 1;
        $ofset2    = $bd_d - $bg_d + 1;
        $ofset_all = $ofset + $ofset2;


        // création d'un espace vide pour y mettre le nouveau sous tableau
        $sql = "UPDATE `".$this->table_name."` SET `".$this->fields['bd']."` = `".$this->fields['bd']."` + $ofset WHERE `".$this->fields['bd']."` >= ".$bg_d."";
        $this->db->sql_query($sql);

        $sql = "UPDATE `".$this->table_name."` SET `".$this->fields['bg']."` = `".$this->fields['bg']."` + $ofset WHERE `".$this->fields['bg']."` >= ".$bg_d."";
        $this->db->sql_query($sql);
        /**/


        //déplacement du tableau du dessous pour le mettre dans le trou que l'on vient de faire
        $sql = "UPDATE `".$this->table_name."` 
            SET `".$this->fields['bd']."` = `".$this->fields['bd']."` - ".$ofset_all.",
            `".$this->fields['bg']."` = `".$this->fields['bg']."` - ".$ofset_all."
            WHERE `".$this->fields['bg']."` >= ".($bornes['bg'] + $ofset)." AND `".$this->fields['bd']."` <= ".($bornes['bd'] + $ofset)."";
        $this->db->sql_query($sql);
        /**/


        //comblement du vide crée qui se retrouve à la fin de l'inversion
        $sql = "UPDATE `".$this->table_name."` SET `".$this->fields['bd']."` = `".$this->fields['bd']."` - $ofset
         WHERE `".$this->fields['bd']."` >= ".($bornes['bd'] + $ofset)."";
        $this->db->sql_query($sql);


        $sql = "UPDATE `".$this->table_name."` SET `".$this->fields['bg']."` = `".$this->fields['bg']."` - $ofset
        WHERE `".$this->fields['bg']."` >= ".($bornes['bd'] + $ofset)."";
        $this->db->sql_query($sql);
        /*         * */
    }

    public function countFather($id)
    {

        $bornes = $this->getInterval($id);

        $sql = "SELECT count(1) as cpt FROM `".$this->table_name."` WHERE `".$this->fields['bg']."` < ".$bornes['bg']." AND ".$bornes['bd']." < `".$this->fields['bd']."` ".$this->extraWhere();


        $res2 = $this->db->sql_query($sql);

        while ($ob = $this->db->sql_fetch_object($res2)) {
            return $ob->cpt;
        }
    }

    private function getInterval($id)
    {
        $sql = "SELECT * FROM `".$this->table_name."` WHERE `".$this->fields['id']."` =".$id;
        $res = $this->db->sql_query($sql);

        $ret = [];

        while ($ob = $this->db->sql_fetch_object($res)) {
            $ret['bg'] = $ob->{$this->fields['bg']};
            $ret['bd'] = $ob->{$this->fields['bd']};
        }

        return $ret;
    }


    public function getfather($id)
    {

        $sql = "SELECT * FROM `".$this->table_name."` WHERE `".$this->fields['id_parent']."` = ".$id."";
        $res = $this->db->sql_query($sql);

        $ret = array();
        while ($ob = $this->db->sql_fetch_object($res)) {
            $ret['bg'] = $ob->{$this->fields['bg']};
            $ret['bd'] = $ob->{$this->fields['bd']};
            $ret['id'] = $ob->{$this->fields['id']};
        }

        return $ret;

    }

    public function left($id)
    {
        $bornes = $this->getInterval($id);

        $current_father = $this->getfather($id);
        $new_father = $this->getfather($current_father['id']);

        $sql = "UPDATE `".$this->table_name."` SET `".$this->fields['bg']."`";

        
    }

    public function getFirstFather($id)
    {


        $sql = "SELECT id, parent_id ,bg,bd,title  FROM `menu` WHERE `bg` < 71 AND 72 < `bd` and group_id = 1 order by `bg` desc limit 1;";
    }
}