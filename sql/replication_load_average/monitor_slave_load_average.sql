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

DROP EVENT IF EXISTS monitor_slave_load_average;
 
DELIMITER $$
 
CREATE EVENT IF NOT EXISTS monitor_slave_load_average 
ON SCHEDULE EVERY 5 SECOND DO
BEGIN
    CALL compute_slave_load_average();
END$$
 
DELIMITER ;


set sql_log_bin = 1;