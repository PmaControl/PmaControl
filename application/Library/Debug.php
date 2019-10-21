<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use \Glial\Cli\Color;
use \Glial\Cli\Table;

class Debug
{
    static $debug       = false;
    static $count       = 0;
    static $microtime   = array();
    static $display_sql = true;

    static function parseDebug(& $param)
    {
        if (!empty($param)) {
            if (is_array($param)) {
                foreach ($param as $key => $elem) {
                    if ($elem == "--debug") {
                        self::$debug = true;
                        self::checkPoint("Start debug");
                        //self::debug(\Glial\Cli\Color::getColoredString("Debug enabled !", "yellow"));

                        unset($param[$key]);
                    }
                }
            } else {
                if ($param == "--debug") {
                    self::$debug = true;
                    self::checkPoint("Start debug");
                }
            }
        }
    }

    static public function debugShowQueries($di_link)
    {
        if (self::$debug) {

            $thread_sgbd = $di_link->getConnected();

            self::debug($thread_sgbd);

            foreach ($thread_sgbd as $name_db) {

                echo \Glial\Cli\Color::getColoredString($name_db, "black", "yellow")."\n";

                $db = $di_link->sql($name_db);

                $table = new Table("1");
                $table->addHeader(array("Top", "File", "Time", "Cumul", "Query",
                    "Rows"));

                $cumul = 0;
                $i     = 1;
                foreach ($db->query as $tab) {
                    if (mb_strlen($tab['query']) > 100) {
                        $query = trim(\SqlFormatter::format($tab['query']));
                    } else {
                        if (mb_strlen($tab['query']) > 200) {
                            $tab['query'] = substr($tab['query'], 0, 200);
                        }

                        $query = trim(\SqlFormatter::highlight($tab['query']));
                    }

                    $cumul += $tab['time'];
                    $table->addHeader(array($i, pathinfo($tab['file'])['basename'].":".$tab['line'],
                        (string) $tab['time'], (string) $cumul, $query,
                        $tab['rows']));

                    $i++;
                }

                echo $table->display();
            }
        }
    }

    static public function checkPoint($name = "")
    {
        if (self::$debug) {

            $calledFrom = debug_backtrace();

            self::$microtime[] = array(microtime(true), pathinfo($calledFrom[0]['file'])["basename"].':'.$calledFrom[0]['line'],
                date("Y-m-d H:i:s"), $name);
        }
    }

    static function debugShowTime()
    {
        if (self::$debug) {

            $table = new Table("1");
            $table->addHeader(array("Top", "Name", "File:line", "Date", "Time", "Cumul"));


            $cumul = 0;
            $time  = 0;

            $i = 0;
            foreach (self::$microtime as $var) {
                if ($cumul === 0) {
                    $cumul_new = 0;
                    $time_new  = "N/A";

                    $cumul = $var[0];
                } else {

                    $time_new  = round(abs($time - $var[0]), 5);
                    $cumul_new = round(abs($cumul - $var[0]), 5);


                    if ($time_new > 1) {
                        $time_new = \Glial\Cli\Color::getColoredString($time_new, "grey", "red");
                    } elseif ($time_new > 0.1) {
                        $time_new = \Glial\Cli\Color::getColoredString($time_new, "yellow");
                    }
                }


                $i++;
                $table->addLine(array($i, $var[3], $var[1], $var[2], $time_new, $cumul_new));

                $time = $var[0];
            }

            echo "\n".$table->display();
        }
    }

    static function debugPurge()
    {
        self::$microtime = array();
    }

    static function debugQueriesOff()
    {
        self::$display_sql = false;
    }

    static function debug($string, $var = "")
    {
        if (self::$debug) {

            self::head();

            if (!empty($var)) {

                if (IS_CLI) {
                    echo \Glial\Cli\Color::getColoredString($var, "grey", "blue")." ";
                } else {
                    echo $var."<br>";
                }
            }


            if (is_array($string) || is_object($string)) {


                if (IS_CLI) {

                    print_r($string);
                } else {
                    echo $var."<br>";
                    echo "<pre>";
                    print_r($string);
                    echo "</pre>";
                }
            } else {

                if (IS_CLI) {
                    echo trim($string)."\n";
                } else {
                    echo "<b>".trim(str_replace("\n", "<br>", $string))."</b><br>";
                }
            }
        }
    }

    static function sql($sql, $var = "")
    {
        if (self::$debug) {


            self::head();

            if (!empty($var)) {



                if (IS_CLI) {
                    echo \Glial\Cli\Color::getColoredString($var, "grey", "blue")." ";
                } else {
                    echo $var." ";
                }
            }

            $sql = \SqlFormatter::highlight($sql);

            if (mb_strlen($sql) > 10000) {
                $suspention = Color::getColoredString("[...]", "grey", "blue");
                $sql = mb_substr($sql, 0, 5000)."\n".$suspention."\n".mb_substr($sql, -5000);
                echo $sql;
            }
            //echo trim($string)."\n";
        }
    }

    static function getDate()
    {
        if (IS_CLI) {
            return \Glial\Cli\Color::getColoredString("[".date('Y-m-d H:i:s')."]", "purple")." ";
        } else {
            return "[".date('Y-m-d H:i:s')."] ";
        }
    }

    static function warning($var = "")
    {
        self::head();

        if (self::$debug) {


            if (IS_CLI) {
                echo \Glial\Cli\Color::getColoredString($var, "grey", "yellow")." ";
                echo "\n";
            }
        }
    }

    static function error($var = "")
    {
        self::head();

        if (self::$debug) {
            if (IS_CLI) {
                echo \Glial\Cli\Color::getColoredString($var, "grey", "red")." ";

                echo "\n";
            }
        }
    }

    static function success($var = "")
    {
        self::head();

        if (self::$debug) {
            if (IS_CLI) {
                echo \Glial\Cli\Color::getColoredString($var, "grey", "green")." ";
                echo "\n";
            }
        }
    }

    static function head()
    {
        $calledFrom = debug_backtrace();
        $file       = pathinfo(substr(str_replace(ROOT, '', $calledFrom[1]['file']), 1))["basename"];
        $line       = $calledFrom[1]['line'];

        $file = explode(".", $file)[0];

        echo "#".self::$count++."\t";
        echo $file.":".$line."\t";

        echo self::getDate();
        //echo \Glial\Cli\Color::getColoredString("[".date('Y-m-d H:i:s')."]", "purple")." ";
    }
}