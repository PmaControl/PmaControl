<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use \Glial\Sgbd\Sgbd;

/**
 * Class responsible for util workflows.
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
class Util
{
/**
 * Stores `$server` for server.
 *
 * @var mixed
 * @phpstan-var mixed
 * @psalm-var mixed
 */
    static $server;

/**
 * Retrieve util state through `getFilter`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param int $id_mysql_server Input value for `id_mysql_server`.
 * @phpstan-param int $id_mysql_server
 * @psalm-param int $id_mysql_server
 * @param mixed $alias Input value for `alias`.
 * @phpstan-param mixed $alias
 * @psalm-param mixed $alias
 * @return mixed Returned value for getFilter.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getFilter()
 * @example /fr/util/getFilter
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static private function getFilter($id_mysql_server = array(), $alias = 'a')
    {

        $where = "";
        if (!empty($_GET['environment']['libelle'])) {
            $environment = $_GET['environment']['libelle'];
        }
        if (!empty($_SESSION['environment']['libelle']) && empty($_GET['environment']['libelle'])) {
            $environment                    = $_SESSION['environment']['libelle'];
            $_GET['environment']['libelle'] = $environment;
        }

        if (!empty($_SESSION['client']['libelle'])) {
            $client = $_SESSION['client']['libelle'];
        }
        if (!empty($_GET['client']['libelle']) && empty($_GET['client']['libelle'])) {
            $client                    = $_GET['client']['libelle'];
            $_GET['client']['libelle'] = $client;
        }

        if (!empty($environment)) {
            $where .= " AND `".$alias."`.id_environment IN (".implode(',', json_decode($environment, true)).")";
        }
        if (!empty($client)) {
            $where .= " AND `".$alias."`.id_client IN (".implode(',', json_decode($client, true)).")";
        }

        if (!empty($id_mysql_server)) {
            $where .= " AND `".$alias."`.id IN (".implode(',', $id_mysql_server).") ";
        }

        return $where;
    }

/**
 * Retrieve util state through `getServer`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param int $id_mysql_server Input value for `id_mysql_server`.
 * @phpstan-param int $id_mysql_server
 * @psalm-param int $id_mysql_server
 * @return mixed Returned value for getServer.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getServer()
 * @example /fr/util/getServer
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function getServer($id_mysql_server = 0)
    {


        if (empty(self::$server)) {
            $db = Sgbd::sql(DB_DEFAULT);


            $sql = "SELECT a.*,d.libelle, d.class FROM mysql_server a
            INNER JOIN environment d on d.id = a.id_environment
            WHERE 1=1 ".self::getFilter();

            $res = $db->sql_query($sql);

            $server = array();
            while ($arr    = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $server[$arr['id']] = $arr;

                $server[$arr['id']]['link'] = '<span class="label label-'.$arr['class'].'">'
                    .substr($arr['libelle'], 0, 1).'</span> '
                    .' <a href="gff">'.$arr['display_name'].'</a>';
            }

            self::$server = $server;
        }

        if (!empty($id_mysql_server)) {
            if (!empty(self::$server[$id_mysql_server])) {
                return self::$server[$id_mysql_server];
            }
        }

        return $server;
    }
    /*
     * 
     * Retourne le nom de la classe sans l'espace de nom
     * 
     */

    static public function getController($class)
    {
        $elems = explode('\\', $class);
        return end($elems);
    }
}
