<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class link__architecture__mysql_server extends Model
{
var $schema = "CREATE TABLE `link__architecture__mysql_server` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `id_architecture` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_mysql_server` (`id_mysql_server`,`id_architecture`),
  KEY `id_architecture` (`id_architecture`),
  CONSTRAINT `link__architecture__mysql_server_ibfk_1` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `link__architecture__mysql_server_ibfk_2` FOREIGN KEY (`id_architecture`) REFERENCES `architecture` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

var $field = array("id_architecture","id","id_mysql_server");

var $validate = array(
	'id_architecture' => array(
		'reference_to' => array('The constraint to architecture.id isn\'t respected.','architecture', 'id')
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
