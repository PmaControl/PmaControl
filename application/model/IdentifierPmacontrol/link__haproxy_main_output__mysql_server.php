<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class link__haproxy_main_output__mysql_server extends Model
{
var $schema = "CREATE TABLE `link__haproxy_main_output__mysql_server` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_haproxy_output` int(11) NOT NULL,
  `id_mysql_server` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_haproxy_output` (`id_haproxy_output`),
  KEY `id_mysql_server` (`id_mysql_server`),
  CONSTRAINT `link__haproxy_main_output__mysql_server_ibfk_1` FOREIGN KEY (`id_haproxy_output`) REFERENCES `haproxy_main_output` (`id`),
  CONSTRAINT `link__haproxy_main_output__mysql_server_ibfk_2` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

var $field = array("id_mysql_server","id","id_haproxy_output");

var $validate = array(
	'id_mysql_server' => array(
		'reference_to' => array('The constraint to mysql_server.id isn\'t respected.','mysql_server', 'id')
	),
	'id_haproxy_output' => array(
		'reference_to' => array('The constraint to haproxy_output.id isn\'t respected.','haproxy_output', 'id')
	),
);

function get_validate()
{
return $this->validate;
}
}
