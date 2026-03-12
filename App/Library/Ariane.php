<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

/**
 * Class responsible for ariane workflows.
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
class Ariane
{
/**
 * Stores `$db` for db.
 *
 * @var mixed
 * @phpstan-var mixed
 * @psalm-var mixed
 */
    private $db;
/**
 * Stores `$class` for class.
 *
 * @var mixed
 * @phpstan-var mixed
 * @psalm-var mixed
 */
    private $class;
/**
 * Stores `$method` for method.
 *
 * @var mixed
 * @phpstan-var mixed
 * @psalm-var mixed
 */
    private $method;
/**
 * Stores `$title` for title.
 *
 * @var mixed
 * @phpstan-var mixed
 * @psalm-var mixed
 */
    private $title;

/**
 * Handle ariane state through `__construct`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $db Input value for `db`.
 * @phpstan-param mixed $db
 * @psalm-param mixed $db
 * @return void Returned value for __construct.
 * @phpstan-return void
 * @psalm-return void
 * @see self::__construct()
 * @example /fr/ariane/__construct
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function __construct($db)
    {
        $this->db = $db;
    }

/**
 * Handle ariane state through `buildAriane`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $method Input value for `method`.
 * @phpstan-param mixed $method
 * @psalm-param mixed $method
 * @param mixed $title Input value for `title`.
 * @phpstan-param mixed $title
 * @psalm-param mixed $title
 * @return mixed Returned value for buildAriane.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::buildAriane()
 * @example /fr/ariane/buildAriane
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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

            $title = __($ob->title);

            if (!empty($ob->url)) {
                $ariane[] = '<a href="'.str_replace("{LINK}", LINK, $ob->url).'">'.$ob->icon.' '.$title.'</a>';
            } else {
                $ariane[] = $ob->icon.' '.$title;
            }
            $ariane2[] = $ob->icon.' '.$title;
            $title     = $ob->icon.' '.$title;
            $count++;
        }

        if ($method != 'error_web::error404') {
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
