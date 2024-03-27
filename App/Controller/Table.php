<?php

namespace App\Controller;

use App\Library\Graphviz;
use Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use \App\Library\Debug;
use \App\Library\Table as Tablelib;

class Table extends Controller {

    static $table_number = 1;

    static $tables = array();
    static $edges = array();

    function mpd($param) {

        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $table_schema = $param[1];
        $table_name = $param[2];
        $default = Sgbd::sql(DB_DEFAULT);

        Debug::debug($param);

        $data = array();
        $data['param'] = $param;

        $sql = "SELECT id_mysql_server, constraint_schema,constraint_table, constraint_column, referenced_schema, referenced_table, referenced_column
        FROM `foreign_key_real`
        WHERE `id_mysql_server` = ".$id_mysql_server." AND `referenced_schema` = '".$table_schema."' 
        AND (`referenced_table` = '".$table_name."' OR `constraint_table` = '".$table_name."')
        UNION
        SELECT id_mysql_server, constraint_schema,constraint_table, constraint_column, referenced_schema, referenced_table, referenced_column
        FROM `foreign_key_virtual`
        WHERE `id_mysql_server` = ".$id_mysql_server." AND `referenced_schema` = '".$table_schema."' 
        AND (`referenced_table` = '".$table_name."' OR `constraint_table` = '".$table_name."')
        ";

/*
        $sql = "SELECT id_mysql_server, constraint_schema,constraint_table, constraint_column, referenced_schema, referenced_table, referenced_column
        FROM `foreign_key_real`
        WHERE `id_mysql_server` = ".$id_mysql_server." AND `referenced_schema` = '".$table_schema."' 
        
        UNION
        SELECT id_mysql_server, constraint_schema,constraint_table, constraint_column, referenced_schema, referenced_table, referenced_column
        FROM `foreign_key_virtual`
        WHERE `id_mysql_server` = ".$id_mysql_server." AND `referenced_schema` = '".$table_schema."'   
        ";
*/
        $this->getElemeFromQuery($sql);

        Debug::sql($sql);

        $graph = "";
        $graph .= Graphviz::generateStart(array());

        foreach(self::$tables as $id_mysql_server => $databases) {
            foreach($databases as $table_schema => $tables ){
                foreach($tables as $table_name2 => $field){
                    $graph .= Graphviz::generateTable(array($id_mysql_server,$table_schema, $table_name2),$field['field']);
                }
            }
        }

        // edge
        foreach(self::$edges as $edge){
            $graph .= Graphviz::generateEdge($edge);
        }
        
        $graph .= Graphviz::generateEnd(array());

        $data['debug'] = $graph;


        $data['graph'] = Graphviz::generateDot($id_mysql_server."-".$table_schema."-".$table_name, $graph);

        $data['table_schema'] = $table_schema;
        $data['table_name'] = $table_name;

        $this->set('data', $data);
    }

    public function getListFieldToUnderline($table_info)
    {
        $fields = array();

        foreach($table_info as $table)
        {
            $fields[$table['constraint_schema']][$table['constraint_table']][] = $table['constraint_column'];
            $fields[$table['constraint_schema']][$table['constraint_table']] = array_unique($fields[$table['constraint_schema']][$table['constraint_table']]);
        }

        return $fields;
    }

    public function getElemeFromQuery($sql)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $res = $db->sql_query($sql);

        while($arr = $db->sql_fetch_array($res , MYSQLI_ASSOC))
        {
            self::$tables[$arr['id_mysql_server']][$arr['constraint_schema']][$arr['constraint_table']]['field'][$arr['constraint_column']]['color'] = Graphviz::getColor($arr['referenced_table']);
            self::$tables[$arr['id_mysql_server']][$arr['referenced_schema']][$arr['referenced_table']]['field'][$arr['referenced_column']]['color'] = Graphviz::getColor($arr['referenced_table']);

            $pos1 = Tablelib::findFieldPosition(array($arr['id_mysql_server'], $arr['constraint_schema'], $arr['constraint_table'], $arr['constraint_column']));
            $pos2 = Tablelib::findFieldPosition(array($arr['id_mysql_server'], $arr['referenced_schema'], $arr['referenced_table'], $arr['referenced_column']));

            $tmp = array();
            $tmp['tooltip'] = $arr['constraint_table'].".".$arr['constraint_column']." => ".$arr['referenced_table'].".".$arr['referenced_column'];
            $tmp['arrow'] = $arr['constraint_table'].":d".$pos1." -> ".$arr['referenced_table'].":a".$pos2;
            $tmp['color'] = Graphviz::getColor($arr['referenced_table']);
            self::$edges[] = $tmp;
        }
    }
}
