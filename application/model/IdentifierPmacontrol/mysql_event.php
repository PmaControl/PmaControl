<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class mysql_event extends Model
{
var $schema = "CREATE TABLE `mysql_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `id_mysql_status` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `message` varchar(250) NOT NULL,
  `serialized` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_mysql_server` (`id_mysql_server`),
  KEY `id_mysql_server_2` (`id_mysql_server`),
  KEY `id_mysql_status` (`id_mysql_status`),
  CONSTRAINT `mysql_event_ibfk_1` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mysql_event_ibfk_2` FOREIGN KEY (`id_mysql_status`) REFERENCES `mysql_status` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

var $field = array("id","id_mysql_server","id_mysql_status","date","message","serialized");

var $validate = array(
	'id_mysql_server' => array(
		'reference_to' => array('The constraint to mysql_server.id isn\'t respected.','mysql_server', 'id')
	),
	'id_mysql_status' => array(
		'reference_to' => array('The constraint to mysql_status.id isn\'t respected.','mysql_status', 'id')
	),
	'date' => array(
		'dateTime' => array('This must be a datetime.')
	),
);

function get_validate()
{
return $this->validate;
}
}
