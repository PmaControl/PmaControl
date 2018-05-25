<?php


/*
 * if you use a direrct DNS set : define('WWW_ROOT', "/");
 * 
 * if you dev in local or other use : define('WWW_ROOT', "/path_to_the_final_directory/");
 * 
 * example : http://127.0.0.1/directory/myapplication/ => define('WWW_ROOT', "/directory/myapplication/");
 * 
 * Don't forget the final "/"
 */


if (! defined('WWW_ROOT'))
{
    define('WWW_ROOT', "/pmacontrol/");
}


