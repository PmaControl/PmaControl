-- Issue #773: add a short-lived "previous token" window so that requests
-- already in flight with the previous verifier do not race-revoke the
-- session when a parallel request rotates the cookie.
--
-- Without these columns, every parallel request that loses the
-- compare-and-swap UPDATE on `token_hash` falls into the revoke branch
-- and kills the session for all tabs.

ALTER TABLE `user_persistent_auth_session`
  ADD COLUMN `previous_token_hash` CHAR(64) DEFAULT NULL AFTER `token_hash`,
  ADD COLUMN `previous_token_expires` DATETIME DEFAULT NULL AFTER `previous_token_hash`;
