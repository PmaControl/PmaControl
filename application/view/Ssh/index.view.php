<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

echo '<div class="row" style="padding:10px; margin: 5px;">';
echo '<div class="col-md-6">This is a list of SSH keys associated with your account. Remove any keys that you do not recognize.</div>';


echo '<div class="col-md-6" style="text-align:right">';
echo '<a href="" type="button" class="btn btn-success">'.__('New SSH key').'</a>';
echo '</div>';


echo '</div>';


foreach($data['keys'] as $key)
{
    echo '<div class="row" style="font-size:14px; border:#666 1px solid; padding:10px; margin: 10px 5px 0 5px; border-radius: 3px;">';
    echo '<div class="pma-item">';

        echo '<div class="col-md-2 text-center"><i class="fa fa-key fa-5a" aria-hidden="true"></i><br /><span class="badge">SSH</span></div>';
         echo '<div class="col-md-3"><b>User :</b> '.$key['user'].'</div>';
        echo '<div class="col-md-5"><b>Fingerprint:</b> '.implode(':',str_split($key['fingerprint'],2)).''
            . '<br />'
            . 'Added on : '.$key['added_on']
            . '</div>';
        echo '<div class="col-md-2">';

        echo '<a href="'.LINK.'ssh/delete/'.$key['id'].'" type="button" class="btn btn-danger">'.__('Delete').'</a>';
        echo '</div>';

    echo '</div>';

    echo '</div>';

}
