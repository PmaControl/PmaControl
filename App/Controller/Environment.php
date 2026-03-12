<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\Post;
use \Glial\Sgbd\Sgbd;


/**
 * Class responsible for environment workflows.
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
class Environment extends Controller {

/**
 * Render environment state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/environment/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index() {
        $this->title = '<i class="fa fa-th-large" aria-hidden="true"></i> ' . __("Environment");


        $this->di['js']->addJavascript(array('bootstrap-editable.min.js', 'Tree/index.js'));
        $this->di['js']->addJavascript(array('bootstrap-select.min.js'));


        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM environment order by `id`";

        $res = $db->sql_query($sql);

        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['env'][] = $row;
        }


        $this->set('data', $data);
    }

/**
 * Update environment state through `update`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @return void Returned value for update.
 * @phpstan-return void
 * @psalm-return void
 * @see self::update()
 * @example /fr/environment/update
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

            $sql = "UPDATE environment SET `" . $_POST['name'] . "` = '" . $_POST['value'] . "' WHERE id = " . $db->sql_real_escape_string($_POST['pk']) . "";
            $db->sql_query($sql);

            if ($db->sql_affected_rows() === 1) {
                echo "OK";
            } else {
                header("HTTP/1.0 503 Internal Server Error");
            }
        }
    }


/**
 * Create environment state through `add`.
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
 * @example /fr/environment/add
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function add($param) {
        $this->di['js']->addJavascript(array("bootstrap-select.min.js"));
        $db = Sgbd::sql(DB_DEFAULT);

        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $variable['environment'] = $_POST['environment'];

            //if ((empty($variable['environment']['libelle']))||(empty($variable['environment']['libelle']))||(empty($variable['environment']['libelle']))||(empty($variable['environment']['libelle'])))

            $return = $db->sql_save($variable);
            if (!$return) {
                $error = $db->sql_error();
                $_SESSION['ERROR'] = $error;

                $msg = "<ul><li>" . implode("</li><li>", $error['environment']) . "</li></ul>";

                set_flash("error", "Error", $msg);

                header("location: " . LINK . "environment/add/" . Post::getToPost());
            } else {
                debug($db->sql_error());
                //header("location: ".LINK."environment/index/");
            }
        }

        $colors = array("danger", "warning", "default", "info", "success", "primary");

        $data['colors'] = array();
        foreach ($colors as $color) {
            $temp = [];
            $temp['id'] = $color;
            $temp['libelle'] = $color;

            $temp['extra'] = array("data-content" => "<span title='" . $color . "' class='label label-" . $color . "'>" . strtoupper($color) . "</span>");

            $data['colors'][] = $temp;
        }

        $this->set('data', $data);
    }


/**
 * Delete environment state through `delete`.
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
 * @example /fr/environment/delete
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function delete($param) {
        
        $this->view = false;
        $id_environment = $param[0];

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "DELETE FROM environment WHERE id = '".$id_environment."' and id > 6";
        $db->sql_query($sql);

        header("location:". LINK . "environment/index/");
    }

}

