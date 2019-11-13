<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class objet extends Model
{
var $schema = "CREATE TABLE `objet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(64) NOT NULL,
  `reference_id` int(11) NOT NULL,
  `ip` char(15) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

var $field = array("id","reference_id","ip","table_name");

var $validate = array(
	'reference_id' => array(
		'numeric' => array('This must be an int.')
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
