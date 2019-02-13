#!/bin/sh
/usr/bin/php7.2 /data/www/pmacontrol/application/webroot/index.php Database databaseRefresh 84 1 'drupal_home' '/mysql/backup' >> /data/www/pmacontrol/tmp/log/Database-refresh-5c6456f9c2c4b.log
/usr/bin/php7.2 /data/www/pmacontrol/application/webroot/index.php job callback 5f5347f4-181d-43cf-a23f-7e6d55db48d5
