<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use \App\Library\Debug;

/**
 * Class responsible for tree workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
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
class Tree
{
/**
 * Stores `$db` for db.
 *
 * @var mixed
 * @phpstan-var mixed
 * @psalm-var mixed
 */
    var $db;
/**
 * Stores `$table_name` for table name.
 *
 * @var mixed
 * @phpstan-var mixed
 * @psalm-var mixed
 */
    var $table_name;
/**
 * Stores `$fields` for fields.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    var $fields  = array("id" => "id", "id_parent" => "id_parent", "bg" => "bg", "bd" => "bd");
/**
 * Stores `$options` for options.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    var $options = array(); // extra mapping

/**
 * Handle tree state through `__construct`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $db_link Input value for `db_link`.
 * @phpstan-param mixed $db_link
 * @psalm-param mixed $db_link
 * @param mixed $table_name Input value for `table_name`.
 * @phpstan-param mixed $table_name
 * @psalm-param mixed $table_name
 * @param mixed $fields Input value for `fields`.
 * @phpstan-param mixed $fields
 * @psalm-param mixed $fields
 * @param mixed $options Input value for `options`.
 * @phpstan-param mixed $options
 * @psalm-param mixed $options
 * @return void Returned value for __construct.
 * @phpstan-return void
 * @psalm-return void
 * @throws \Throwable When the underlying operation fails.
 * @see self::__construct()
 * @example /fr/tree/__construct
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function __construct($db_link, $table_name, $fields = array(), $options=array())
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

/**
 * Delete tree state through `delete`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $id Input value for `id`.
 * @phpstan-param mixed $id
 * @psalm-param mixed $id
 * @return void Returned value for delete.
 * @phpstan-return void
 * @psalm-return void
 * @see self::delete()
 * @example /fr/tree/delete
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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

        $this->removeaclfile();
    }

/**
 * Handle tree state through `extraWhere`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for extraWhere.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::extraWhere()
 * @example /fr/tree/extraWhere
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function extraWhere()
    {

        $extra = array();
        foreach ($this->options as $key => $val) {
            $extra[] = "`".$key."` = '".$val."'";
        }

        return " AND ".implode(" AND ", $extra);
    }

/**
 * Create tree state through `add`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $leaf Input value for `leaf`.
 * @phpstan-param mixed $leaf
 * @psalm-param mixed $leaf
 * @param int $id_parent Input value for `id_parent`.
 * @phpstan-param int $id_parent
 * @psalm-param int $id_parent
 * @return mixed Returned value for add.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::add()
 * @example /fr/tree/add
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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
        
        $this->removeaclfile();

        if (!$id_menu) {
            debug($this->db->sql_error());
            debug($menu);
            exit;
        }

        return $id_menu;
    }

/**
 * Handle tree state through `up`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $id Input value for `id`.
 * @phpstan-param mixed $id
 * @psalm-param mixed $id
 * @return void Returned value for up.
 * @phpstan-return void
 * @psalm-return void
 * @see self::up()
 * @example /fr/tree/up
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function up($id) // remonte d'un cran un item dans le menu sans effet dans l'arbre recursif
    {
        $bornes = $this->getInterval($id);

        $sql2 = "WITH a as (select `".$this->fields['bg']."` from `".$this->table_name."` where `".$this->fields['id']."`=".$id.") "
            ."SELECT b.`".$this->fields['bg']."`, b.`".$this->fields['bd']."` from `".$this->table_name."` b INNER JOIN a ON b.`".$this->fields['bd']."`=a.`".$this->fields['bg']."`-1;";


        debug($sql2);

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

/**
 * Handle tree state through `countFather`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $id Input value for `id`.
 * @phpstan-param mixed $id
 * @psalm-param mixed $id
 * @return mixed Returned value for countFather.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::countFather()
 * @example /fr/tree/countFather
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function countFather($id)
    {

        $bornes = $this->getInterval($id);

        $sql = "SELECT count(1) as cpt FROM `".$this->table_name."` WHERE `".$this->fields['bg']."` < ".$bornes['bg']." AND ".$bornes['bd']." < `".$this->fields['bd']."` ".$this->extraWhere();


        $res2 = $this->db->sql_query($sql);

        while ($ob = $this->db->sql_fetch_object($res2)) {
            return $ob->cpt;
        }
    }

/**
 * Retrieve tree state through `getInterval`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $id Input value for `id`.
 * @phpstan-param mixed $id
 * @psalm-param mixed $id
 * @return mixed Returned value for getInterval.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getInterval()
 * @example /fr/tree/getInterval
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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

/**
 * Retrieve tree state through `getfather`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $id Input value for `id`.
 * @phpstan-param mixed $id
 * @psalm-param mixed $id
 * @return mixed Returned value for getfather.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getfather()
 * @example /fr/tree/getfather
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getfather($id)
    {

        $sql = "SELECT * FROM `".$this->table_name."` WHERE `".$this->fields['id_parent']."` = ".$id."";
        $res = $this->db->sql_query($sql);

        $ret = array();
        while ($ob  = $this->db->sql_fetch_object($res)) {
            $ret['bg'] = $ob->{$this->fields['bg']};
            $ret['bd'] = $ob->{$this->fields['bd']};
            $ret['id'] = $ob->{$this->fields['id']};
        }

        return $ret;
    }

/**
 * Handle tree state through `left`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $id Input value for `id`.
 * @phpstan-param mixed $id
 * @psalm-param mixed $id
 * @return void Returned value for left.
 * @phpstan-return void
 * @psalm-return void
 * @see self::left()
 * @example /fr/tree/left
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function left($id)
    {
        $bornes = $this->getInterval($id);

        $current_father = $this->getfather($id);


        debug($current_father);

        $new_father = $this->getfather($current_father['id']);


        try {



            $sql = "BEGIN";
            $this->db->sql_query($sql);
            $sql = "UPDATE `".$this->table_name."` SET `".$this->fields['bg']."`=`".$this->fields['bg']."`-2 "
                ."WHERE  `".$this->fields['bg']."` > ".$bornes['bg']." AND `".$this->fields['bg']."` < ".$current_father['bd'];
            $this->db->sql_query($sql);

            $sql = "UPDATE `".$this->table_name."` SET `".$this->fields['bd']."`=`".$this->fields['bd']."`-2 "
                ."WHERE  `".$this->fields['bd']."` > ".$bornes['bd']." AND `".$this->fields['bd']."` <= ".$current_father['bd'];
            $this->db->sql_query($sql);


            $sql = "UPDATE `".$this->table_name."` SET `".$this->fields['bg']."`=".($current_father['bd'] - 1).",
            `".$this->fields['bg']."`=".($current_father['bd'] - 1).",
            `".$this->fields['id_parent']."`=".$new_father['id'].",";

            $this->db->sql_query("COMMIT;");
        } catch (Exception $ex) {
            $this->db->sql_query("ROLLBACK;");
        }
    }

/**
 * Delete tree state through `removeaclfile`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for removeaclfile.
 * @phpstan-return void
 * @psalm-return void
 * @see self::removeaclfile()
 * @example /fr/tree/removeaclfile
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function removeaclfile()
    {
        if (file_exists($_SERVER["DOCUMENT_ROOT"].WWW_ROOT."tmp/acl/acl.ser")) {
            unlink($_SERVER["DOCUMENT_ROOT"].WWW_ROOT."tmp/acl/acl.ser");
        }
    }

/**
 * Retrieve tree state through `getFirstFather`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $id Input value for `id`.
 * @phpstan-param mixed $id
 * @psalm-param mixed $id
 * @return void Returned value for getFirstFather.
 * @phpstan-return void
 * @psalm-return void
 * @see self::getFirstFather()
 * @example /fr/tree/getFirstFather
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getFirstFather($id)
    {


        $sql = "SELECT id, parent_id, bg, bd, title FROM `menu` WHERE `bg` < 71 AND 72 < `bd` and group_id = 1 order by `bg` desc limit 1;
";
    }
}

