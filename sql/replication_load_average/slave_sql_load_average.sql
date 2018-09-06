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

DROP TABLE IF EXISTS slave_sql_load_average;
 
CREATE TABLE slave_sql_load_average (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tstamp TIMESTAMP,
  idle_avg VARCHAR(12),
  idle_delta_formatted VARCHAR(12),
  busy_pct DECIMAL(5,2),
  one_min_avg DECIMAL(5,2),
  five_min_avg DECIMAL(5,2),
  fifteen_min_avg DECIMAL(5,2),
  idle_sum BIGINT,
  idle_delta BIGINT,
  events_sum INT,
  events_delta INT,
  current_wait VARCHAR(128),
  KEY (tstamp)
) ENGINE = InnoDB;

set sql_log_bin = 1;