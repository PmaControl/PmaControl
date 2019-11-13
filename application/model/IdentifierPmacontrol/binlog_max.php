<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class binlog_max extends Model
{
var $schema = "CREATE TABLE `binlog_max` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `size_max` bigint(20) NOT NULL,
  `number_file_max` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_mysql_server` (`id_mysql_server`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8";

var $field = array("id","id_mysql_server","size_max","number_file_max");

var $validate = array(
	'id_mysql_server' => array(
		'reference_to' => array('The constraint to mysql_server.id isn\'t respected.','mysql_server', 'id')
	),
	'size_max' => array(
		'numeric' => array('This must be an int.')
	),
	'number_file_max' => array(
		'numeric' => array('This must be an int.')
	),
);

function get_validate()
{
return $this->validate;
}
}
