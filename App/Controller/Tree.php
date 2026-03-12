<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\Tree as TreeInterval;
use App\Library\Debug;
use \Glial\Sgbd\Sgbd;

// https://codepen.io/gab/pen/Bxpwi

/**
 * Class responsible for tree workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Tree extends Controller
{

/**
 * Render tree state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/tree/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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
                header('location: '.LINK.$this->getClass().'/'.__FUNCTION__.'/'.$_POST['menu']['id']);
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

        $db = Sgbd::sql(DB_DEFAULT);


        $sql = "select * from menu_group";

        $res = $db->sql_query($sql);


        $data['liste_menu'] = array();
        while ($ob                 = $db->sql_fetch_object($res)) {
            $tmp            = array();
            $tmp['id']      = $ob->id;
            $tmp['libelle'] = $ob->title;

            $data['liste_menu'][] = $tmp;
        }

        $sql2 = "SELECT * FROM menu WHERE group_id=".$data['id_menu']." ORDER BY bg ASC";
        $res2 = $db->sql_query($sql2);

        $data['menu'] = array();
        while ($ob           = $db->sql_fetch_array($res2, MYSQLI_ASSOC)) {

            $data['menu'][] = $ob;
        }

        $this->set('data', $data);
    }

/**
 * Delete tree state through `delete`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for delete.
 * @phpstan-return void
 * @psalm-return void
 * @see self::delete()
 * @example /fr/tree/delete
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function delete($param)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $id_menu = $param[0];
        $id      = $param[1];

        $tree = new TreeInterval($db, "menu", array(), array("group_id" => $id_menu));
        $tree->delete($id);

        header("location: ".LINK."tree/index/".$id_menu);
    }

/**
 * Create tree state through `add`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for add.
 * @phpstan-return void
 * @psalm-return void
 * @see self::add()
 * @example /fr/tree/add
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function add($param)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $id_menu   = $param[0];
        $id_parent = $param[1];

        $tree = new TreeInterval($db, "menu", array("id_parent" => "parent_id"), array("group_id" => $id_menu));

        if ($_SERVER['REQUEST_METHOD'] === "POST") {

            $tree->add($_POST['menu'], $id_parent);
            header("location: ".LINK."tree/index/".$id_menu);
        }
    }

/**
 * Handle tree state through `up`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for up.
 * @phpstan-return void
 * @psalm-return void
 * @see self::up()
 * @example /fr/tree/up
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function up($param)
    {

        Debug::parseDebug($param);

        $db      = Sgbd::sql(DB_DEFAULT);
        $id_menu = $param[0];
        $id      = $param[1];
        $tree    = new TreeInterval($db, "menu", array("id_parent" => "parent_id"), array("group_id" => $id_menu));

        $tree->up($id);

        Debug::debugShowQueries($db); // <= ici

        header("location: ".LINK."tree/index/".$id_menu);
    }

/**
 * Update tree state through `update`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for update.
 * @phpstan-return void
 * @psalm-return void
 * @see self::update()
 * @example /fr/tree/update
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function update($param)
    {
        $this->view        = false;
        $this->layout_name = false;

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "UPDATE menu SET `".$_POST['name']."` = '".$_POST['value']."' WHERE id = ".$db->sql_real_escape_string($_POST['pk'])."";
        $db->sql_query($sql);

        if ($db->sql_affected_rows() === 1) {
            echo "OK";
        } else {
            header("HTTP/1.0 503 Internal Server Error");
        }
    }

/**
 * Retrieve tree state through `getCountFather`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for getCountFather.
 * @phpstan-return void
 * @psalm-return void
 * @see self::getCountFather()
 * @example /fr/tree/getCountFather
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getCountFather($param)
    {


        $id_menu = $param[0];
        $id      = $param[1];

        $db = Sgbd::sql(DB_DEFAULT);

        $tree = new TreeInterval($db, "menu", array("id_parent" => "parent_id"), array("group_id" => $id_menu));

        $data['cpt'] = $tree->countFather($id);


        $this->set('data', $data);
    }

/**
 * Handle tree state through `left`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for left.
 * @phpstan-return void
 * @psalm-return void
 * @see self::left()
 * @example /fr/tree/left
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function left($param)
    {
        $id_menu = $param[0];
        $id      = $param[1];
        $db      = Sgbd::sql(DB_DEFAULT);
        $tree    = new TreeInterval($db, "menu", array("id_parent" => "parent_id"), array("group_id" => $id_menu));

        $tree->left($id);
    }
}
