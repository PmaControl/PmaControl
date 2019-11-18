<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class cleaner_table extends Model
{
var $schema = "CREATE TABLE `cleaner_table` (
  `id` int(11) NOT NULL,
  `id_cleaner_main` int(11) NOT NULL,
  `table_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_cleaner_main` (`id_cleaner_main`),
  CONSTRAINT `cleaner_table_ibfk_1` FOREIGN KEY (`id_cleaner_main`) REFERENCES `cleaner_main` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

var $field = array("id","id_cleaner_main","table_name");

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
