-- Audit module (#1235) — audit_client_metrics
--
-- Browser-side metrics captured asynchronously from clientMetrics.js
-- (sendBeacon → AuditLog::recordClientMetrics → separate NDJSON spool
-- under tmp/audit/client_metrics/). One row per page-load, linked to
-- request_log via request_uid. Designed for upsert: LCP/CLS/INP arrive
-- after the initial POST, so the drain UPDATEs the row in place.
--
-- Privacy: GPU/RAM/CPU stored as normalised families/buckets only
-- (gpu_family, ram_bucket, cpu_bucket), never as raw fingerprintable
-- strings. The drain runs the normalisation pass; raw UNMASKED_RENDERER
-- never lands in the DB.

CREATE TABLE audit_client_metrics (
    id                   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    request_uid          CHAR(32)        NOT NULL,
    date                 DATETIME(3)     NOT NULL,
    id_user_main         INT UNSIGNED    NULL,

    -- Route context (mirrored from request_log for filterable queries)
    route_controller     VARCHAR(64)     NULL,
    route_action         VARCHAR(64)     NULL,
    user_role_class      VARCHAR(32)     NULL,

    -- Page lifecycle event that emitted this row
    page_lifecycle       ENUM('load','visibilitychange','pagehide','beforeunload') NULL,
    timezone_offset      SMALLINT        NULL,

    -- Screen / viewport
    screen_width         SMALLINT UNSIGNED NULL,
    screen_height        SMALLINT UNSIGNED NULL,
    screen_avail_width   SMALLINT UNSIGNED NULL,
    screen_avail_height  SMALLINT UNSIGNED NULL,
    color_depth          TINYINT  UNSIGNED NULL,
    pixel_depth          TINYINT  UNSIGNED NULL,
    device_pixel_ratio   DECIMAL(3,2)      NULL,
    viewport_width       SMALLINT UNSIGNED NULL,
    viewport_height      SMALLINT UNSIGNED NULL,
    document_width       MEDIUMINT UNSIGNED NULL,
    document_height      MEDIUMINT UNSIGNED NULL,
    orientation_type     VARCHAR(24)       NULL,
    orientation_angle    SMALLINT          NULL,

    -- Hardware: normalised, never raw (anti-fingerprint)
    gpu_family           VARCHAR(32)       NULL,
    ram_bucket           VARCHAR(8)        NULL,
    cpu_bucket           VARCHAR(8)        NULL,
    max_touch_points     TINYINT UNSIGNED  NULL,

    -- JS memory (Chrome only)
    js_heap_used_mb      MEDIUMINT UNSIGNED NULL,
    js_heap_total_mb     MEDIUMINT UNSIGNED NULL,
    js_heap_limit_mb     MEDIUMINT UNSIGNED NULL,

    -- Navigation Timing v2
    nav_dns_ms           SMALLINT UNSIGNED NULL,
    nav_tcp_ms           SMALLINT UNSIGNED NULL,
    nav_ttfb_ms          SMALLINT UNSIGNED NULL,
    nav_download_ms      SMALLINT UNSIGNED NULL,
    nav_dom_ready_ms     INT UNSIGNED      NULL,
    nav_load_ms          INT UNSIGNED      NULL,
    nav_first_paint_ms   INT UNSIGNED      NULL,
    nav_fcp_ms           INT UNSIGNED      NULL,

    -- Core Web Vitals (arrive async via the web-vitals lib, server upserts)
    cwv_lcp_ms           INT UNSIGNED      NULL,
    cwv_cls              DECIMAL(5,3)      NULL,
    cwv_inp_ms           SMALLINT UNSIGNED NULL,
    cwv_lcp_rating       ENUM('good','needs-improvement','poor') NULL,
    cwv_cls_rating       ENUM('good','needs-improvement','poor') NULL,
    cwv_inp_rating       ENUM('good','needs-improvement','poor') NULL,

    -- Network Information API
    net_effective_type   VARCHAR(8)        NULL,
    net_rtt_ms           SMALLINT UNSIGNED NULL,
    net_downlink_mbps    DECIMAL(6,2)      NULL,
    net_save_data        TINYINT(1)        NULL,

    -- Battery
    bat_level_pct        TINYINT UNSIGNED  NULL,
    bat_charging         TINYINT(1)        NULL,
    bat_charging_time_s  INT UNSIGNED      NULL,
    bat_discharging_time_s INT UNSIGNED    NULL,

    -- UI prefs
    prefers_dark         TINYINT(1)        NULL,
    prefers_reduced_motion TINYINT(1)      NULL,

    -- Navigator
    nav_lang             VARCHAR(8)        NULL,
    nav_languages        VARCHAR(255)      NULL,
    nav_platform         VARCHAR(64)       NULL,
    nav_vendor           VARCHAR(64)       NULL,
    nav_cookie_enabled   TINYINT(1)        NULL,
    nav_online           TINYINT(1)        NULL,
    visibility_state     ENUM('visible','hidden') NULL,
    ua_client_hints      JSON              NULL,

    -- Capture meta
    schema_version       TINYINT UNSIGNED  NOT NULL DEFAULT 1,
    collector_version    VARCHAR(16)       NOT NULL DEFAULT '1.0',
    capture_ts_ms        BIGINT UNSIGNED   NULL,
    payload_size_bytes   MEDIUMINT UNSIGNED NULL,
    degraded_capture     TINYINT(1)        NOT NULL DEFAULT 0,
    missing_fields_bm    BIGINT UNSIGNED   NOT NULL DEFAULT 0,
    collector_errors     VARCHAR(255)      NULL,

    PRIMARY KEY (id),
    UNIQUE KEY uq_request_uid (request_uid),
    KEY idx_date (date),
    KEY idx_user_date (id_user_main, date),
    KEY idx_route (route_controller, route_action),
    KEY idx_gpu (gpu_family)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
