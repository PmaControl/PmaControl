<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class sharding extends Model
{
var $schema = "CREATE TABLE `sharding` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `prefix` varchar(20) NOT NULL,
  `date` datetime NOT NULL,
  `table_link` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prefix` (`prefix`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8";

var $field = array("date","id","prefix","table_link","name");

var $validate = array(
	'date' => array(
		'dateTime' => array('This must be a datetime.')
	),
);

function get_validate()
{
return $this->validate;
}
}
