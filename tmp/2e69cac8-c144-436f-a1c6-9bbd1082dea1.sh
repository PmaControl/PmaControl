#!/bin/sh
/usr/bin/php7.2 /data/www/pmacontrol/application/webroot/index.php Database databaseRefresh 28 1 'drupal_home' '/data/backup' >> /data/www/pmacontrol/tmp/log/database-refresh-5d00c253b8888.log
/usr/bin/php7.2 /data/www/pmacontrol/application/webroot/index.php job callback 2e69cac8-c144-436f-a1c6-9bbd1082dea1
