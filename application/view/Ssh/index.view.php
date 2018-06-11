<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

echo '<div class="row" style="padding:10px; margin: 5px;">';
//echo '<div class="col-md-6">This is a list of SSH keys associated with your account. Remove any keys that you do not recognize.</div>';


echo '<div class="col-md-12" style="text-align:right">';
echo '<a href="'.LINK.'ssh/add" type="button" class="btn btn-success">'.__('New SSH key').'</a>';
echo '</div>';


echo '</div>';


foreach ($data['keys'] as $key) {
    echo '<div class="row" style="font-size:14px; border:#666 1px solid; padding:10px; margin: 10px 5px 0 5px; border-radius: 3px;">';


    echo '<div class="col-md-2 text-center">';

    echo '<svg height="32" class="octicon octicon-key" viewBox="0 0 14 16" version="1.1" width="28" aria-hidden="true"><path fill-rule="evenodd" d="M12.83 2.17C12.08 1.42 11.14 1.03 10 1c-1.13.03-2.08.42-2.83 1.17S6.04 3.86 6.01 5c0 .3.03.59.09.89L0 12v1l1 1h2l1-1v-1h1v-1h1v-1h2l1.09-1.11c.3.08.59.11.91.11 1.14-.03 2.08-.42 2.83-1.17S13.97 6.14 14 5c-.03-1.14-.42-2.08-1.17-2.83zM11 5.38c-.77 0-1.38-.61-1.38-1.38 0-.77.61-1.38 1.38-1.38.77 0 1.38.61 1.38 1.38 0 .77-.61 1.38-1.38 1.38z"></path></svg>';



    //echo '<i class="fa fa-key fa-5a" aria-hidden="true"></i>';

    echo '<br /><span class="badge">SSH</span></div>';
    echo '<div class="col-md-3"><b>User :</b> '.$key['user'].'</div>';
    echo '<div class="col-md-5"><b>Fingerprint:</b> '.implode(':', str_split($key['fingerprint'], 2)).''
    .'<br />'
    .'Added on : '.$key['added_on']
    .'</div>';
    echo '<div class="col-md-2">';

    echo '<a href="'.LINK.'ssh/delete/'.$key['id'].'" type="button" class="btn btn-danger">'.__('Delete').'</a>';
    echo '</div>';

    echo '<div class="col-md-12" style="padding-top:10px">';

    /*
     * green : #449D44
     * red : #C9302C
     */
    foreach ($data['servers'] as $server) {
        if ($server['active'] == "1") {
            $class = 'label-success';
        } else {
            $class = 'label-primary';
        }


        echo '<span class="label '.$class.'" style="line-height:22px">'.$server['display_name'].'</span> ';
    }

    echo '</div>';


    echo '</div>';
}
