<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class plugin_main extends Model
{
var $schema = "CREATE TABLE `plugin_main` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(32) NOT NULL,
  `description` text NOT NULL,
  `auteur` varchar(100) NOT NULL,
  `image` varchar(255) NOT NULL COMMENT 'le lien de l''image',
  `fichier` varchar(255) NOT NULL,
  `date_installation` datetime NOT NULL,
  `md5_zip` varchar(32) NOT NULL,
  `version` char(10) NOT NULL,
  `est_actif` int(11) NOT NULL DEFAULT 0,
  `type_licence` varchar(50) NOT NULL,
  `numero_licence` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`,`version`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1";

var $field = array("est_actif","id","date_installation","version","nom","md5_zip","type_licence","auteur","fichier","numero_licence","image","description");

var $validate = array(
	'est_actif' => array(
		'numeric' => array('This must be an int.')
	),
	'date_installation' => array(
		'dateTime' => array('This must be a datetime.')
	),
);

function get_validate()
{
return $this->validate;
}
}
