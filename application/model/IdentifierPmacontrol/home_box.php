<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class home_box extends Model
{
var $schema = "CREATE TABLE `home_box` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `icon` varchar(100) NOT NULL,
  `class` varchar(50) NOT NULL,
  `method` varchar(50) NOT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `class` (`class`,`method`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1";

var $field = array("id","order","method","title","class","icon");

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
