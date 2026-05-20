-- Epic #799 / lot 1 : foundation tables for native MySQL NDB Cluster support.
--
-- The shape mirrors maxscale_server / mysqlrouter_server (existing satellite
-- service tables in PmaControl) but adds a dedicated `ndb_node` table because
-- a single NDB cluster contains 3 distinct member roles (ndb_mgmd, ndbmtd,
-- mysqld with NDB engine) and we want to query them by role / node group
-- without parsing JSON.

CREATE TABLE IF NOT EXISTS `ndb_cluster` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `display_name`    VARCHAR(100) NOT NULL,
  `ndb_version`     VARCHAR(40)  DEFAULT NULL,
  `no_of_replicas`  TINYINT      DEFAULT NULL,
  `is_deleted`      TINYINT(1)   NOT NULL DEFAULT 0,
  `date_inserted`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_updated`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `u_ndb_cluster_name` (`display_name`)
) ENGINE=InnoDB ROW_FORMAT=COMPRESSED DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `ndb_node` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_ndb_cluster`  INT UNSIGNED NOT NULL,
  `ndb_node_id`     SMALLINT UNSIGNED NOT NULL,
  `role`            ENUM('mgmd','data','sql') NOT NULL,
  `hostname`        VARCHAR(100) NOT NULL DEFAULT '',
  `ip`              VARCHAR(45)  NOT NULL DEFAULT '',
  `port`            SMALLINT UNSIGNED DEFAULT NULL,
  `node_group`      SMALLINT     DEFAULT NULL,
  `is_primary`      TINYINT(1)   NOT NULL DEFAULT 0,
  `status`          VARCHAR(40)  NOT NULL DEFAULT 'UNKNOWN',
  `ndb_version`     VARCHAR(40)  DEFAULT NULL,
  `uptime_sec`      INT UNSIGNED DEFAULT NULL,
  `date_seen`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `u_ndb_node_clusterid_nodeid` (`id_ndb_cluster`, `ndb_node_id`),
  KEY `idx_ndb_node_role_status` (`id_ndb_cluster`, `role`, `status`),
  CONSTRAINT `fk_ndb_node_cluster` FOREIGN KEY (`id_ndb_cluster`) REFERENCES `ndb_cluster` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB ROW_FORMAT=COMPRESSED DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `ndb_cluster__mysql_server` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_ndb_cluster`   INT UNSIGNED NOT NULL,
  `id_mysql_server`  INT NOT NULL,
  `date`             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `u_ndb_cluster_mysql_server` (`id_ndb_cluster`, `id_mysql_server`),
  KEY `idx_ndb_cluster_mysql_server_msid` (`id_mysql_server`),
  CONSTRAINT `fk_ndb_cluster_mysql_server` FOREIGN KEY (`id_ndb_cluster`) REFERENCES `ndb_cluster` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Flag a mysql_server as the SQL/API endpoint of an NDB cluster. Same shape
-- as the existing `is_proxy` / `is_proxysql` / `is_maxscale` flags, so Dot3
-- and Aspirateur can branch on it without re-deriving from version_comment.
ALTER TABLE `mysql_server`
  ADD COLUMN IF NOT EXISTS `is_ndb_cluster_node` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_proxy`;
