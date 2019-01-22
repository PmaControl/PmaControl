<?php

use \Glial\Synapse\Controller;
use App\Library\Tree as TreeInterval;
use App\Library\Debug;

// https://codepen.io/gab/pen/Bxpwi

class Tree extends Controller
{

    public function index($param)
    {
        $this->di['js']->addJavascript(array('bootstrap-editable.min.js', 'Tree/index.js'));
        $this->di['js']->addJavascript(array('bootstrap-select.min.js'));


        if (empty($param[0])) {
            $param[0] = 1;
        }


        $data['id_menu'] = $param[0];


        if ($_SERVER['REQUEST_METHOD'] === "POST") {

            if (!empty($_POST['menu']['id'])) {
                header('location: '.LINK.__CLASS__.'/'.__FUNCTION__.'/'.$_POST['menu']['id']);
            }
        }

        /*
          $this->di['js']->code_javascript('$(function () {  $(\'[data-toggle="popover"]\').popover({trigger:"hover"}) });');
          $this->di['js']->code_javascript('
          $(\'[data-toggle="popover"]\').each(function(index, element) {
          var contentElementId = $(element).data().target;
          var contentHtml = $(contentElementId).html();
          $(element).popover({
          content: contentHtml,
          trigger:"hover",
          html:true
          });
          });');
         */


        $db = $this->di['db']->sql(DB_DEFAULT);


        $sql = "select * from menu_group";

        $res = $db->sql_query($sql);


        $data['liste_menu'] = array();
        while ($ob                 = $db->sql_fetch_object($res)) {
            $tmp            = array();
            $tmp['id']      = $ob->id;
            $tmp['libelle'] = $ob->title;

            $data['liste_menu'][] = $tmp;
        }


        //$data['id_menu'] = 1;

        $sql2 = "SELECT * FROM menu WHERE group_id=".$data['id_menu']." ORDER BY bg ASC";
        $res2 = $db->sql_query($sql2);

        $data['menu'] = array();
        while ($ob           = $db->sql_fetch_array($res2, MYSQLI_ASSOC)) {

            $data['menu'][] = $ob;
        }



        $this->set('data', $data);
    }

    public function delete($param)
    {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $id_menu = $param[0];
        $id      = $param[1];

        $tree = new TreeInterval($db, "menu", array(), array("group_id" => $id_menu));

        $tree->delete($id);


        header("location: ".LINK."tree/index/".$id_menu);
    }

    public function add($param)
    {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $id_menu   = $param[0];
        $id_parent = $param[1];

        $tree = new TreeInterval($db, "menu", array("id_parent" => "parent_id"), array("group_id" => $id_menu));

        if ($_SERVER['REQUEST_METHOD'] === "POST") {

            $tree->add($_POST['menu'], $id_parent);
            header("location: ".LINK."tree/index/".$id_menu);
        }
    }

    public function up($param)
    {

        Debug::parseDebug($param);

        $db = $this->di['db']->sql(DB_DEFAULT);

        $id_menu = $param[0];
        $id      = $param[1];

        $tree = new TreeInterval($db, "menu", array("id_parent" => "parent_id"), array("group_id" => $id_menu));

        $tree->up($id);

        $this->debugShowQueries();

        header("location: ".LINK."tree/index/".$id_menu);
    }

    public function update($param)
    {
        $this->view        = false;
        $this->layout_name = false;

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "UPDATE menu SET `".$_POST['name']."` = '".$_POST['value']."' WHERE id = ".$db->sql_real_escape_string($_POST['pk'])."";
        $db->sql_query($sql);

        if ($db->sql_affected_rows() === 1) {
            echo "OK";
        } else {
            header("HTTP/1.0 503 Internal Server Error");
        }
    }

    public function getCountFather($param)
    {


        $id_menu = $param[0];
        $id      = $param[1];

        $db = $this->di['db']->sql(DB_DEFAULT);

        $tree = new TreeInterval($db, "menu", array("id_parent" => "parent_id"), array("group_id" => $id_menu));

        $data['cpt'] = $tree->countFather($id);


        $this->set('data', $data);
    }

    public function left($param)
    {
        $id_menu = $param[0];
        $id      = $param[1];

        $db = $this->di['db']->sql(DB_DEFAULT);

        $tree = new TreeInterval($db, "menu", array("id_parent" => "parent_id"), array("group_id" => $id_menu));

        $tree->left($id);
    }
}
// WITH a as (SELECT count(1) as cpt from menu WHERE menu_id = 1 ) SELECT