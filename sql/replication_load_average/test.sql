/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Author:  aurelien
 * Created: Aug 14, 2018
 */


select * from performance_schema.events_waits_summary_global_by_event_name 
WHERE EVENT_NAME = 'wait/synch/cond/sql/MYSQL_RELAY_LOG::update_cond';



select tstamp, busy_pct, one_min_avg, five_min_avg, fifteen_min_avg from slave_sql_load_average order by tstamp desc limit 10;






SELECT VARIABLE_VALUE FROM INFORMATION_SCHEMA.GLOBAL_VARIABLES WHERE VARIABLE_NAME = 'performance_schema';
-- ON

SELECT enabled, timed FROM performance_schema.setup_instruments
WHERE name = 'wait/synch/cond/sql/MYSQL_RELAY_LOG::update_cond';


--Activate 
UPDATE performance_schema.setup_instruments SET enabled='YES', timed='YES' WHERE name = 'wait/synch/cond/sql/MYSQL_RELAY_LOG::update_cond';


--- current wait 

wait/synch/mutex/mysys/IO_CACHE::append_buffer_lock

MariaDB [sys]> CALL compute_slave_load_average();
ERROR 1365 (22012): Division by 0