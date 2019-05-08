/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Author:  aurelien
 * Created: Aug 14, 2018
 */

set sql_log_bin = 0;

DROP PROCEDURE IF EXISTS compute_slave_load_average;
 
DELIMITER $$
 
CREATE PROCEDURE compute_slave_load_average()
BEGIN
    DECLARE v_ps_enabled VARCHAR(3);
    DECLARE v_update_cond_enabled VARCHAR(3);
    DECLARE v_update_cond_timed VARCHAR(3);
 
    DECLARE v_wait_count BIGINT DEFAULT 0;
    DECLARE v_last_wait_count BIGINT DEFAULT 0;
    DECLARE v_wait_count_delta BIGINT DEFAULT 0;
 
    DECLARE v_wait_sum BIGINT DEFAULT 0;
    DECLARE v_last_wait_sum BIGINT DEFAULT 0;
    DECLARE v_wait_delta BIGINT DEFAULT 0;
 
    DECLARE v_last_tstamp DATETIME;
    DECLARE v_wait_sum_tstamp DATETIME;
    DECLARE v_time_diff BIGINT;
 
    DECLARE v_current_wait VARCHAR(128);
    DECLARE v_current_timer_end BIGINT;
 
    DECLARE v_busy_pct DECIMAL(5,2);
    DECLARE v_one_min_avg DECIMAL(5,2);
    DECLARE v_five_min_avg DECIMAL(5,2);
    DECLARE v_fifteen_min_avg DECIMAL(5,2);
 
    DECLARE v_insert_id BIGINT;
     
    /* Disable binary logging */
    SET sql_log_bin = 0;
 
    /* Check Performance Schema is enabled properly */
    SELECT VARIABLE_VALUE INTO v_ps_enabled
      FROM INFORMATION_SCHEMA.GLOBAL_VARIABLES 
     WHERE VARIABLE_NAME = 'performance_schema';
 
    SELECT enabled, timed
      INTO v_update_cond_enabled, v_update_cond_timed
      FROM performance_schema.setup_instruments
     WHERE name = 'wait/synch/cond/sql/MYSQL_RELAY_LOG::update_cond';
 
    IF v_ps_enabled = 'OFF' THEN
        INSERT INTO ps_helper_logs (module, message)
        VALUES ('compute_slave_load_average', 'performance_schema is disabled');
    ELSEIF v_update_cond_enabled = 'NO' OR v_update_cond_timed = 'NO' THEN
        INSERT INTO ps_helper_logs (module, message)
        VALUES ('compute_slave_load_average', 
                CONCAT('performance_schema is not configured properly, 
                        the wait/synch/cond/sql/MYSQL_RELAY_LOG::update_cond event is currently
                        set to enabled: ', v_update_cond_enabled, ', timed: ', v_update_cond_timed, 
                        'within the setup_instruments table'));
    ELSE
        /* Get the latest MYSQL_RELAY_LOG::update_cond wait info for the slave SQL thread */
        SELECT his.sum_timer_wait, his.count_star, cur.event_name, cur.timer_end, SYSDATE() 
          INTO v_wait_sum, v_wait_count, v_current_wait, v_current_timer_end, v_wait_sum_tstamp 
          FROM performance_schema.events_waits_summary_by_thread_by_event_name his
          JOIN performance_schema.threads thr USING (thread_id)
          JOIN performance_schema.events_waits_current cur USING (thread_id)
         WHERE his.event_name = 'wait/synch/cond/sql/MYSQL_RELAY_LOG::update_cond'
           AND name = 'thread/sql/slave_sql';
 
        /* Get the last summary for comparison */
        SELECT idle_sum, events_sum, tstamp 
          INTO v_last_wait_sum, v_last_wait_count, v_last_tstamp
          FROM slave_sql_load_average
         ORDER BY id DESC LIMIT 1;
 
        /* Compute the interval and event count deltas */
        SET v_time_diff = (UNIX_TIMESTAMP(v_wait_sum_tstamp) - UNIX_TIMESTAMP(v_last_tstamp)) * 1000000000000;
        SET v_wait_count_delta = v_wait_count - v_last_wait_count;
 
        /* Compute the delta busy percentages */
        IF (v_wait_sum != v_last_wait_sum AND v_wait_count_delta > 0) THEN
            /* There have been waits during the period, calculate the stats */
            SET v_wait_delta = v_wait_sum - v_last_wait_sum;
 
            IF (v_wait_delta > v_time_diff) THEN
                /* The last wait was longer than our current period, estimate waits in period */
                SET v_wait_delta = v_wait_delta % (v_time_diff * FLOOR(v_wait_delta/v_time_diff));
                SET v_busy_pct = 100 - ((v_wait_delta / v_time_diff) * 100);
            ELSE
                /* In a normal period, calculate using raw wait delta */
                SET v_busy_pct = 100 - ((v_wait_delta / v_time_diff) * 100);
            END IF;
        ELSEIF (v_current_wait = 'wait/synch/cond/sql/MYSQL_RELAY_LOG::update_cond'
                AND v_current_timer_end IS NULL) THEN
            /* Waiting 100% on a single event for the entire period, i.e 100% idle*/
            SET v_wait_delta = v_time_diff;
            SET v_busy_pct = 0.00;
        ELSE
            /* Waiting 100% on a single event for the entire period that is not update_cond */
            SET v_wait_delta = v_time_diff;
            SET v_busy_pct = 100.00;
        END IF;
 
        /* Log the initial stats */
        INSERT INTO slave_sql_load_average 
               (idle_sum, idle_delta, idle_avg, idle_delta_formatted, 
                events_sum, events_delta, busy_pct, tstamp, current_wait)
        VALUES (v_wait_sum, v_wait_delta, format_time(v_wait_delta / v_wait_count_delta),
                format_time(v_wait_delta), v_wait_count, v_wait_count_delta, 
                v_busy_pct, v_wait_sum_tstamp, v_current_wait);
 
        SELECT LAST_INSERT_ID() INTO v_insert_id;
 
        /* Compute the averages taking the last interval in to account */
        SELECT SUM(busy_pct)/COUNT(*) INTO v_one_min_avg
          FROM slave_sql_load_average
         WHERE busy_pct IS NOT NULL
           AND tstamp > SYSDATE() - INTERVAL 1 MINUTE;
 
        SELECT SUM(busy_pct)/COUNT(*) INTO v_five_min_avg
          FROM slave_sql_load_average
         WHERE busy_pct IS NOT NULL
           AND tstamp > SYSDATE() - INTERVAL 5 MINUTE;
 
        SELECT SUM(busy_pct)/COUNT(*) INTO v_fifteen_min_avg
          FROM slave_sql_load_average
         WHERE busy_pct IS NOT NULL
           AND tstamp > SYSDATE() - INTERVAL 15 MINUTE;
 
        UPDATE slave_sql_load_average SET
               one_min_avg = v_one_min_avg,
               five_min_avg = v_five_min_avg,
               fifteen_min_avg = v_fifteen_min_avg
         WHERE id = v_insert_id;
 
        /* Purge anything older than 2 hours */            
        DELETE FROM slave_sql_load_average 
         WHERE tstamp < NOW() - INTERVAL 2 HOUR;
    END IF;
 
    /* Re-enable binary logging */
    SET sql_log_bin = 1;
END$$
 
DELIMITER ; 


set sql_log_bin = 1;