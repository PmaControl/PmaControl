<?php


/*
DEBUG      (100): Detailed debug information.
INFO       (200): Interesting events. Examples: User logs in, SQL logs.
NOTICE     (250): Normal but significant events.
WARNING    (300): Exceptional occurrences that are not errors. Examples: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.
ERROR      (400): Runtime errors that do not require immediate action but should typically be logged and monitored.
CRITICAL   (500): Critical conditions. Example: Application component unavailable, unexpected exception.
ALERT      (550): Action must be taken immediately. Example: Entire website down, database unavailable, etc. This should trigger the SMS alerts and wake you up.
EMERGENCY  (600): Emergency: system is unusable.

*/

if (!defined('LOG_FILE')) {
    define("LOG_FILE", TMP."log/glial.log");
}


if (!defined('LOG_LOGGER')) {
    define("LOG_LOGGER", 50);
}
