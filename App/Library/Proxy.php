<?php

namespace App\Library;

use \Glial\Sgbd\Sgbd;

/**
 * Class responsible for proxy workflows.
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
class Proxy
{

/**
 * Retrieve proxy state through `getDbLink`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param int $id_proxysql_server Input value for `id_proxysql_server`.
 * @phpstan-param int $id_proxysql_server
 * @psalm-param int $id_proxysql_server
 * @return mixed Returned value for getDbLink.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::getDbLink()
 * @example /fr/proxy/getDbLink
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function getDbLink($id_proxysql_server)
    {

        if (!is_int(intval($id_proxysql_server))) {
            throw new \Exception("PMACTRL-856 : first parameter, id_proxysql_server should be an int (".$id_proxysql_server.") !");
        }

        $dblink = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT id from proxysql_server where id=".$id_proxysql_server.";";
        $res = $dblink->sql_query($sql);

        while ($ob = $dblink->sql_fetch_object($res)) {
            return Sgbd::sql("proxysql_".$ob->id);
        }

        throw new \Exception("PMACTRL-854 : impossible to find the server ProxySQL with id '".$id_proxysql_server."'");
    }
}
