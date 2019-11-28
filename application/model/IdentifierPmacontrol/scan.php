<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class scan extends Model
{
var $schema = "CREATE TABLE `scan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` char(15) NOT NULL,
  `ms` float NOT NULL,
  `date` datetime NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

var $field = array("ms","date","id","ip","data");

var $validate = array(
	'ms' => array(
		'decimal' => array('This must be a float.')
	),
	'date' => array(
		'dateTime' => array('This must be a datetime.')
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
