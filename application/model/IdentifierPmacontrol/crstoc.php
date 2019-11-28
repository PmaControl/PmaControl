<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class CrStoc extends Model
{
var $schema = "CREATE TABLE `CrStoc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ReferenceCommandePriseInterneOC` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `ReferencePrestationPrise` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `ReferenceCommandeSousTraitantOI` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `CrRaccordementPrise` varchar(2) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `MotifKoCrRaccordementPrise` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `ReferencePrise` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `DateRaccordementPrise` datetime DEFAULT NULL,
  `PrisePosee` varchar(3) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `BilanOptique` varchar(4096) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `Commentaire` varchar(512) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `NotificationReprovisioningHL` varchar(3) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `NumeroDecharge` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `commande_service_id` bigint(20) NOT NULL,
  `actif` int(11) NOT NULL,
  `Reserve1` varchar(70) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `Reserve2` varchar(70) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `Reserve3` varchar(70) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `Reserve4` varchar(70) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `Reserve5` varchar(70) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `Reserve6` varchar(70) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `Reserve7` varchar(70) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `Reserve8` varchar(70) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `Reserve9` varchar(70) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `Reserve10` varchar(70) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `Reserve11` varchar(70) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `Reserve12` varchar(70) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `Reserve13` varchar(70) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `Reserve14` varchar(70) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `Reserve15` varchar(70) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `Reserve16` varchar(70) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `Reserve17` varchar(70) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_7824D84759E8500` (`commande_service_id`)
) ENGINE=InnoDB AUTO_INCREMENT=89776 DEFAULT CHARSET=utf8";

var $field = array("commande_service_id","id","actif","DateRaccordementPrise","CrRaccordementPrise","PrisePosee","NotificationReprovisioningHL","Reserve12","Reserve3","Reserve17","Reserve8","Reserve13","Reserve4","Reserve9","Reserve14","Reserve5","Reserve10","Reserve1","Reserve15","Reserve6","Reserve11","Reserve2","Reserve16","Reserve7","ReferenceCommandeSousTraitantOI","MotifKoCrRaccordementPrise","ReferenceCommandePriseInterneOC","ReferencePrise","ReferencePrestationPrise","NumeroDecharge","Commentaire","BilanOptique");

var $validate = array(
	'commande_service_id' => array(
		'numeric' => array('This must be an int.')
	),
	'actif' => array(
		'numeric' => array('This must be an int.')
	),
	'DateRaccordementPrise' => array(
		'dateTime' => array('This must be a datetime.')
	),
);

function get_validate()
{
return $this->validate;
}
}
