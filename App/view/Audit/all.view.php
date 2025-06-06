<?php

use \App\Library\Debug;

Debug::debug($data);
foreach($data['servers'] as $server)
{
    Debug::debug($server['mysql_available']);

    if ($server['mysql_available'] == "1")
    {


        echo $server['mysql_available']."\n";
    }
}