<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class architecture_legend extends Model
{
var $schema = "CREATE TABLE `architecture_legend` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `const` varchar(30) NOT NULL,
  `name` varchar(50) NOT NULL,
  `color` varchar(20) NOT NULL,
  `style` varchar(20) NOT NULL,
  `order` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  `condition` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=latin1";

var $field = array("id","order","color","style","type","const","name","condition");

var $validate = array(
	'order' => array(
		'numeric' => array('This must be an int.')
	),
);

function get_validate()
{
return $this->validate;
}
}
