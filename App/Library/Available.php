<?php

namespace App\Library;

use App\Library\Extraction;
class Available
{
    const MYSQL_AVAILABLE = "mysql_available";
    static array $mysql_available = array();
    public static function getMySQL($id_mysql_server = 0)
    {
        if (empty(self::$mysql_available))
        {
            $servers = Extraction::display(array(self::MYSQL_AVAILABLE));

            foreach($servers as $id_server => $server){
                if ($server[''][self::MYSQL_AVAILABLE] === "1"){
                    self::$mysql_available[] = $id_server;
                }
            }
        }

        if ($id_mysql_server === 0){
            //add fake id=0 to prevent no one server available
            $list = "0,".implode(',' ,self::$mysql_available);
            return $list;
        }
        else{
            if (in_array($id_mysql_server, self::$mysql_available)){
                return true;
            }
            else {
                return false;
            }
        }
    }
}