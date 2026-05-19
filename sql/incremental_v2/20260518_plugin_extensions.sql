-- Per-plugin registry of UI / data extension points.
-- Populated by PluginPackage when a plugin manifest declares "extensions"
-- (kind = "partial" for view fragments copied at install time, or "callback"
-- for static PHP class methods invoked at runtime). Uninstall removes every
-- row owned by the plugin so the core UI reverts to its baseline.
CREATE TABLE IF NOT EXISTS `plugin_extension` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_id` int(11) NOT NULL,
  `slot` varchar(64) NOT NULL,
  `kind` enum('partial','callback') NOT NULL,
  `payload` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 100,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_plugin_extension_plugin` (`plugin_id`),
  KEY `idx_plugin_extension_slot` (`slot`, `sort_order`),
  CONSTRAINT `fk_plugin_extension_plugin`
    FOREIGN KEY (`plugin_id`) REFERENCES `plugin_main` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
