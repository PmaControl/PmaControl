<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class link__master__mysql_server extends Model
{
var $schema = "CREATE TABLE `link__master__mysql_server` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_master_name` int(11) NOT NULL,
  `id_mysql_server` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

var $field = array("id_mysql_server","type","id","id_master_name");

var $validate = array(
	'id_mysql_server' => array(
		'reference_to' => array('The constraint to mysql_server.id isn\'t respected.','mysql_server', 'id')
	),
	'type' => array(
		'numeric' => array('This must be an int.')
	),
	'id_master_name' => array(
		'reference_to' => array('The constraint to master_name.id isn\'t respected.','master_name', 'id')
	),
);

function get_validate()
{
return $this->validate;
}
}
