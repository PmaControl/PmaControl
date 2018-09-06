-- MySQL dump 10.16  Distrib 10.3.9-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: marketplace
-- ------------------------------------------------------
-- Server version	10.3.9-MariaDB-1:10.3.9+maria~xenial-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `history__order__status`
--

DROP TABLE IF EXISTS `history__order__status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `history__order__status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_order` int(11) NOT NULL,
  `id_status` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_order` (`id_order`) USING BTREE,
  KEY `id_status` (`id_status`) USING BTREE,
  CONSTRAINT `order_ibfk_7` FOREIGN KEY (`id_order`) REFERENCES `order` (`id`),
  CONSTRAINT `order_ibfk_8` FOREIGN KEY (`id_status`) REFERENCES `status` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `history__order__status`
--

LOCK TABLES `history__order__status` WRITE;
/*!40000 ALTER TABLE `history__order__status` DISABLE KEYS */;
/*!40000 ALTER TABLE `history__order__status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `history__order_item__status`
--

DROP TABLE IF EXISTS `history__order_item__status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `history__order_item__status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_order_item` int(11) NOT NULL,
  `id_status` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_order_item` (`id_order_item`) USING BTREE,
  KEY `id_status` (`id_status`) USING BTREE,
  CONSTRAINT `order_item_ibfk_3` FOREIGN KEY (`id_order_item`) REFERENCES `order_item` (`id`),
  CONSTRAINT `order_item_ibfk_4` FOREIGN KEY (`id_status`) REFERENCES `status` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `history__order_item__status`
--

LOCK TABLES `history__order_item__status` WRITE;
/*!40000 ALTER TABLE `history__order_item__status` DISABLE KEYS */;
/*!40000 ALTER TABLE `history__order_item__status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `history__order_unit__status`
--

DROP TABLE IF EXISTS `history__order_unit__status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `history__order_unit__status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_order_unit` int(11) NOT NULL,
  `id_status` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_order_item_unit` (`id_order_unit`) USING BTREE,
  KEY `id_status` (`id_status`) USING BTREE,
  CONSTRAINT `order_item_unit_ibfk_5` FOREIGN KEY (`id_order_unit`) REFERENCES `order_unit` (`id`),
  CONSTRAINT `order_item_unit_ibfk_6` FOREIGN KEY (`id_status`) REFERENCES `status` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `history__order_unit__status`
--

LOCK TABLES `history__order_unit__status` WRITE;
/*!40000 ALTER TABLE `history__order_unit__status` DISABLE KEYS */;
/*!40000 ALTER TABLE `history__order_unit__status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `merchant`
--

DROP TABLE IF EXISTS `merchant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `merchant` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `name` varchar(64) NOT NULL,
  `store_external_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `merchant`
--

LOCK TABLES `merchant` WRITE;
/*!40000 ALTER TABLE `merchant` DISABLE KEYS */;
/*!40000 ALTER TABLE `merchant` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order`
--

DROP TABLE IF EXISTS `order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_purchase` int(11) NOT NULL,
  `id_merchant` int(11) NOT NULL,
  `id_status` int(11) NOT NULL,
  `uuid` char(36) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `amount_ht` decimal(10,2) NOT NULL,
  `shipping_amount` decimal(10,2) NOT NULL,
  `shipping_amount_ht` decimal(10,2) NOT NULL,
  `commission` decimal(10,2) NOT NULL,
  `commission_ht` decimal(10,2) NOT NULL,
  `shipping_address` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `estimated_shipping_date` date DEFAULT NULL,
  `estimated_delivery_date` date DEFAULT NULL,
  `shipping_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`) USING BTREE,
  KEY `id_purchase` (`id_purchase`) USING BTREE,
  KEY `id_merchant` (`id_merchant`) USING BTREE,
  KEY `id_status` (`id_status`) USING BTREE,
  CONSTRAINT `order_ibfk_1` FOREIGN KEY (`id_purchase`) REFERENCES `purchase` (`id`),
  CONSTRAINT `order_ibfk_2` FOREIGN KEY (`id_merchant`) REFERENCES `merchant` (`id`),
  CONSTRAINT `order_ibfk_3` FOREIGN KEY (`id_status`) REFERENCES `status` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order`
--

LOCK TABLES `order` WRITE;
/*!40000 ALTER TABLE `order` DISABLE KEYS */;
/*!40000 ALTER TABLE `order` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_item`
--

DROP TABLE IF EXISTS `order_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_order` int(11) NOT NULL,
  `id_status` int(11) NOT NULL,
  `uuid` char(36) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `relay_external_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  `attributes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `amount_ht` decimal(10,2) NOT NULL,
  `shipping_amount` decimal(10,2) NOT NULL,
  `shipping_amount_ht` decimal(10,2) NOT NULL,
  `commission` decimal(10,2) NOT NULL,
  `commission_ht` decimal(10,2) NOT NULL,
  `estimated_shipping_date` date DEFAULT NULL,
  `estimated_delivery_date` date DEFAULT NULL,
  `shipping_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  KEY `order_id` (`id_order`),
  KEY `id_mmm_status` (`id_status`) USING BTREE,
  CONSTRAINT `order_item_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id`),
  CONSTRAINT `order_item_ibfk_2` FOREIGN KEY (`id_status`) REFERENCES `status` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_item`
--

LOCK TABLES `order_item` WRITE;
/*!40000 ALTER TABLE `order_item` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_unit`
--

DROP TABLE IF EXISTS `order_unit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_unit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_order_item` int(11) NOT NULL,
  `id_status` int(11) NOT NULL,
  `uuid` char(36) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `relay_external_id` int(11) NOT NULL,
  `amount` double(10,2) NOT NULL,
  `amount_ht` double(10,2) NOT NULL,
  `shipping_amount` double(10,2) NOT NULL,
  `commission` double(10,2) NOT NULL,
  `estimated_shipping_date` date DEFAULT NULL,
  `estimated_delivery_date` date DEFAULT NULL,
  `shipping_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_status` (`id_status`) USING BTREE,
  KEY `id_order_item` (`id_order_item`) USING BTREE,
  KEY `uuid` (`uuid`),
  KEY `relay_external_id` (`relay_external_id`),
  CONSTRAINT `order_unit_ibfk_1` FOREIGN KEY (`id_order_item`) REFERENCES `order_item` (`id`),
  CONSTRAINT `order_unit_ibfk_2` FOREIGN KEY (`id_status`) REFERENCES `status` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_unit`
--

LOCK TABLES `order_unit` WRITE;
/*!40000 ALTER TABLE `order_unit` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_unit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase`
--

DROP TABLE IF EXISTS `purchase`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_site` int(11) NOT NULL,
  `uuid` char(36) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `cart_external_id` int(11) NOT NULL,
  `customer_external_id` int(11) NOT NULL,
  `customer_firstname` int(11) NOT NULL,
  `payment_method_id` int(11) NOT NULL COMMENT 'hum, erreur ?',
  `customer_lastname` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `amount_ht` decimal(10,2) NOT NULL,
  `commission` decimal(10,2) NOT NULL,
  `payment_method` varchar(255) NOT NULL,
  `billing_address` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'champs en json',
  `creation_date` timestamp NULL DEFAULT NULL,
  `scoring_date` timestamp NULL DEFAULT NULL,
  `estimated_shipping_date` date DEFAULT NULL,
  `estimated_delivery_date` date DEFAULT NULL,
  `shipping_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`) USING BTREE,
  KEY `id_site` (`id_site`),
  KEY `customer_external_id` (`customer_external_id`),
  KEY `payment_method_id` (`payment_method_id`),
  KEY `cart_external_id` (`cart_external_id`),
  CONSTRAINT `purchase_ibfk_1` FOREIGN KEY (`id_site`) REFERENCES `site` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase`
--

LOCK TABLES `purchase` WRITE;
/*!40000 ALTER TABLE `purchase` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site`
--

DROP TABLE IF EXISTS `site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `label` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site`
--

LOCK TABLES `site` WRITE;
/*!40000 ALTER TABLE `site` DISABLE KEYS */;
/*!40000 ALTER TABLE `site` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `status`
--

DROP TABLE IF EXISTS `status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `code` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `label` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`) USING BTREE,
  UNIQUE KEY `code` (`code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `status`
--

LOCK TABLES `status` WRITE;
/*!40000 ALTER TABLE `status` DISABLE KEYS */;
/*!40000 ALTER TABLE `status` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-09-04 19:13:16
