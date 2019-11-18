<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class dictionary_port extends Model
{
var $schema = "CREATE TABLE `dictionary_port` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_dictionary_serveur` int(11) NOT NULL,
  `id_dictionary_service` int(11) NOT NULL,
  `port` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

var $field = array("id_dictionary_service","port","id","id_dictionary_serveur");

var $validate = array(
	'id_dictionary_service' => array(
		'reference_to' => array('The constraint to dictionary_service.id isn\'t respected.','dictionary_service', 'id')
	),
	'port' => array(
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
