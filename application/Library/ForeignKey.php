<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

class ForeignKey
{
 
    var $db; /* link connection */
    var $database;
    var $previous_db;
    
    
    public function __construct($db, $database)
    {
        $this->previous_db = $this->db->database;
        $this->db = $db;
    }
    
    
    public function getPath($table_a, $table_b)
    {
       
        $this->db->sql_select_db($database);
        
        
        $sql = "";
        
        
        
        
        $this->db->sql_select_db($database);
        
    }
    
        
}