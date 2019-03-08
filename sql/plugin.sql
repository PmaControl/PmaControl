-- MySQL dump 10.17  Distrib 10.3.13-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: pmacontrol
-- ------------------------------------------------------
-- Server version	10.3.13-MariaDB-1:10.3.13+maria~xenial-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `plugin_main`
--

DROP TABLE IF EXISTS `plugin_main`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plugin_main` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(32) NOT NULL,
  `description` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL COMMENT 'le lien de l''image',
  `repertoire` varchar(32) NOT NULL,
  `date_installation` datetime NOT NULL,
  `md5_zip` binary(16) NOT NULL,
  `version` char(10) NOT NULL,
  `est_actif` int(11) NOT NULL,
  `numero_licence` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plugin_main`
--

LOCK TABLES `plugin_main` WRITE;
/*!40000 ALTER TABLE `plugin_main` DISABLE KEYS */;
/*!40000 ALTER TABLE `plugin_main` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plugin_file`
--

DROP TABLE IF EXISTS `plugin_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plugin_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_plugin_main` int(11) NOT NULL,
  `file` varchar(255) NOT NULL,
  `md5` binary(16) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_plugin_main` (`id_plugin_main`),
  CONSTRAINT `plugin_file_ibfk_1` FOREIGN KEY (`id_plugin_main`) REFERENCES `plugin_main` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plugin_file`
--

LOCK TABLES `plugin_file` WRITE;
/*!40000 ALTER TABLE `plugin_file` DISABLE KEYS */;
/*!40000 ALTER TABLE `plugin_file` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-03-07 10:39:28
