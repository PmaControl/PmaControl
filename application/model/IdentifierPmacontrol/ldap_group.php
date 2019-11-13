<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class ldap_group extends Model
{
var $schema = "CREATE TABLE `ldap_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_group` int(11) NOT NULL,
  `cn` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_group` (`id_group`),
  CONSTRAINT `ldap_group_ibfk_1` FOREIGN KEY (`id_group`) REFERENCES `group` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

var $field = array("id","id_group","cn");

var $validate = array(
	'id_group' => array(
		'reference_to' => array('The constraint to group.id isn\'t respected.','group', 'id')
	),
);

function get_validate()
{
return $this->validate;
}
}
