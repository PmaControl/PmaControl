#!/bin/sh
/usr/bin/php /data/www/pmacontrol/application/webroot/index.php Database databaseRefresh 2 1 'teleinfo' '/data/www/pmacontrol/tmp' >> /data/www/pmacontrol/tmp/log/Database-refresh-5c67f41513263.log
/usr/bin/php /data/www/pmacontrol/application/webroot/index.php job callback 0817a345-1f16-4342-8e5b-c62a7dbc4d0f
