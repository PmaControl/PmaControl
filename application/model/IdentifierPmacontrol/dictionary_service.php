<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class dictionary_service extends Model
{
var $schema = "CREATE TABLE `dictionary_service` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_dictionary_serveur` int(11) NOT NULL,
  `port_default` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

var $field = array("port_default","id","id_dictionary_serveur","name");

var $validate = array(
	'port_default' => array(
		'numeric' => array('This must be an int.')
	),
	'id_dictionary_serveur' => array(
		'reference_to' => array('The constraint to dictionary_serveur.id isn\'t respected.','dictionary_serveur', 'id')
	),
);

function get_validate()
{
return $this->validate;
}
}
