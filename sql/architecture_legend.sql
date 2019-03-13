-- MySQL dump 10.17  Distrib 10.3.12-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: pma_new
-- ------------------------------------------------------
-- Server version	10.3.12-MariaDB-1:10.3.12+maria~bionic-log

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
-- Table structure for table `architecture_legend`
--

DROP TABLE IF EXISTS `architecture_legend`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `architecture_legend` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `const` varchar(30) NOT NULL,
  `name` varchar(50) NOT NULL,
  `color` varchar(20) NOT NULL,
  `style` varchar(20) NOT NULL,
  `order` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `architecture_legend`
--

LOCK TABLES `architecture_legend` WRITE;
/*!40000 ALTER TABLE `architecture_legend` DISABLE KEYS */;
INSERT INTO `architecture_legend` VALUES (1,'REPLICATION_OK','Healty','#008000','filled',1,'REPLICATION'),(2,'REPLICATION_IST','Galera IST','turquoise4','filled',20,'REPLICATION'),(3,'REPLICATION_SST','Galera SST','#e3ea12','dashed',12,'REPLICATION'),(4,'REPLICATION_STOPPED','Stopped','#0000FF','filled',3,'REPLICATION'),(5,'REPLICATION_ERROR_SQL','Error SQL','#FF0000','filled',3,'REPLICATION'),(7,'REPLICATION_DELAY','Delay','#FFA500','filled',2,'REPLICATION'),(10,'REPLICATION_ERROR_IO','Error IO','#FF0000','dashed',3,'REPLICATION'),(11,'REPLICATION_ERROR_CONNECT','Error connecting','#696969','dashed',3,'REPLICATION'),(13,'NODE_OK','Healty','#008000','filled',1,'NODE'),(14,'NODE_ERROR','Out of order','#FF0000','filled',2,'NODE'),(15,'NODE_BUSY','Going down','#FFA500','filled',3,'NODE'),(16,'NODE_NOT_PRIMARY','Node probably desynced','Orange','filled',10,'NODE'),(17,'NODE_DONOR','donnor','#00FF00','filled',11,'NODE'),(18,'NODE_DONOR_DESYNCED','Node donor desynced','#e3ea12','filled',11,'NODE'),(19,'NODE_MANUAL_DESYNC','node desync manually','#0000ff','filled',12,'NODE'),(20,'NODE_JOINER','node joining cluster','#000000','dashed',15,'NODE'),(21,'GALERA_AVAILABLE','galera all ok','#008000','filled',1,'GALERA'),(22,'GALERA_DEGRADED','','#e3ea12','filled',2,'GALERA'),(23,'GALERA_WARNING','N*2  node should be N*2+1','orange','filled',3,'GALERA'),(24,'GALERA_CRITICAL','only 2 node','#FF0000','filled',4,'GALERA'),(25,'GALERA_EMERGENCY','only one node in galera','#FF0000','dashed',5,'GALERA'),(26,'GALERA_OUTOFORDER','galera HS','#000000','filled',6,'GALERA'),(27,'REPLICATION_BLACKOUT','Out of order','#000000','filled',15,'REPLICATION'),(28,'SEGMENT_OK','segment ok','#008000','dashed',1,'SEGMENT'),(29,'SEGMENT_KO','segment out of order','#FF0000','dashed',2,'SEGMENT'),(32,'SEGMENT_PARTIAL','un neud est hs','#FFA500','dashed',3,'SEGMENT');
/*!40000 ALTER TABLE `architecture_legend` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-02-07 10:50:19
