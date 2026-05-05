ALTER TABLE `plugin_main`
  ADD COLUMN IF NOT EXISTS `sha256_zip` char(64) NOT NULL DEFAULT '' AFTER `md5_zip`,
  ADD COLUMN IF NOT EXISTS `signature_zip` text DEFAULT NULL AFTER `sha256_zip`;
