<?php

namespace App\Controller;

use Glial\I18n\I18n;
use Glial\Synapse\Controller;
use App\Library\Post;
use \Glial\Sgbd\Sgbd;

/*
 * Module pour gérer les tag sur les equipements pour les régrouper
 *
 *
 */
class Tag extends Controller {

/**
 * Render tag state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $params Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $params
 * @psalm-param array<int,mixed> $params
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/tag/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function index($params) {


        /*
          $this->di['js']->addJavascript('https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js', 'colorpicker/Colorpicker.js');

          $this->di['js']->code_javascript("$(function () {
          // Basic instantiation:
          $('#demo').colorpicker();
          });");
         */

        $this->di['js']->addJavascript(array('bootstrap-editable.min.js', 'Tree/index.js'));

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM tag order by name";

        $res = $db->sql_query($sql);


        $data['tags'] = array();
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['tags'][] = $arr;
        }


        $this->set('data', $data);
    }

/**
 * Create tag state through `add`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for add.
 * @phpstan-return void
 * @psalm-return void
 * @see self::add()
 * @example /fr/tag/add
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function add() {
        $db = Sgbd::sql(DB_DEFAULT);


        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            if (!empty($_POST['tag']['name'])) {

                $save['tag'] = array();
                $save['tag'] = $_POST['tag'];


                $id_tag = $db->sql_save($save);

                if ($id_tag) {
                    $msg = I18n::getTranslation(__("The tag has been added"));
                    $title = I18n::getTranslation(__("Success"));
                    set_flash("success", $title, $msg);

                    $method = "index";
                } else {
                    $msg = I18n::getTranslation(__("Impossible to add this tag : " . $db->sql_error()));
                    $title = I18n::getTranslation(__("Error"));
                    set_flash("error", $title, $msg);

                    $method = __FUNCTION__;
                }


                header('location: ' . LINK .$this->getClass(). '/' . $method . '/' . Post::getToPost());
            }
        }

        $data = array();
        $this->set('data', $data);
    }

/**
 * Update tag state through `update`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @return void Returned value for update.
 * @phpstan-return void
 * @psalm-return void
 * @see self::update()
 * @example /fr/tag/update
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function update() {

        $this->view = false;
        $this->layout_name = false;

        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $db = Sgbd::sql(DB_DEFAULT);

            $sql = "UPDATE tag SET `" . $_POST['name'] . "` = '" . $_POST['value'] . "' WHERE id = " . $db->sql_real_escape_string($_POST['pk']) . "";
            $db->sql_query($sql);

            if ($db->sql_affected_rows() === 1) {
                echo "OK";
            } else {
                header("HTTP/1.0 503 Internal Server Error");
            }
        }
    }

}

