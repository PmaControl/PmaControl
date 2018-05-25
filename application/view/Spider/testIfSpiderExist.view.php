<?php


if (empty($data['spider_activated']))
{

	echo '<div class="well" style="border-left-color: #d9534f;   border-left-width: 5px;">';
	echo '<p><b>'.__('IMPORTANT !').'</b></p>';
	echo __('Spider is not installed on your server. Please refer to doucumentation of you server MySQL to install it properly.');
        echo '</div>';
}
