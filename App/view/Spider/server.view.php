<?php 

if (!empty($data['no_spider']))
{

        echo '<div class="well" style="border-left-color: #d9534f;   border-left-width: 5px;">';
        echo '<p><b>'.__('IMPORTANT !').'</b></p>';
        echo __('We coudldn\'t found any table with Spider engine on this MySQL server');
        echo '</div>';
        echo '<br>';
}
else
{


	debug($data);


}



?>
