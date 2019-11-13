<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class haproxy_main_input extends Model
{
var $schema = "CREATE TABLE `haproxy_main_input` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_haproxy_main` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `mask` varchar(15) NOT NULL,
  `port` int(11) NOT NULL,
  `mode` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_haproxy_main` (`id_haproxy_main`),
  CONSTRAINT `haproxy_main_input_ibfk_1` FOREIGN KEY (`id_haproxy_main`) REFERENCES `haproxy_main` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

var $field = array("port","id","id_haproxy_main","mask","mode","name");

var $validate = array(
	'port' => array(
		'numeric' => array('This must be an int.')
	),
	'id_haproxy_main' => array(
		'reference_to' => array('The constraint to haproxy_main.id isn\'t respected.','haproxy_main', 'id')
	),
);

function get_validate()
{
return $this->validate;
}
}
