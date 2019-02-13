#!/bin/sh
/usr/bin/php7.2 /data/www/pmacontrol/application/webroot/index.php Database databaseRefresh 84 1 'drupal_home' '/mysql/backup' >> /data/www/pmacontrol/tmp/log/Database-refresh-5c645ad816ef3.log
/usr/bin/php7.2 /data/www/pmacontrol/application/webroot/index.php job callback b53d8187-427d-4bde-83dc-1e8be61699fc
