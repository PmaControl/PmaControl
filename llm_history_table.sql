-- Table to store LLM index analysis histories
CREATE TABLE `llm_index_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `id_mysql_database` int(11) DEFAULT NULL,
  `id_query` int(11) DEFAULT NULL COMMENT 'Reference to query table if exists',
  `table_name` varchar(255) NOT NULL,
  `proposed_index` varchar(500) NOT NULL COMMENT 'Index columns as comma-separated string',
  `columns` json NOT NULL COMMENT 'Index columns as JSON array',
  `date_created` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_server_database` (`id_mysql_server`, `id_mysql_database`),
  KEY `idx_date` (`date_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Stores historical LLM index suggestions';

--197729

 -- TEST

 /*





SHOW CREATE TABLE: CREATE TABLE orders ( id BIGINT PRIMARY KEY, customer_id BIGINT, created_at DATETIME, status VARCHAR(20) ); 

Existing indexes: PRIMARY KEY (id) EXPLAIN: EXPLAIN SELECT * FROM orders WHERE customer_id = 42 AND status = 'PAID'; 
-> type: ALL -> rows: 1200000 TXT;






 */

