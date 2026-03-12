<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;


/**
 * Class responsible for crontab workflows.
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
class Crontab extends Controller {

/**
 * Stores `$module_group` for module group.
 *
 * @var string
 * @phpstan-var string
 * @psalm-var string
 */
    public $module_group = "Administration";
/**
 * Stores `$debut` for debut.
 *
 * @var string
 * @phpstan-var string
 * @psalm-var string
 */
    var $debut = '#Les lignes suivantes sont gerees automatiquement via un script PHP. - Merci de ne pas editer manuellement';
/**
 * Stores `$fin` for fin.
 *
 * @var string
 * @phpstan-var string
 * @psalm-var string
 */
    var $fin = '#Les lignes suivantes ne sont plus gerees automatiquement';

/**
 * Render crontab state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/crontab/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function index() {
        
    }

/**
 * Handle crontab state through `admin_crontab`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for admin_crontab.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::admin_crontab()
 * @example /fr/crontab/admin_crontab
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function admin_crontab() {
        $module['picture'] = "administration/iconAttendance.gif";
        $module['name'] = __("Crontab");
        $module['description'] = __("Manage all yours jobs");

        //if (from() !== "administration.controller.php") {


        $this->javascript = array("jquery.1.3.2.js");

        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            if (!empty($_POST['crontab']['command'])) {

                $regexp = $this->buildRegexp();

                $ligne = $_POST['crontab']['minute'] . " " . $_POST['crontab']['hour'] . " " . $_POST['crontab']['dayofmonth'] . " " . $_POST['crontab']['month'] . " " . $_POST['crontab']['dayofweek'] . " " . $_POST['crontab']['command'];

                if (preg_match("/$regexp/", $ligne)) {
                    set_flash("success", "Added", "This tasks has beend added in the crontab");

                    $this->add($_POST['crontab']['minute'], $_POST['crontab']['hour'], $_POST['crontab']['dayofmonth'], $_POST['crontab']['month'], $_POST['crontab']['dayofweek'],
                            $_POST['crontab']['command'], "commentaire =)");

                    header("location: " . $_SERVER['REQUEST_URI']);
                    die();
                } else {
                    set_flash("error", "Error", "This crontab is not valid : " . $ligne);


                    $ret = array();
                    foreach ($_POST['crontab'] as $var => $val) {
                        $ret[] = "crontab:" . $var . ":" . $val;
                    }

                    $param = implode("/", $ret);


                    header("location: " . LINK .$this->getClass(). "/" . __FUNCTION__ . "/" . $param);

                    die();
                }
            }

            if (!empty($_POST['crontab']['delete'])) {
                set_flash("success", "Removed", "This task has been removed");
                $this->delete($_POST['crontab']['delete']);
            }
        }

        //$this->layout_name = "admin";


        $this->title = __("Crontab");
        $this->ariane = "> <a href=\"" . LINK . "administration/\">" . __("Administration") . "</a> > " . $this->title;
        $data = $this->view();
        $this->set("data", $data);
        //}

        return $module;
    }

/**
 * Handle crontab state through `view`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for view.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::view()
 * @example /fr/crontab/view
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function view() {
        $isSection = false;
        exec('crontab -l', $oldCrontab);  /* on récupère l'ancienne crontab dans $oldCrontab */

        $tab = array();

        foreach ($oldCrontab as $index => $ligne) /* copie $oldCrontab dans $newCrontab et ajoute le nouveau script */ {
            if ($ligne == $this->debut) {
                $isSection = true;
                continue;
            }

            if ($ligne == $this->fin) {
                $isSection = false;
                break;
            }

            if ($isSection) {
                $elem = explode(" ", $ligne);

                if ($elem[0] === "#") {
                    $id = $elem[1];
                    continue;
                }

                $tab[$id] = $ligne;
            }
        }



        return ($tab);
    }

/**
 * Handle crontab state through `monitor`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for monitor.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::monitor()
 * @example /fr/crontab/monitor
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function monitor($param) {
        $this->view = false;

        $php = explode(" ", shell_exec("whereis php"))[1];

        $cmd = $php . " " . GLIAL_INDEX . " " . implode(" ", $param);
        passthru($cmd, $code_retour);


        return $code_retour;
    }

}

