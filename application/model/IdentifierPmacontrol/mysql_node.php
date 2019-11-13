<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class mysql_node extends Model
{
var $schema = "CREATE TABLE `mysql_node` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_galera` int(11) NOT NULL,
  `id_mysql_server` int(11) NOT NULL,
  `size` int(11) NOT NULL,
  `ready` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `nodes` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_mysql_galera` (`id_mysql_galera`),
  KEY `id_mysql_server` (`id_mysql_server`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

var $field = array("ready","id","status","id_mysql_galera","id_mysql_server","size","nodes");

var $validate = array(
	'ready' => array(
		'numeric' => array('This must be an int.')
	),
	'status' => array(
		'numeric' => array('This must be an int.')
	),
	'id_mysql_galera' => array(
		'reference_to' => array('The constraint to mysql_galera.id isn\'t respected.','mysql_galera', 'id')
	),
	'id_mysql_server' => array(
		'reference_to' => array('The constraint to mysql_server.id isn\'t respected.','mysql_server', 'id')
	),
	'size' => array(
		'numeric' => array('This must be an int.')
	),
);

function get_validate()
{
return $this->validate;
}
}
