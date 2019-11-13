<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class version extends Model
{
var $schema = "CREATE TABLE `version` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `version` varchar(20) NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8";

var $field = array("id","date","version","comment");

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
