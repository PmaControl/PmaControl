<?php

/*
 * Test all configuration to be sure everything correctly installed
 * developement => true
 * production => false
 * default : true
 */

if (!defined('ENVIRONEMENT')) {
    define("ENVIRONEMENT", false);
}


if (ENVIRONEMENT) {
    error_reporting(-1);
    ini_set('display_errors', 1);
}


