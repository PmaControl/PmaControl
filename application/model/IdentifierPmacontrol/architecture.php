<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class architecture extends Model
{
var $schema = "CREATE TABLE `architecture` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `data` mediumblob NOT NULL,
  `display` mediumblob NOT NULL,
  `height` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

var $field = array("height","id","width","date","data","display");

var $validate = array(
	'height' => array(
		'numeric' => array('This must be an int.')
	),
	'width' => array(
		'numeric' => array('This must be an int.')
	),
	'date' => array(
		'dateTime' => array('This must be a datetime.')
	),
);

function get_validate()
{
return $this->validate;
}
}
