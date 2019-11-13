<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class pmacli_drain_item extends Model
{
var $schema = "CREATE TABLE `pmacli_drain_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_pmacli_drain_process` int(11) NOT NULL,
  `row` int(11) NOT NULL,
  `table` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_pmacli_drain_process` (`id_pmacli_drain_process`,`table`),
  CONSTRAINT `pmacli_drain_item_ibfk_1` FOREIGN KEY (`id_pmacli_drain_process`) REFERENCES `pmacli_drain_process` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

var $field = array("id","id_pmacli_drain_process","row","table");

var $validate = array(
	'id_pmacli_drain_process' => array(
		'reference_to' => array('The constraint to pmacli_drain_process.id isn\'t respected.','pmacli_drain_process', 'id')
	),
	'row' => array(
		'numeric' => array('This must be an int.')
	),
);

function get_validate()
{
return $this->validate;
}
}
