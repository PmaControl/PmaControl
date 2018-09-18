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


DROP TABLE IF EXISTS ps_helper_logs;

CREATE TABLE ps_helper_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tstamp TIMESTAMP,
  module VARCHAR(64),
  message TEXT
) ENGINE = InnoDB;


set sql_log_bin = 1;