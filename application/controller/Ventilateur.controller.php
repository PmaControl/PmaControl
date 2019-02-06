<?php

use \Glial\Synapse\Controller;
use App\Library\Zmsg;

//require ROOT."/application/library/Filter.php";
//https://blog.programster.org/php-multithreading-pool-example
// Aspirateur v2 avec zeroMQ
// http://zeromq.org/intro:get-the-software
//http://zguide.zeromq.org/php:all



class Ventilateur extends Controller
{

    public function queue()
    {
        define("MAX_WORKERS", 100);

        if (class_exists("ZMQ") && defined("ZMQ::LIBZMQ_VER")) {
            echo ZMQ::LIBZMQ_VER, PHP_EOL;
        }


        /*
         *  Task worker - design 2
         *  Adds pub-sub flow to receive and respond to kill signal
         * @author Ian Barber <ian(dot)barber(at)gmail(dot)com>
         */

        $context = new ZMQContext();

//  Socket to receive messages on
        $receiver = new ZMQSocket($context, ZMQ::SOCKET_PULL);
        $receiver->connect("tcp://localhost:5557");

//  Socket to send messages to
        $sender = new ZMQSocket($context, ZMQ::SOCKET_PUSH);
        $sender->connect("tcp://localhost:5558");

//  Socket for control input
        $controller = new ZMQSocket($context, ZMQ::SOCKET_SUB);
        $controller->connect("tcp://localhost:5559");
        $controller->setSockOpt(ZMQ::SOCKOPT_SUBSCRIBE, "");

//  Process messages from receiver and controller
        $poll      = new ZMQPoll();
        $poll->add($receiver, ZMQ::POLL_IN);
        $poll->add($controller, ZMQ::POLL_IN);
        $readable  = $writeable = array();

//  Process messages from both sockets
        while (true) {

            echo "10\n";

            $events = $poll->poll($readable, $writeable);
            if ($events > 0) {
                foreach ($readable as $socket) {
                    if ($socket === $receiver) {
                        $message = $socket->recv();
                        //  Simple progress indicator for the viewer
                        echo $message, PHP_EOL;

                        //  Do the work
                        usleep($message * 1000);

                        //  Send results to sink
                        $sender->send("");
                    }
                    //  Any waiting controller command acts as 'KILL'
                    else if ($socket === $controller) {
                        exit();
                    }
                }
            }
        }
    }

    public function worker()
    {
        
    }

    public function pull()
    {


        $context = new ZMQContext();

//  Socket to send messages on
        $sender = new ZMQSocket($context, ZMQ::SOCKET_PUSH);
        $sender->bind("tcp://*:5557");

        echo "Press Enter when the workers are ready: ";
        $fp   = fopen('php://stdin', 'r');
        $line = fgets($fp, 512);
        fclose($fp);
        echo "Sending tasks to workers…", PHP_EOL;

//  The first message is "0" and signals start of batch
        $sender->send(0);

//  Send 100 tasks
        $total_msec = 0;     //  Total expected cost in msecs
        for ($task_nbr = 0; $task_nbr < 100; $task_nbr++) {
            //  Random workload from 1 to 100msecs
            $workload   = mt_rand(1, 100);
            $total_msec += $workload;
            $sender->send($workload);
        }

        printf("Total expected cost: %d msec\n", $total_msec);
        sleep(1);              //  Give 0MQ time to deliver
    }

    public function add()
    {

        $context = new ZMQContext();

//  Socket to receive messages on
        $receiver = new ZMQSocket($context, ZMQ::SOCKET_PULL);
        $receiver->connect("tcp://localhost:5557");

//  Socket to send messages to
        $sender = new ZMQSocket($context, ZMQ::SOCKET_PUSH);
        $sender->connect("tcp://localhost:5558");

//  Socket for control input
        $controller = new ZMQSocket($context, ZMQ::SOCKET_SUB);
        $controller->connect("tcp://localhost:5559");
        $controller->setSockOpt(ZMQ::SOCKOPT_SUBSCRIBE, "");

//  Process messages from receiver and controller
        $poll = new ZMQPoll();


        $id = $poll->add($receiver , ZMQ::POLL_IN);
        
        echo "\n".$id."\n";

    }
}