<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class menu extends Model
{
var $schema = "CREATE TABLE `menu` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` tinyint(3) unsigned DEFAULT NULL,
  `bg` int(11) NOT NULL,
  `bd` int(11) NOT NULL,
  `active` int(11) NOT NULL DEFAULT 1,
  `icon` text NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `class` varchar(255) NOT NULL DEFAULT '',
  `method` varchar(255) NOT NULL DEFAULT '',
  `position` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `group_id` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `level` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `class` (`class`,`method`,`bg`,`bd`)
) ENGINE=InnoDB AUTO_INCREMENT=183 DEFAULT CHARSET=latin1";

var $field = array("group_id","bg","bd","active","id","position","parent_id","title","url","level","class","method","icon");

var $validate = array(
	'group_id' => array(
		'numeric' => array('This must be an int.')
	),
	'bg' => array(
		'numeric' => array('This must be an int.')
	),
	'bd' => array(
		'numeric' => array('This must be an int.')
	),
	'active' => array(
		'numeric' => array('This must be an int.')
	),
	'position' => array(
		'numeric' => array('This must be an int.')
	),
	'parent_id' => array(
		'numeric' => array('This must be an int.')
	),
);

function get_validate()
{
return $this->validate;
}
}
