<?php

use \Glial\Synapse\Controller;

class Crontab extends Controller
{
    public $module_group = "Administration";
    var $debut        = '#Les lignes suivantes sont gerees automatiquement via un script PHP. - Merci de ne pas editer manuellement';
    var $fin          = '#Les lignes suivantes ne sont plus gerees automatiquement';

    function index()
    {
        
    }

    function admin_crontab()
    {
        $module['picture']     = "administration/iconAttendance.gif";
        $module['name']        = __("Crontab");
        $module['description'] = __("Manage all yours jobs");

        //if (from() !== "administration.controller.php") {


        $this->javascript = array("jquery.1.3.2.js");

        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            if (!empty($_POST['crontab']['command'])) {

                $regexp = $this->buildRegexp();

                $ligne = $_POST['crontab']['minute']." ".$_POST['crontab']['hour']." ".$_POST['crontab']['dayofmonth']." ".$_POST['crontab']['month']." ".$_POST['crontab']['dayofweek']." ".$_POST['crontab']['command'];

                if (preg_match("/$regexp/", $ligne)) {
                    set_flash("success", "Added", "This tasks has beend added in the crontab");

                    $this->add($_POST['crontab']['minute'], $_POST['crontab']['hour'], $_POST['crontab']['dayofmonth'], $_POST['crontab']['month'], $_POST['crontab']['dayofweek'],
                        $_POST['crontab']['command'], "commentaire =)");

                    header("location: ".$_SERVER['REQUEST_URI']);
                    die();
                } else {
                    set_flash("error", "Error", "This crontab is not valid : ".$ligne);


                    $ret = array();
                    foreach ($_POST['crontab'] as $var => $val) {
                        $ret[] = "crontab:".$var.":".$val;
                    }

                    $param = implode("/", $ret);


                    header("location: ".LINK.__CLASS__."/".__FUNCTION__."/".$param);

                    die();
                }
            }

            if (!empty($_POST['crontab']['delete'])) {
                set_flash("success", "Removed", "This task has been removed");
                $this->delete($_POST['crontab']['delete']);
            }
        }

        //$this->layout_name = "admin";


        $this->title  = __("Crontab");
        $this->ariane = "> <a href=\"".LINK."administration/\">".__("Administration")."</a> > ".$this->title;
        $data         = $this->view();
        $this->set("data", $data);
        //}

        return $module;
    }

    private function view()
    {
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

    public function monitor($param)
    {
        $this->view = false;

        $php = explode(" ", shell_exec("whereis php"))[1];

        $cmd = $php." ".GLIAL_INDEX." ".implode(" ", $param);
        passthru($cmd, $code_retour);


        return $code_retour;
    }
}