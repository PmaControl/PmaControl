<?php
/**
 * Index
 *
 * The Front Controller for handling every request
 *
 * PHP versions 5.5 required
 *
 * GLIAL : Rapid Development Framework (http://glial.com)
 * Copyright 2007-2013, Esysteme Software Foundation, Inc. (http://www.esysteme.com/)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2007-2010, Esysteme Software Foundation, Inc. (http://www.esysteme.com)
 * @link          http://www.glial-framework-php.org/ GLIAL(tm) Project
 * @package       glial
 * @since         Gliale(tm) v 0.1
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */


//to know if we are in cli
define('IS_CLI', PHP_SAPI === 'cli');


if (!IS_CLI) {
    //include ("exception.php");
    $ScreenError = 1;
}

try {

    /*
      function exception_error_handler($errno, $errstr, $errfile, $errline)
      {
      throw new Exception("GLI-100 : [ERROR SYSTEM:" . $errno . "] " . $errstr . " (" . $errfile . ":" . $errline . ")", 100);
      }

      set_error_handler("exception_error_handler");
     */

    define("TIME_START", microtime(true));

//Use the DS to separate the directories in other defines
    define('DS', DIRECTORY_SEPARATOR);

    /**
     * These defines should only be edited if you have glial installed in
     * a directory layout other than the way it is distributed.
     * When using custom settings be sure to use the DS and do not add a trailing DS.
     */
    if (IS_CLI) {
        if (substr($_SERVER["SCRIPT_FILENAME"], 0, 1) === DS) {
            //The actual directory name for the "app". 
            define('ROOT', dirname(dirname(dirname(htmlspecialchars($_SERVER["SCRIPT_FILENAME"], ENT_QUOTES, "utf-8")))));
            define('APP_DIR', dirname(dirname(htmlspecialchars($_SERVER["SCRIPT_FILENAME"], ENT_QUOTES, "utf-8"))));
        } else {
            define('ROOT', dirname(dirname(dirname($_SERVER["PWD"].DS.$_SERVER["SCRIPT_FILENAME"]))));
            define('APP_DIR', dirname(dirname($_SERVER["PWD"].DS.$_SERVER["SCRIPT_FILENAME"])));
        }
    } else {
        define('ROOT', dirname(dirname(dirname(htmlspecialchars($_SERVER["SCRIPT_FILENAME"], ENT_QUOTES, "utf-8")))));
        define('APP_DIR', dirname(dirname(htmlspecialchars($_SERVER["SCRIPT_FILENAME"], ENT_QUOTES, "utf-8"))));
    }

//temp directory
    define("TMP", ROOT.DS."tmp".DS);
    define("DATA", ROOT.DS."data".DS);

//The actual directory name for the "config".
    define('CONFIG', ROOT.DS."configuration".DS);

//The actual directory name for the extern "library".
    define('LIBRARY', ROOT.DS."library".DS);

//The absolute path to the "glial" directory.
    define('CORE_PATH', ROOT.DS);
    define('LIB', CORE_PATH."lib".DS);

//The absolute path to the webroot directory.
    define('WEBROOT_DIR', basename(dirname(__FILE__)).DS);

    if (!IS_CLI) {
        require(CONFIG."webroot.config.php");
        define('IMG', WWW_ROOT."image".DS);
        define('CSS', WWW_ROOT."css".DS);
        define('FILE', WWW_ROOT."file".DS);
        define('VIDEO', WWW_ROOT."video".DS);
        define('JS', WWW_ROOT."js".DS);
    }

    define('GLIAL_INDEX', __FILE__);

    if (isset($_GET['glial_path']) && strpos($_GET['glial_path'], 'favicon.ico')) {
        //case where navigator ask favicon.ico even if it's not set in your html
        exit;
    } else {
        if (!include(ROOT.DS."vendor/glial/glial/Glial/Bootstrap.php")) {
            trigger_error("Gliale core could not be found. Check the value of CORE_PATH in App/Webroot/index.php.  It should point to the directory containing your ".DS."glial core directory and your ".DS."vendors root directory.",
                E_USER_ERROR);
        }
    }
} catch (Exception $e) {

    if (IS_CLI)
    {
        echo \Glial\Cli\Color::getColoredString("[".date("Y-m-d H:i:s")."][ERROR] ".$e->getMessage()." ", "black", "red")."\n";
    }
    else
    {
        echo "[".date("Y-m-d H:i:s")."][ERROR] ".$e->getMessage()."\n";
    }

    $error_code = $e->getCode();
    if ($error_code >= 80) {
        $log->emergency($e->getMessage());
    } elseif ($error_code >= 70) {
        $log->alert($e->getMessage());
    } elseif ($error_code >= 60) {
        $log->critical($e->getMessage());
    } elseif ($error_code >= 50) {
        $log->error($e->getMessage());
    } elseif ($error_code >= 40) {
        $log->warning($e->getMessage());
    } elseif ($error_code >= 30) {
        $log->notice($e->getMessage());
    } elseif ($error_code >= 20) {
        $log->info($e->getMessage());
    } elseif ($error_code >= 10) {
        $log->debug($e->getMessage());
    } else {
        $log->log(1, $e->getMessage());
    }


    //debug($e);
} finally {
    if (!IS_CLI) {
        /*
          $stat = new Statistics;
          $stat->getData($GLOBALS['_SITE']['IdUser']);
          $stat->callDeamon(); */
    }
    if (isset($error_code)) {

        echo "CODE ERROR : ".$e->getCode()."\n";

        exit(1);
    }
}
