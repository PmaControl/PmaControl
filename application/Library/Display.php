<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

class Display
{

    static public function server($arr)
    {

        
        return '<span title="'.$arr['libelle'].'" class="label label-'.$arr['class'].'">'.$arr['letter'].'</span>'
        .' <a href="">'.$arr['display_name'].'</a> <small class="text-muted">'.$arr['ip'].'</small>';
    }
}