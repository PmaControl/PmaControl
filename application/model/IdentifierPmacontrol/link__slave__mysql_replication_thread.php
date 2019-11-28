<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class link__slave__mysql_replication_thread extends Model
{
var $schema = "CREATE TABLE `link__slave__mysql_replication_thread` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_slave_name` int(11) NOT NULL,
  `id_mysql_replication_thread` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

var $field = array("id_mysql_replication_thread","type","id","id_slave_name");

var $validate = array(
	'id_mysql_replication_thread' => array(
		'reference_to' => array('The constraint to mysql_replication_thread.id isn\'t respected.','mysql_replication_thread', 'id')
	),
	'type' => array(
		'numeric' => array('This must be an int.')
	),
	'id_slave_name' => array(
		'reference_to' => array('The constraint to slave_name.id isn\'t respected.','slave_name', 'id')
	),
);

function get_validate()
{
return $this->validate;
}
}
