<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class export_option extends Model
{
var $schema = "CREATE TABLE `export_option` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(64) NOT NULL,
  `key` varchar(32) NOT NULL,
  `active` int(11) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `config_file` varchar(64) NOT NULL,
  `crypted_fields` varchar(250) NOT NULL COMMENT 'coma separated',
  `splited_fields` varchar(255) NOT NULL,
  `sql` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1";

var $field = array("id","active","key","table_name","config_file","libelle","crypted_fields","splited_fields","sql");

var $validate = array(
	'active' => array(
		'numeric' => array('This must be an int.')
	),
);

function get_validate()
{
return $this->validate;
}
}
