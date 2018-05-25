<?php


if (! defined('ROUTE_DEFAULT'))
{
    define('ROUTE_DEFAULT', "server/listing");
}


// in case where u cannot access to main PAGE because you need to be logged, else same as ROUTE_DEFAULT
if (! defined('ROUTE_LOGIN'))
{
    define('ROUTE_LOGIN', "user/connection");
}


