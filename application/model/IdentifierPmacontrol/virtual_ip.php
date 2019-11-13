<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class virtual_ip extends Model
{
var $schema = "CREATE TABLE `virtual_ip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `ip` char(15) NOT NULL,
  `hostname` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_mysql_server` (`id_mysql_server`),
  CONSTRAINT `virtual_ip_ibfk_1` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

var $field = array("id","id_mysql_server","ip","hostname");

var $validate = array(
	'id_mysql_server' => array(
		'reference_to' => array('The constraint to mysql_server.id isn\'t respected.','mysql_server', 'id')
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
