-- Audit module (#1235) post-MVP #4 — GeoIP enrichment columns on request_log.
-- Resolved in the drain via the existing `data_geoip` / `data_geoip_city`
-- range tables (cf docs/data_geoip.md), so private/loopback IPs stay NULL.

ALTER TABLE request_log
    ADD COLUMN geo_country_iso CHAR(2)     NULL AFTER ip,
    ADD COLUMN geo_city        VARCHAR(64) NULL AFTER geo_country_iso,
    ADD INDEX idx_geo_country (geo_country_iso, date);
