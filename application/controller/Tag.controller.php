<?php

use Glial\I18n\I18n;
use Glial\Synapse\Controller;
use App\Library\Post;

class Tag extends Controller
{

    function index($params)
    {


        /*
          $this->di['js']->addJavascript('https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js', 'colorpicker/Colorpicker.js');

          $this->di['js']->code_javascript("$(function () {
          // Basic instantiation:
          $('#demo').colorpicker();
          });");
         */

        $this->di['js']->addJavascript(array('bootstrap-editable.min.js', 'Tree/index.js'));

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM tag order by name";

        $res = $db->sql_query($sql);


        $data['tags'] = array();
        while ($arr          = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['tags'][] = $arr;
        }


        $this->set('data', $data);
    }

    public function add()
    {
        $db = $this->di['db']->sql(DB_DEFAULT);


        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            if (!empty($_POST['tag']['name'])) {

                $save['tag'] = array();
                $save['tag'] = $_POST['tag'];


                $id_tag = $db->sql_save($save);

                if ($id_tag) {
                    $msg   = I18n::getTranslation(__("The tag has been added"));
                    $title = I18n::getTranslation(__("Success"));
                    set_flash("success", $title, $msg);

                    $method = "index";
                } else {
                    $msg   = I18n::getTranslation(__("Impossible to add this tag : ".$db->sql_error()));
                    $title = I18n::getTranslation(__("Error"));
                    set_flash("error", $title, $msg);

                    $method = __FUNCTION__;
                }


                header('location: '.LINK.__CLASS__.'/'.$method.'/'.Post::getToPost());
            }
        }

        $data = array();
        $this->set('data', $data);
    }

    public function update()
    {

        $this->view        = false;
        $this->layout_name = false;

        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $db = $this->di['db']->sql(DB_DEFAULT);

            $sql = "UPDATE tag SET `".$_POST['name']."` = '".$_POST['value']."' WHERE id = ".$db->sql_real_escape_string($_POST['pk'])."";
            $db->sql_query($sql);

            if ($db->sql_affected_rows() === 1) {
                echo "OK";
            } else {
                header("HTTP/1.0 503 Internal Server Error");
            }
        }
    }
}