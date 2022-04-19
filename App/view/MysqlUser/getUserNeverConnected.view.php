<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \App\Library\Debug;

echo "gg";

Debug::debug($data);



foreach ($data['accounts'] as $account) {

    echo "'" . $account->user . "'@'" . $account->host . "'\n";
}


echo "xwdfhshdfg";
