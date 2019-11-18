<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class alias_dns extends Model
{
var $schema = "CREATE TABLE `alias_dns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dns` varchar(200) NOT NULL,
  `port` int(11) NOT NULL,
  `destination` varchar(15) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dns` (`dns`,`port`,`destination`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT";

var $field = array("port","id","destination","dns");

var $validate = array(
	'port' => array(
		'numeric' => array('This must be an int.')
	),
);

function get_validate()
{
return $this->validate;
}
}
