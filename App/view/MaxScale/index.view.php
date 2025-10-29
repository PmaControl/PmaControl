<?php


use App\Controller\Common;
use \App\Library\Display;
use \App\Library\Html;
use \Glial\Synapse\FactoryController;
use \App\Library\Chiffrement;

//debug($data['proxysql']);
echo '<div class="row" style="padding:10px; margin: 5px;">';
//echo '<div class="col-md-6">This is a list of SSH keys associated with your account. Remove any keys that you do not recognize.</div>';

echo '<div class="col-md-12" style="text-align:right">';
echo '<a href="'.LINK.'Maxscale/add" type="button" class="btn btn-success">'.__('New Maxscale Server').'</a>';
echo '</div>';
echo '</div>';


if ( ! empty($data['maxscale']))
{
    foreach ($data['maxscale'] as $id_proxysql => $maxscale) {
        echo '<div class="row" style="font-size:14px; border:#666 1px solid; padding:10px; margin: 10px 5px 0 5px; border-radius: 3px;">';

        $id_proxysql++;
        
        echo '<div class="col-md-5">';

        echo '<div class="row">';
        echo '<div class="col-md-1 text-center" style="display: flex; justify-content: center; align-items: center; ">';
        echo '<img src="'.IMG.'icon/maxscale.png" height="48px" width="48px">';
        echo '</div>';

        echo '<div class="col-md-5">';
        echo 'MaxScale Admin <b><a href="">'.$maxscale['hostname'].":".$maxscale['port']."</a></b>";
        echo '</div>';

        echo '<div class="col-md-5">';
        echo "Login: ".$maxscale['login'];
 
        echo " - Password : ";
        
        //.$maxscale['password'];
        FactoryController::addNode("Server", "passwd", array(Chiffrement::encrypt($maxscale['password'])));


        echo '</div>';
        
        echo '</div>';
        echo '<div class="row">&nbsp;</div>';
        echo '<div class="row">';
        //boutton

        $menus = ["Servers" => "link", "Current Sessions" => "", "Services" => "", "Listeners" => "", "Filters" => ""];

        foreach($menus as $title => $link) {
            echo '<a href="'.LINK.'MaxScale/menu/'.$link.'" type="button" class="btn btn-default">'.__($title).'</a> ';
        }


        

        echo '</div>';
        echo '<div class="row">&nbsp;</div>';
        

        //frontend

        if (! empty($maxscale['id_mysql_servers']))
        {
            $ids = explode(',',$maxscale['id_mysql_servers']);

            foreach( $ids as $id_mysql_server)
            {
                if (! empty($data['extra'][$id_mysql_server]))
                {
                    $extra = $data['extra'][$id_mysql_server];
                    //debug($extra);
                }


                echo '<div class="row">';
                $thead = Html::thead(array(__("Hostname"), __("Status")));
                if (empty($extra['mysql_available'])) {
                    if (!empty($extra['mysql_error'])){
                        $status = '<big><span class="label label-danger">'.$extra['mysql_error'].'</span></big>';
                    }
                    else
                    {
                        $status = '<big><span class="label label-warning">'.__("NO DATA").'</span></big>';
                    }
                    
                }
                elseif ($extra['mysql_available'] === "1"){
                    $status = '<big><span class="label label-success">'.__("ONLINE").'</span></big>';
                }

                $tbody = Html::tbody(array(Display::srv($id_mysql_server, true, LINK."maxscale/".$id_mysql_server),$status));
                
                $body = Html::table(
                    $thead,
                    $tbody,
                    
                );
                echo Html::box(__('Frontend'),$body );
                echo '</div>';
            }
        }



        /******************** */



        /*********** */


        echo '</div>';


        echo '</div>';
    }
}