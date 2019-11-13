<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class haproxy_main_output extends Model
{
var $schema = "CREATE TABLE `haproxy_main_output` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_haproxy_input` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `port` int(11) NOT NULL,
  `check_port` int(11) NOT NULL,
  `extra` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_haproxy_input` (`id_haproxy_input`),
  CONSTRAINT `haproxy_main_output_ibfk_1` FOREIGN KEY (`id_haproxy_input`) REFERENCES `haproxy_main_input` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

var $field = array("port","id","check_port","id_haproxy_input","ip","name","extra");

var $validate = array(
	'port' => array(
		'numeric' => array('This must be an int.')
	),
	'check_port' => array(
		'numeric' => array('This must be an int.')
	),
	'id_haproxy_input' => array(
		'reference_to' => array('The constraint to haproxy_input.id isn\'t respected.','haproxy_input', 'id')
	),
	'ip' => array(
		'ip' => array('your IP is not valid')
	),
);

function get_validate()
{
return $this->validate;
}
}
