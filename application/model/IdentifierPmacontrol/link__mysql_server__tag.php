<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class link__mysql_server__tag extends Model
{
var $schema = "CREATE TABLE `link__mysql_server__tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `id_tag` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_mysql_server` (`id_mysql_server`,`id_tag`),
  KEY `id_tag` (`id_tag`),
  CONSTRAINT `link__mysql_server__tag_ibfk_1` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `link__mysql_server__tag_ibfk_2` FOREIGN KEY (`id_tag`) REFERENCES `tag` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

var $field = array("id","id_mysql_server","id_tag");

var $validate = array(
	'id_mysql_server' => array(
		'reference_to' => array('The constraint to mysql_server.id isn\'t respected.','mysql_server', 'id')
	),
	'id_tag' => array(
		'reference_to' => array('The constraint to tag.id isn\'t respected.','tag', 'id')
	),
);

function get_validate()
{
return $this->validate;
}
}
