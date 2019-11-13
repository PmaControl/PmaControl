<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class backup_storage_area extends Model
{
var $schema = "CREATE TABLE `backup_storage_area` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_geolocalisation_city` int(11) NOT NULL,
  `id_geolocalisation_country` int(11) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `port` int(11) NOT NULL,
  `path` text NOT NULL,
  `libelle` varchar(30) NOT NULL,
  `id_ssh_key` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_geolocalisation_city` (`id_geolocalisation_city`,`id_geolocalisation_country`),
  KEY `id_ssh_key` (`id_ssh_key`),
  CONSTRAINT `backup_storage_area_ibfk_1` FOREIGN KEY (`id_ssh_key`) REFERENCES `ssh_key` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

var $field = array("port","id","id_geolocalisation_city","id_geolocalisation_country","id_ssh_key","ip","libelle","path");

var $validate = array(
	'port' => array(
		'numeric' => array('This must be an int.')
	),
	'id_geolocalisation_city' => array(
		'reference_to' => array('The constraint to geolocalisation_city.id isn\'t respected.','geolocalisation_city', 'id')
	),
	'id_geolocalisation_country' => array(
		'reference_to' => array('The constraint to geolocalisation_country.id isn\'t respected.','geolocalisation_country', 'id')
	),
	'id_ssh_key' => array(
		'reference_to' => array('The constraint to ssh_key.id isn\'t respected.','ssh_key', 'id')
	),
	'ip' => array(
		'ip' => array('your IP is not valid')
	),
);

function get_validate()
{
return $this->validate;
}
}
