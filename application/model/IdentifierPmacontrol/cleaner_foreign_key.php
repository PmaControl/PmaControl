<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class cleaner_foreign_key extends Model
{
var $schema = "CREATE TABLE `cleaner_foreign_key` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_cleaner_main` int(11) NOT NULL,
  `constraint_schema` varchar(64) NOT NULL,
  `constraint_table` varchar(64) NOT NULL,
  `constraint_column` varchar(64) NOT NULL,
  `referenced_schema` varchar(64) NOT NULL,
  `referenced_table` varchar(64) NOT NULL,
  `referenced_column` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_cleaner_main` (`id_cleaner_main`),
  CONSTRAINT `cleaner_foreign_key_ibfk_1` FOREIGN KEY (`id_cleaner_main`) REFERENCES `cleaner_main` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

var $field = array("id","id_cleaner_main","constraint_schema","referenced_column","constraint_table","constraint_column","referenced_schema","referenced_table");

var $validate = array(
	'id_cleaner_main' => array(
		'reference_to' => array('The constraint to cleaner_main.id isn\'t respected.','cleaner_main', 'id')
	),
);

function get_validate()
{
return $this->validate;
}
}
