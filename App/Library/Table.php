<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

class Table
{

    static $table = array();
    /*
     * (PmaControl 2.0.64)<br/>
     * @author Aurélien LEQUOY, <aurelien.lequoy@68koncept.com>
     * @return boolean Success
     * @package Controller
     * @since 2.0.64  First time this was introduced.
     * @since pmacontrol 2.0.64 
     * @description test if daemon is launched or not according with pid saved in table daemon_main
     * @access public
     *
     */

    static public function getTableDefinition($param)
    {
        Debug::parseDebug($param);
        
        $id_mysql_server = $param[0];
        $table_schema = $param[1];
        $table_name = $param[2];

        if (empty(self::$table[$id_mysql_server][$table_schema][$table_name]))
        {
            $db = Mysql::getDbLink($id_mysql_server);

            $sql = "DESCRIBE `".$table_schema."`.`".$table_name."`;";
            Debug::sql($sql);

            $res = $db->sql_query($sql);

            while($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                self::$table[$id_mysql_server][$table_schema][$table_name][] = $arr;
            }
            
            Debug::debug(self::$table[$id_mysql_server][$table_schema][$table_name], "Table $table_schema.$table_name");
        }

        return self::$table[$id_mysql_server][$table_schema][$table_name];
    }

    static public function findFieldPosition($param)
    {
        Debug::parseDebug($param);
        
        //$id_mysql_server = $param[0];
        //$table_schema = $param[1];
        //$table_name = $param[2];
        $field_name = $param[3];

        $def = self::getTableDefinition($param);

        $i=1;
        foreach($def as $row) {   
            if ($row['Field'] === $field_name) {
                Debug::debug($i, "Position of $field_name");
                return $i;
            }
            $i++;
        }
    }

}