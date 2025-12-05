<?php


use \Glial\Cli\Color;



/**
 * Basic defines for timing functions.
 */
define('SECOND', 1);
define('MINUTE', 60);
define('HOUR', 3600);
define('DAY', 86400);
define('WEEK', 604800);
define('MONTH', 2592000);
define('YEAR', 31536000);

//test git

/**
 * Prints out debug information about given variable.
 *
 * Only runs if debug level is greater than zero.
 *
 * @param mixed $var Variable to show debug information for.
 * @param boolean $showHtml If set to true, the method prints the debug data in a screen-friendly way.
 * @param boolean $showFrom If set to true, the method prints from where the function was called.
 * @link http://book.cakephp.org/view/1190/Basic-Debugging
 * @link http://book.cakephp.org/view/1128/debug
 */
function debug(mixed $var, $showHtml = false, $showFrom = true)
{
    
    if (IS_CLI) {
        $calledFrom = debug_backtrace();

        echo Color::getColoredString(substr(str_replace(ROOT, '', $calledFrom[0]['file']), 1).' (line ' . $calledFrom[0]['line'] . ')', "grey", "blue","bold") . "\n";
        print_r($var);
    } else {
        if (true) {

            if ($showFrom) {
                $calledFrom = debug_backtrace();
                echo '<strong>' . substr(str_replace(ROOT, '', $calledFrom[0]['file']), 1) . '</strong>';
                echo ' (line <strong>' . $calledFrom[0]['line'] . '</strong>)';
            }
            echo "\n<pre>\n";

            print_r($var);
            if ($showHtml) {
                $var = str_replace('<', '&lt;', str_replace('>', '&gt;', $var));
            }
            echo "\n</pre>\n";
        }
    }
}



function set_flash($type_error, $title, $msg)
{
    $msg_flash["type_error"] = $type_error;
    $msg_flash["title"] = $title;
    $msg_flash["msg"] = $msg;

    $_SESSION['msg_flash'][] = $msg_flash;
}

function get_flash()
{
    if (!empty($_SESSION['msg_flash'])) {
        $data = $_SESSION['msg_flash'];
        include APP_DIR . DS . "element" . DS . "flash" . ".php";


        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            unset($_SESSION['msg_flash']);
        }
    }
}

function input($table, $field, $classo = "", $indice = -1)
{
    /* if (  strstr($table))
      {
      echo " [$table][$field]";
      } */

    if ($indice != -1) {
        if (!empty($_GET[$table][$indice][$field])) {
            $value = $_GET[$table][$indice][$field];
        } else {
            $value = "";
        }
    } else {
        if (!empty($_GET[$table][$field])) {
            $value = $_GET[$table][$field];
        } else {
            $value = "";
        }
    }

    if (!empty($_SESSION['ERROR'][$table][$field])) {
        $error = " <span class=\"error\">" . $_SESSION['ERROR'][$table][$field] . "</span>";
        $class = " error";

        unset($_SESSION['ERROR'][$table][$field]);
    } else {
        $error = "";
        $class = "";
    }

    if (!empty($classo))
        $classo = "$classo ";


    if ($indice != -1) {
        return "<input id=\"" . $table . "-" . $indice . "-" . $field . "\" class=\"" . $classo . "text" . $class . "\" type=\"text\" name=\"" . $table . "[" . $indice . "][" . $field . "]\" value=\"" . $value . "\" />" . $error;
    } else {
        return "<input id=\"" . $table . "-" . $field . "\" class=\"" . $classo . "text" . $class . "\" type=\"text\" name=\"" . $table . "[" . $field . "]\" value=\"" . $value . "\" />" . $error;
    }
}

function textarea($table, $field, $classo = "")
{
    if (!empty($_GET[$table][$field])) {
        $value = $_GET[$table][$field];
    } else {
        $value = "";
    }

    if (!empty($_SESSION['ERROR'][$table][$field])) {
        $error = " <span class=\"error\">" . $_SESSION['ERROR'][$table][$field] . "</span>";
        $class = " error";

        unset($_SESSION['ERROR'][$table][$field]);
    } else {
        $error = "";
        $class = "";
    }

    if (!empty($classo))
        $classo = "$classo ";
    return "<textarea id=\"" . $table . "-" . $field . "\" class=\"" . $classo . "text" . $class . "\" type=\"text\" name=\"" . $table . "[" . $field . "]\" />" . $value . "</textarea>" . $error;
}

function hidden($table, $field, $default_value)
{
    if (!empty($_GET[$table][$field])) {
        $value = $_GET[$table][$field];
    } else {
        if (!empty($default_value)) {
            $value = $default_value;
        } else {
            $value = "";
        }
    }



    if (!empty($classo))
        $classo = "$classo ";
    return "<input id=\"" . $table . "-" . $field . "\" type=\"hidden\" name=\"" . $table . "[" . $field . "]\" value=\"" . $value . "\" />";
}

function autocomplete($table, $field, $classo = "", $indice = -1)
{

    if ($indice != -1) {
        if (!empty($_GET[$table][$indice][$field])) {
            $value = $_GET[$table][$indice][$field];
            $valueauto = $_GET[$table][$indice][$field . "-auto"];
        } else {
            $value = "";
            $valueauto = "";
        }
    } else {
        if (!empty($_GET[$table][$field])) {
            $value = $_GET[$table][$field];
            $valueauto = $_GET[$table][$field . "-auto"];
        } else {
            $value = "";
            $valueauto = "";
        }
    }

    if (!empty($_SESSION['ERROR'][$table][$field])) {
        $error = " <span class=\"error\">" . $_SESSION['ERROR'][$table][$field] . "</span>";
        $class = " error";
        unset($_SESSION['ERROR'][$table][$field]);
    } else {
        $error = "";
        $class = "";
    }

    if (!empty($classo))
        $classo = "$classo ";

    if ($indice != -1) {
//return "<input id=\"" . $table . "-" . $indice . "-" . $field . "\" class=\"" . $classo . "text" . $class . "\" type=\"text\" name=\"" . $table . "[" . $indice . "][" . $field . "]\" value=\"" . $value . "\" />" . $error;
        return "<input id=\"" . $table . "-" . $indice . "-" . $field . "-auto\" class=\"" . $classo . "text" . $class . "\" type=\"text\" autocomplete=\"new-password\" name=\"" . $table . "[" . $indice . "][" . $field . "-auto]\" value=\"" . $valueauto . "\" />"
                . "<input id=\"" . $table . "-" . $indice . "-" . $field . "\" name=\"" . $table . "[" . $indice . "][" . $field . "]\" class=\"hidden\" autocomplete=\"new-password\" type=\"text\" value=\"" . $value . "\" />" . $error;
    } else {
        return "<input id=\"" . $table . "-" . $field . "-auto\" class=\"" . $classo . "text" . $class . "\" type=\"text\" autocomplete=\"new-password\" name=\"" . $table . "[" . $field . "-auto]\" value=\"" . $valueauto . "\" />"
                . "<input id=\"" . $table . "-" . $field . "\" name=\"" . $table . "[" . $field . "]\" class=\"hidden\" autocomplete=\"new-password\" type=\"text\" value=\"" . $value . "\" />" . $error;
    }
}

function password($table, $field, $classo = "")
{

    if (!empty($_SESSION['ERROR'][$table][$field])) {
        $error = " <span class=\"error\">" . $_SESSION['ERROR'][$table][$field] . "</span>";
        $class = " error";
        unset($_SESSION['ERROR'][$table][$field]);
    } else {
        $error = "";
        $class = "";
    }
    if (!empty($classo))
        $classo = "$classo ";
    return "<input id=\"" . $table . "-" . $field . "\" class=\"" . $classo . "text" . $class . "\" type=\"password\" name=\"" . $table . "[" . $field . "]\" />" . $error;
}

function select($table, $field, $data, $default_id = "", $classo = "", $ajax = 0, $indice = -1)
{
    if (!empty($_SESSION['ERROR'][$table][$field])) {
        $error = " <span class=\"error\">" . $_SESSION['ERROR'][$table][$field] . "</span>";
        $class = " error";
        unset($_SESSION['ERROR'][$table][$field]);
    } else {
        $error = "";
        $class = "";
    }


    if (!empty($classo))
        $classo = "$classo ";

    $ret = "";
    if ($ajax == 0) {
        if ($indice != -1) {
            $ret .= "<select id=\"" . $table . "-" . $indice . "-" . $field . "\" class=\"" . $classo . "select" . $class . "\" name=\"" . $table . "[" . $indice . "]" . "[" . $field . "]\">";
        } else {
            $ret .= "<select id=\"" . $table . "-" . $field . "\" class=\"" . $classo . "select" . $class . "\" name=\"" . $table . "[" . $field . "]\">";
        }
    }

    if (count($data) != 1) {
        $ret .= "<option value=\"\">--- " . __("Select") . " ---</option>";
    }
//$_SQL = Singleton::getInstance(SQL_DRIVER);
//$table_to_get = substr($field,3);
//$sql = "SELECT id, `".$libelle."` FROM `".$table_source."` WHERE ".$libelle." != '' ORDER BY ".$libelle."";
//$res = $_SQL->sql_query($sql);
//$var = $_SQL->sql_to_array($res);

    $i = 0;

    foreach ($data as $val) {


        if (!empty($val['group']) && 1 === $val['group']) {

            if ($i != 0)
                $ret .= "</optgroup>";
            $ret .= "<optgroup LABEL=\"" . $val['libelle'] . "\">";

            $i++;
        }
        else {

            if ((!empty($_GET[$table][$field]) && $_GET[$table][$field] == $val['id']) || (!empty($default_id) && $default_id == $val['id'])) {
                $ret .= "<option value=\"" . $val['id'] . "\" selected=\"selected\">" . $val['libelle'] . "</option>";
            } else {
                $ret .= "<option value=\"" . $val['id'] . "\">" . $val['libelle'] . "</option>";
            }
        }
    }
    if ($i > 0)
        $ret .= "</optgroup>";



    if ($ajax == 0) {
        $ret .= "</select>" . $error;
    }
    return $ret;
}

function error_msg($table, $field)
{


    if (!empty($_SESSION['ERROR'][$table][$field])) {

        //unset($_SESSION['ERROR'][$table][$field]);
        return "<span class=\"error\">" . $_SESSION['ERROR'][$table][$field] . "</span>";


    }
}
