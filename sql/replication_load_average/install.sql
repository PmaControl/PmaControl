/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Author:  aurelien
 * Created: Aug 14, 2018
 */


--tables
SOURCE ./ps_helper_logs.sql
SOURCE ./slave_sql_load_average.sql

--procedure
SOURCE ./compute_slave_load_average.sql

--event
SOURCE ./monitor_slave_load_average.sql