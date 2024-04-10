INSERT IGNORE INTO `daemon_main` VALUES
(2,'scan ip','2016-06-10 16:35:31',0,64,'log/scanip.log',60,2000,1,'','','',0,0,'',0,'','','',''),
(3,'scan port','2016-08-22 00:00:00',0,64,'log/scanport.log',1,60,1,'','','',0,0,'',0,'','','',''),
(5,'Generate architecture graph','2016-11-08 00:00:00',10046,64,'log/daemon_5.log',5,1,10,'Dot2','run','',1,0,'',0,'','','',''),
(7,'integrate data','2017-12-05 12:27:30',10053,64,'log/daemon_7.log',0,1,1,'integrate','integrateAll','',0,0,'',0,'','','',''),
(9,'aspirateur ssh (mode queue)','2017-11-23 18:15:54',10065,64,'log/daemon_9.log',5,10,3,'Aspirateur','addToQueueSsh','',0,21457,'',21457,'trySshConnection','','workerSsh','worker_ssh'),
(11,'aspirateur mysql (mode queue)','2018-11-27 18:15:54',10075,64,'log/daemon_11.log',5,5,2,'Aspirateur','addToQueueMySQL','',0,21671,'',21671,'tryMysqlConnection','','worker','worker'),
(12,'check all queue','2018-11-27 18:15:54',10089,64,'log/daemon_12.log',8,1,2,'Aspirateur','checkAllWorker','',0,0,'',0,'','','',''),
(13,'aspirateur proxysql (mode queue)','2022-12-28 18:02:33',10103,1,'log/daemon_13.log',9,2,10,'Aspirateur','addToQueueProxySQL','',0,21672,'',21672,'tryProxySQLConnection','','workerProxysql','worker_proxysql'),
(14,'Listener','2024-04-02 12:37:07',10117,64,'log/daemon_14.log',1,1,1,'Listener','checkAll','',0,0,'',0,'','','','');


INSERT IGNORE INTO `ts_file` VALUES
(3,'answer'),
(5,'database'),
(2,'hardware'),
(9,'list_db'),
(7,'proxysql'),
(6,'service_mysql'),
(8,'service_ssh'),
(1,'ssh_stats'),
(4,'variable');


