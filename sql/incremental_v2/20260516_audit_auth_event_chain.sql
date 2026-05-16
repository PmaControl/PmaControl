-- Audit module (#1235) post-MVP #7 — tamper-evident hash chain on auth_event.
-- Each row stores `self_hash` = sha256(<canonical_event_fields> || prev_hash).
-- A verifier walks the chain in order; any altered or removed row breaks the
-- hash continuity and `/AuditLog/verify` flags the first inconsistency.

ALTER TABLE auth_event
    ADD COLUMN prev_hash CHAR(64) NULL AFTER detail,
    ADD COLUMN self_hash CHAR(64) NULL AFTER prev_hash,
    ADD INDEX idx_prev_hash (prev_hash);
