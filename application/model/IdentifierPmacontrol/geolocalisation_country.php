<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class geolocalisation_country extends Model
{
var $schema = "CREATE TABLE `geolocalisation_country` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_geolocalisation_continent` int(11) NOT NULL,
  `name_fr` varchar(250) NOT NULL,
  `name_eng` varchar(200) NOT NULL,
  `libelle` varchar(200) NOT NULL,
  `alias` varchar(200) NOT NULL,
  `name_webtitle` varchar(255) NOT NULL,
  `adj` varchar(200) NOT NULL,
  `iso` char(2) NOT NULL,
  `iso3` varchar(3) DEFAULT NULL,
  `population` int(10) unsigned NOT NULL DEFAULT 0,
  `latitude` float NOT NULL DEFAULT 0,
  `longitude` float NOT NULL DEFAULT 0,
  `description` text NOT NULL,
  `num_code` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `iso` (`iso`),
  KEY `id_geolocalisation_continent` (`id_geolocalisation_continent`),
  CONSTRAINT `geolocalisation_country_ibfk_1` FOREIGN KEY (`id_geolocalisation_continent`) REFERENCES `geolocalisation_continent` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=259 DEFAULT CHARSET=utf8";

var $field = array("id","population","id_geolocalisation_continent","latitude","longitude","iso","iso3","num_code","libelle","alias","adj","name_eng","name_fr","name_webtitle","description");

var $validate = array(
	'population' => array(
		'numeric' => array('This must be an int.')
	),
	'id_geolocalisation_continent' => array(
		'reference_to' => array('The constraint to geolocalisation_continent.id isn\'t respected.','geolocalisation_continent', 'id')
	),
	'latitude' => array(
		'decimal' => array('This must be a float.')
	),
	'longitude' => array(
		'decimal' => array('This must be a float.')
	),
);

function get_validate()
{
return $this->validate;
}
}
