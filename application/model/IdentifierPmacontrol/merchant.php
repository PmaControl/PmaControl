<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class merchant extends Model
{
var $schema = "CREATE TABLE `merchant` (
  `merch_id` int(11) NOT NULL AUTO_INCREMENT,
  `merch_uuid` varchar(36) NOT NULL,
  `merch_name` varchar(255) NOT NULL,
  `store_external_id` int(11) NOT NULL,
  PRIMARY KEY (`merch_id`),
  UNIQUE KEY `merch_uuid` (`merch_uuid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

var $field = array("merch_id","store_external_id","merch_uuid","merch_name");

var $validate = array(
	'merch_id' => array(
		'numeric' => array('This must be an int.')
	),
	'store_external_id' => array(
		'numeric' => array('This must be an int.')
	),
);

function get_validate()
{
return $this->validate;
}
}
