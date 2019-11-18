<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class group extends Model
{
var $schema = "CREATE TABLE `group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_parent` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

var $field = array("id","id_parent","name","description");

var $validate = array(
	'id_parent' => array(
		'reference_to' => array('The constraint to parent.id isn\'t respected.','parent', 'id')
	),
);

function get_validate()
{
return $this->validate;
}
}
