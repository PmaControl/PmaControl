<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class client extends Model
{
var $schema = "CREATE TABLE `client` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(100) NOT NULL,
  `logo` varchar(250) NOT NULL DEFAULT '',
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `libelle` (`libelle`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

var $field = array("id","date","libelle","logo");

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
