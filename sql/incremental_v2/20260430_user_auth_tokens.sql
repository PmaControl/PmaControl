ALTER TABLE `user_main`
  ADD COLUMN IF NOT EXISTS `confirm_token_hash` char(64) DEFAULT NULL AFTER `key_auth`,
  ADD COLUMN IF NOT EXISTS `confirm_token_expires_at` datetime DEFAULT NULL AFTER `confirm_token_hash`,
  ADD COLUMN IF NOT EXISTS `reset_token_hash` char(64) DEFAULT NULL AFTER `confirm_token_expires_at`,
  ADD COLUMN IF NOT EXISTS `reset_token_expires_at` datetime DEFAULT NULL AFTER `reset_token_hash`;

ALTER TABLE `user_main`
  ADD INDEX IF NOT EXISTS `idx_user_main_confirm_token_expires_at` (`confirm_token_expires_at`),
  ADD INDEX IF NOT EXISTS `idx_user_main_reset_token_expires_at` (`reset_token_expires_at`);
