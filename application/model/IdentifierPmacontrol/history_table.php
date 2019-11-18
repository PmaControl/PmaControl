<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class history_table extends Model
{
var $schema = "CREATE TABLE `history_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `date_insterted` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

var $field = array("date_insterted","id","name");

var $validate = array(
	'date_insterted' => array(
		'dateTime' => array('This must be a datetime.')
	),
);

function get_validate()
{
return $this->validate;
}
}
