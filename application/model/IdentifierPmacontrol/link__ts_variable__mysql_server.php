<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class link__ts_variable__mysql_server extends Model
{
var $schema = "CREATE TABLE `link__ts_variable__mysql_server` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `id_ts_variable` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_gg` (`id_mysql_server`,`id_ts_variable`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

var $field = array("id_ts_variable","id","id_mysql_server");

var $validate = array(
	'id_ts_variable' => array(
		'reference_to' => array('The constraint to ts_variable.id isn\'t respected.','ts_variable', 'id')
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
