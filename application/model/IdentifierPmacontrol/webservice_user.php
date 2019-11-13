<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class webservice_user extends Model
{
var $schema = "CREATE TABLE `webservice_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_client` int(11) NOT NULL DEFAULT 1,
  `user` varchar(64) NOT NULL,
  `host` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_enabled` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user` (`user`,`host`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

var $field = array("id","is_enabled","id_client","user","password","host");

var $validate = array(
	'is_enabled' => array(
		'numeric' => array('This must be an int.')
	),
	'id_client' => array(
		'reference_to' => array('The constraint to client.id isn\'t respected.','client', 'id')
	),
);

function get_validate()
{
return $this->validate;
}
}
