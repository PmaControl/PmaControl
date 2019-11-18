<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class daemon_worker extends Model
{
var $schema = "CREATE TABLE `daemon_worker` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_daemon_main` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_daemon_main` (`id_daemon_main`),
  CONSTRAINT `daemon_worker_ibfk_1` FOREIGN KEY (`id_daemon_main`) REFERENCES `daemon_main` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

var $field = array("pid","id","id_daemon_main");

var $validate = array(
	'pid' => array(
		'numeric' => array('This must be an int.')
	),
	'id_daemon_main' => array(
		'reference_to' => array('The constraint to daemon_main.id isn\'t respected.','daemon_main', 'id')
	),
);

function get_validate()
{
return $this->validate;
}
}
