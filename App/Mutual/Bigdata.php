<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Mutual;

trait Bigdata
{
    use \App\Library\Filter;


    private function buildQuery($fields, $table_name, $id_mysql_server = 0)
    {
        $sql = 'select a.ip, a.port, a.id, a.name,';

        $i   = 0;
        $tmp = [];
        foreach ($fields as $field) {
            $tmp[] = " c$i.value as $field";
            $i++;
        }

        $sql .= implode(",", $tmp);
        $sql .= " from mysql_server a ";
        $sql .= " INNER JOIN ".$table_name."_max_date b ON a.id = b.id_mysql_server ";

        $tmp = [];
        $i   = 0;
        foreach ($fields as $field) {
            $sql .= " LEFT JOIN ".$table_name."_value_text c$i ON c$i.id_mysql_server = a.id AND b.date = c$i.date";
            $sql .= " LEFT JOIN ".$table_name."_name d$i ON d$i.id = c$i.id_".$table_name."_name ";
            $i++;
        }

        $sql .= " WHERE 1 ".$this->getFilter()."";

        if (!empty($id_mysql_server)) {
            if (is_array($id_mysql_server)) {
                $ids = implode(",", $id_mysql_server);
            } else {
                $ids = intval($id_mysql_server);
            }

            $sql .= " AND a.id IN (".$ids.")";
        }



        $tmp = [];
        $i   = 0;
        foreach ($fields as $field) {
            $sql .= " AND d$i.name = '".$field."'  ";
            $i++;
        }

        $sql .=";";
        return $sql;
    }
    
    
    public function generate_tables()
    {
        
        
    }
}