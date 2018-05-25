<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;

class ErrorWeb extends Controller
{

    function error404()
    {
        $this->layout_name = 'default';

        $this->title  = __("Error 404");
        $this->ariane = " > ".$this->title;

        //$this->javascript = array("");
    }
    
    
    public function message($param)
    {
        
        $data['title'] = $param[0];
        $data['msg'] = $param[1];
        $data['color'] = $param[2];
        
        $this->set('data',$data);
        
    }
}