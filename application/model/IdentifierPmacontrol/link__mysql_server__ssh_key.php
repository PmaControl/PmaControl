<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class link__mysql_server__ssh_key extends Model
{
var $schema = "CREATE TABLE `link__mysql_server__ssh_key` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `id_ssh_key` int(11) NOT NULL,
  `added_on` datetime NOT NULL,
  `active` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_mysql_server` (`id_mysql_server`),
  KEY `id_ssh_key` (`id_ssh_key`),
  CONSTRAINT `link__mysql_server__ssh_key_ibfk_1` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`) ON DELETE CASCADE,
  CONSTRAINT `link__mysql_server__ssh_key_ibfk_2` FOREIGN KEY (`id_ssh_key`) REFERENCES `ssh_key` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

var $field = array("id_ssh_key","added_on","active","id","id_mysql_server");

var $validate = array(
	'id_ssh_key' => array(
		'reference_to' => array('The constraint to ssh_key.id isn\'t respected.','ssh_key', 'id')
	),
	'added_on' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'active' => array(
		'numeric' => array('This must be an int.')
	),
	'id_mysql_server' => array(
		'reference_to' => array('The constraint to mysql_server.id isn\'t respected.','mysql_server', 'id')
	),
);

function get_validate()
{
return $this->validate;
}
}
