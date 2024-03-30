<?php

use \Glial\Synapse\Controller;

use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;
use \App\Library\Debug;

use \Glial\Sgbd\Sgbd;
use \App\Library\Extraction;



class Listener extends Controller
{

    public function before($param)
    {
        $monolog       = new Logger("Listener");
        $handler      = new StreamHandler(LOG_FILE, Logger::DEBUG);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $monolog->pushHandler($handler);
        $this->logger = $monolog;
    }


    public function checkConfig($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql ="SELECT * FROM "


    }

    public function check($param)
    {

        $db = Sgbd::sql(DB_DEFAULT);


    }


    public function onUpdate($param)
    {


    }





}

