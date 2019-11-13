<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class myxplain extends Model
{
var $schema = "CREATE TABLE `myxplain` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `command` text NOT NULL,
  `explain` text NOT NULL,
  `duration` double NOT NULL,
  `date` datetime NOT NULL,
  `json` text NOT NULL DEFAULT '' COMMENT 'used in future for explain format=json',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

var $field = array("duration","id","date","name","json","command","explain");

var $validate = array(
	'date' => array(
		'dateTime' => array('This must be a datetime.')
	),
);

function get_validate()
{
return $this->validate;
}
}
