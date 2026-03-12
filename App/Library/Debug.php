<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use \Glial\Cli\Color;
use \Glial\Cli\Table;

use \Glial\Synapse\FactoryController;

/**
 * Class responsible for debug workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Debug
{
/**
 * Stores `$debug` for debug.
 *
 * @var bool
 * @phpstan-var bool
 * @psalm-var bool
 */
    static $debug       = false;
/**
 * Stores `$count` for count.
 *
 * @var int
 * @phpstan-var int
 * @psalm-var int
 */
    static $count       = 0;
/**
 * Stores `$microtime` for microtime.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    static $microtime   = array();
/**
 * Stores `$display_sql` for display sql.
 *
 * @var bool
 * @phpstan-var bool
 * @psalm-var bool
 */
    static $display_sql = true;

/**
 * Handle debug state through `parseDebug`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param & $param Route parameters forwarded by the router.
 * @phpstan-param & $param
 * @psalm-param & $param
 * @return void Returned value for parseDebug.
 * @phpstan-return void
 * @psalm-return void
 * @see self::parseDebug()
 * @example /fr/debug/parseDebug
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function parseDebug(& $param)
    {
        if (!empty($param)) {
            if (is_array($param)) {
                foreach ($param as $key => $elem) {
                    if ($elem === "--debug") {
                        self::$debug = true;
                        self::checkPoint("Start debug");
                        //self::debug(\Glial\Cli\Color::getColoredString("Debug enabled !", "yellow"));

                        unset($param[$key]);
                    }
                }
            } else {
                if ($param === "--debug") {
                    self::$debug = true;
                    self::checkPoint("Start debug");
                }
            }
        }
    }

/**
 * Handle debug state through `debugShowQueries`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param mixed $di_link Input value for `di_link`.
 * @phpstan-param mixed $di_link
 * @psalm-param mixed $di_link
 * @return void Returned value for debugShowQueries.
 * @phpstan-return void
 * @psalm-return void
 * @see self::debugShowQueries()
 * @example /fr/debug/debugShowQueries
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function debugShowQueries($di_link)
    {
        if (self::$debug) {

            $thread_sgbd = $di_link->getConnected();

            self::debug($thread_sgbd);

            foreach ($thread_sgbd as $name_db) {

                echo Color::getColoredString($name_db, "black", "yellow")."\n";

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

/**
 * Handle debug state through `checkPoint`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $name Input value for `name`.
 * @phpstan-param mixed $name
 * @psalm-param mixed $name
 * @return void Returned value for checkPoint.
 * @phpstan-return void
 * @psalm-return void
 * @see self::checkPoint()
 * @example /fr/debug/checkPoint
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function checkPoint($name = "")
    {
        if (self::$debug) {

            $calledFrom = debug_backtrace();

            self::$microtime[] = array(microtime(true), pathinfo($calledFrom[0]['file'])["basename"].':'.$calledFrom[0]['line'],
                date("Y-m-d H:i:s"), $name);
        }
    }

/**
 * Handle debug state through `debugShowTime`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @return void Returned value for debugShowTime.
 * @phpstan-return void
 * @psalm-return void
 * @see self::debugShowTime()
 * @example /fr/debug/debugShowTime
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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

/**
 * Handle debug state through `debugPurge`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for debugPurge.
 * @phpstan-return void
 * @psalm-return void
 * @see self::debugPurge()
 * @example /fr/debug/debugPurge
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function debugPurge()
    {
        self::$microtime = array();
    }

/**
 * Handle debug state through `debugQueriesOff`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for debugQueriesOff.
 * @phpstan-return void
 * @psalm-return void
 * @see self::debugQueriesOff()
 * @example /fr/debug/debugQueriesOff
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function debugQueriesOff()
    {
        self::$display_sql = false;
    }

/**
 * Handle debug state through `debug`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param mixed $string Input value for `string`.
 * @phpstan-param mixed $string
 * @psalm-param mixed $string
 * @param mixed $var Input value for `var`.
 * @phpstan-param mixed $var
 * @psalm-param mixed $var
 * @param mixed $font_color Input value for `font_color`.
 * @phpstan-param mixed $font_color
 * @psalm-param mixed $font_color
 * @param mixed $background_color Input value for `background_color`.
 * @phpstan-param mixed $background_color
 * @psalm-param mixed $background_color
 * @return void Returned value for debug.
 * @phpstan-return void
 * @psalm-return void
 * @see self::debug()
 * @example /fr/debug/debug
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function debug($string, $var = "", $font_color="grey", $background_color="blue")
    {
        if (self::$debug) {

            self::head();

            if (!empty($var)) {

                if (IS_CLI) {
                    echo Color::getColoredString($var, $font_color, $background_color)." ";
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
                    if (! isset($string)) {
                        $string = "";
                    }
                    echo trim($string)."\n";
                } else {
                    echo "<b>".trim(str_replace("\n", "<br>", $string))."</b><br>";
                }
            }
        }
    }

/**
 * Handle debug state through `sql`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param string $sql Input value for `sql`.
 * @phpstan-param string $sql
 * @psalm-param string $sql
 * @param mixed $var Input value for `var`.
 * @phpstan-param mixed $var
 * @psalm-param mixed $var
 * @return void Returned value for sql.
 * @phpstan-return void
 * @psalm-return void
 * @see self::sql()
 * @example /fr/debug/sql
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function sql(string $sql, $var = "")
    {
        if (self::$debug) {
            self::head();

            if (!empty($var)) {
                if (IS_CLI) {
                    echo Color::getColoredString($var, "grey", "blue")." ";
                } else {
                    echo $var." ";
                }
            }

            if (mb_strlen($sql) > 10000) {
                $suspention = Color::getColoredString("[...]", "grey", "blue");
                $sql        = mb_substr($sql, 0, 5000)."\n".$suspention."\n".mb_substr($sql, -5000);
                echo $sql."\n";
            } else {
                $sql = str_replace("\n\n", "", $sql);
                $sql = preg_replace("/ {2,}/", " ", $sql);
                $sql = \SqlFormatter::highlight($sql);
                echo trim($sql)."\n";
            }
        }
    }

/**
 * Retrieve debug state through `getDate`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for getDate.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getDate()
 * @example /fr/debug/getDate
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function getDate()
    {
        if (IS_CLI) {
            return Color::getColoredString("[".date('Y-m-d H:i:s')."]", "purple")." ";
        } else {
            return "[".date('Y-m-d H:i:s')."] ";
        }
    }

/**
 * Handle debug state through `warning`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $string Input value for `string`.
 * @phpstan-param mixed $string
 * @psalm-param mixed $string
 * @param mixed $var Input value for `var`.
 * @phpstan-param mixed $var
 * @psalm-param mixed $var
 * @return void Returned value for warning.
 * @phpstan-return void
 * @psalm-return void
 * @see self::warning()
 * @example /fr/debug/warning
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function warning($string, $var = "")
    {
        //self::head();
        self::debug($string, $var, "black", "yellow");

    }

/**
 * Handle debug state through `error`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $string Input value for `string`.
 * @phpstan-param mixed $string
 * @psalm-param mixed $string
 * @param mixed $var Input value for `var`.
 * @phpstan-param mixed $var
 * @psalm-param mixed $var
 * @return void Returned value for error.
 * @phpstan-return void
 * @psalm-return void
 * @see self::error()
 * @example /fr/debug/error
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function error($string, $var = "")
    {
        //self::head();
        self::debug($string, $var, "grey", "red");
    }

/**
 * Handle debug state through `success`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $string Input value for `string`.
 * @phpstan-param mixed $string
 * @psalm-param mixed $string
 * @param mixed $var Input value for `var`.
 * @phpstan-param mixed $var
 * @psalm-param mixed $var
 * @return void Returned value for success.
 * @phpstan-return void
 * @psalm-return void
 * @see self::success()
 * @example /fr/debug/success
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function success($string, $var = "")
    {
        //self::head();
        self::debug($string, $var, "grey", "green");
    }

/**
 * Handle debug state through `head`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @return void Returned value for head.
 * @phpstan-return void
 * @psalm-return void
 * @see self::head()
 * @example /fr/debug/head
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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

