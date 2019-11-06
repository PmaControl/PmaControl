<?php

use \App\Boundary\AbstractPlugin;

class install implements AbstractPlugin
{
    //var $MenuInject;

    var $MenuInject = array (
        "0" => array (
            "title" => "sys Schema",
            "url" => "{LINK}mysqlsys/index/",
            "icon" => '<span class="glyphicon glyphicon-th-list" aria-hidden="true"></span>',
            "class" => "mysqlsys",
            "method" => "index"
            )
        );

    function menu_install()
    {
        return $this->MenuInject;
    }

}
