-- Display title for plugins on /plugin/index. Falls back to `nom` when
-- the column is empty so the column is a pure additive change with no
-- behaviour shift for plugins that don't populate it.
ALTER TABLE `plugin_main`
  ADD COLUMN IF NOT EXISTS `title` varchar(64) NOT NULL DEFAULT '' AFTER `nom`;
