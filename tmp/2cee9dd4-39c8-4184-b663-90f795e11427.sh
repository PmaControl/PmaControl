#!/bin/sh
/usr/bin/php7.2 /data/www/pmacontrol/application/webroot/index.php Database databaseRefresh 84 1 'drupal_home' '/mysql/backup' >> /data/www/pmacontrol/tmp/log/Database-refresh-5c645b6868064.log
/usr/bin/php7.2 /data/www/pmacontrol/application/webroot/index.php job callback 2cee9dd4-39c8-4184-b663-90f795e11427
