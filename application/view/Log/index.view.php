<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \App\Library\Debug;

if (!empty($data['log'])) {

    debug($data['log']);
}


//Debug::$debug = true;
//Debug::debugShowQueries($data['db']);
