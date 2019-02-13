#!/bin/sh
/usr/bin/php7.2 /data/www/pmacontrol/application/webroot/index.php Database databaseRefresh 82 1 'drupal_home' '/mysql/backup' >> /data/www/pmacontrol/tmp/log/Database-refresh-5c645a978777e.log
/usr/bin/php7.2 /data/www/pmacontrol/application/webroot/index.php job callback ce85ddb4-0ed9-4c1d-82c5-ab2ee031e892
