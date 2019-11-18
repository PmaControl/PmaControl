<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class geolocalisation_city extends Model
{
var $schema = "CREATE TABLE `geolocalisation_city` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_geolocalisation_country` int(11) NOT NULL,
  `id_country` varchar(10) NOT NULL DEFAULT '0',
  `libelle` varchar(250) NOT NULL,
  `capital` enum('0','1') NOT NULL DEFAULT '0',
  `population` int(10) unsigned NOT NULL DEFAULT 0,
  `latitude` float NOT NULL DEFAULT 0,
  `longitude` float NOT NULL DEFAULT 0,
  `code_zip` varchar(100) NOT NULL,
  `code_insee` varchar(100) NOT NULL,
  `distance` float NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_geolocalisation_country` (`id_geolocalisation_country`),
  KEY `libelle` (`libelle`),
  CONSTRAINT `geolocalisation_city_ibfk_1` FOREIGN KEY (`id_geolocalisation_country`) REFERENCES `geolocalisation_country` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=198798 DEFAULT CHARSET=utf8";

var $field = array("id","population","distance","id_geolocalisation_country","latitude","longitude","capital","id_country","code_insee","code_zip","libelle");

var $validate = array(
	'population' => array(
		'numeric' => array('This must be an int.')
	),
	'distance' => array(
		'decimal' => array('This must be a float.')
	),
	'id_geolocalisation_country' => array(
		'reference_to' => array('The constraint to geolocalisation_country.id isn\'t respected.','geolocalisation_country', 'id')
	),
	'latitude' => array(
		'decimal' => array('This must be a float.')
	),
	'longitude' => array(
		'decimal' => array('This must be a float.')
	),
	'id_country' => array(
		'reference_to' => array('The constraint to country.id isn\'t respected.','country', 'id')
	),
);

function get_validate()
{
return $this->validate;
}
}
