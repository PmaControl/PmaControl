#!/bin/bash
#
#autoreload_watch_php_error.sh
./watch_log.php &
./watch_php_error_debug.php &
./watch_php_error.php &
./watch_sql.php &
./watch-tests.sh &
