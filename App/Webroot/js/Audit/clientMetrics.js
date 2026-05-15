/**
 * Audit module #1235 — client-side metrics collector.
 *
 * Reads navigator / performance / screen / network / battery / GPU
 * data after `window.load` and POSTs one compact JSON document to
 * `/AuditLog/recordClientMetrics/ajax:true/` via `sendBeacon` (with
 * `fetch keepalive` fallback). One submission per page-load, linked to
 * the request_uid the server emitted in `X-Pma-Audit-Id` / on the page
 * (window.PMA_AUDIT_UID).
 *
 * Privacy: the GPU UNMASKED_RENDERER raw string is sent under
 * `gpu_renderer_raw` and normalised server-side to a coarse family
 * (`apple-silicon` / `nvidia-discrete` / `intel-igpu` / …) before
 * persistence. The raw is never stored.
 */
(function () {
    'use strict';

    var COLLECTOR_VERSION = '1.0';

    function getRequestUid() {
        if (typeof window.PMA_AUDIT_UID === 'string' && /^[a-f0-9]{32}$/.test(window.PMA_AUDIT_UID)) {
            return window.PMA_AUDIT_UID;
        }
        var meta = document.querySelector('meta[name="pma-audit-uid"]');
        if (meta) {
            var v = meta.getAttribute('content') || '';
            if (/^[a-f0-9]{32}$/.test(v)) return v;
        }
        return null;
    }

    function endpoint() {
        var link = window.GLIAL_LINK || '/pmacontrol/en/';
        return link + 'AuditLog/recordClientMetrics/ajax:true/';
    }

    function safe(fn, fallback) {
        try { return fn(); } catch (e) { return fallback === undefined ? null : fallback; }
    }

    function getGpu() {
        return safe(function () {
            var canvas = document.createElement('canvas');
            var gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
            if (!gl) return null;
            var dbg = gl.getExtension('WEBGL_debug_renderer_info');
            if (!dbg) return null;
            return {
                renderer: gl.getParameter(dbg.UNMASKED_RENDERER_WEBGL),
                vendor: gl.getParameter(dbg.UNMASKED_VENDOR_WEBGL)
            };
        }, null);
    }

    function getNav() {
        var nav = navigator || {};
        return {
            lang: safe(function () { return nav.language; }),
            languages: safe(function () { return (nav.languages || []).slice(0, 8).join(','); }),
            platform: safe(function () { return nav.platform; }),
            vendor: safe(function () { return nav.vendor; }),
            cookie_enabled: safe(function () { return !!nav.cookieEnabled; }),
            online: safe(function () { return !!nav.onLine; }),
            hw_concurrency: safe(function () { return nav.hardwareConcurrency; }),
            device_memory: safe(function () { return nav.deviceMemory; }),
            max_touch_points: safe(function () { return nav.maxTouchPoints; })
        };
    }

    function getPerf() {
        var perf = performance || {};
        var nav = safe(function () {
            return (perf.getEntriesByType && perf.getEntriesByType('navigation')[0]) || null;
        }, null);
        var paints = safe(function () {
            return (perf.getEntriesByType && perf.getEntriesByType('paint')) || [];
        }, []);
        var firstPaint = (paints || []).find(function (e) { return e.name === 'first-paint'; });
        var fcp = (paints || []).find(function (e) { return e.name === 'first-contentful-paint'; });

        return {
            nav_dns_ms:        nav ? Math.round(nav.domainLookupEnd - nav.domainLookupStart) : null,
            nav_tcp_ms:        nav ? Math.round(nav.connectEnd - nav.connectStart) : null,
            nav_ttfb_ms:       nav ? Math.round(nav.responseStart - nav.requestStart) : null,
            nav_download_ms:   nav ? Math.round(nav.responseEnd - nav.responseStart) : null,
            nav_dom_ready_ms:  nav ? Math.round(nav.domContentLoadedEventEnd - nav.startTime) : null,
            nav_load_ms:       nav ? Math.round(nav.loadEventEnd - nav.startTime) : null,
            nav_first_paint_ms: firstPaint ? Math.round(firstPaint.startTime) : null,
            nav_fcp_ms:        fcp ? Math.round(fcp.startTime) : null
        };
    }

    function getJsHeap() {
        var p = performance || {};
        if (!p.memory) return null;
        return {
            js_heap_used_mb: Math.round(p.memory.usedJSHeapSize / 1048576),
            js_heap_total_mb: Math.round(p.memory.totalJSHeapSize / 1048576),
            js_heap_limit_mb: Math.round(p.memory.jsHeapSizeLimit / 1048576)
        };
    }

    function getNetwork() {
        var c = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        if (!c) return null;
        return {
            net_effective_type: c.effectiveType || null,
            net_rtt_ms: typeof c.rtt === 'number' ? c.rtt : null,
            net_downlink_mbps: typeof c.downlink === 'number' ? c.downlink : null,
            net_save_data: typeof c.saveData === 'boolean' ? c.saveData : null
        };
    }

    /**
     * Compose the full payload (sans battery, sans Core Web Vitals — both
     * resolved asynchronously and POSTed in follow-up emissions).
     */
    function buildBasePayload(uid, lifecycle) {
        var nav = getNav();
        var perf = getPerf();
        var heap = getJsHeap();
        var net = getNetwork();
        var gpu = getGpu();
        var orient = safe(function () { return screen.orientation ? { type: screen.orientation.type, angle: screen.orientation.angle } : null; });

        var missing = [];
        if (nav.hw_concurrency === null) missing.push('hwConcurrency');
        if (nav.device_memory === null)  missing.push('deviceMemory');
        if (net === null)                missing.push('connection');
        if (heap === null)               missing.push('jsHeap');
        if (gpu === null || !gpu.renderer) missing.push('gpu');

        return {
            request_uid: uid,
            page_lifecycle: lifecycle || 'load',
            collector_version: COLLECTOR_VERSION,
            schema_version: 1,
            capture_ts_ms: Date.now(),
            timezone_offset: -new Date().getTimezoneOffset(),

            screen_width: screen.width,
            screen_height: screen.height,
            screen_avail_width: screen.availWidth,
            screen_avail_height: screen.availHeight,
            color_depth: screen.colorDepth,
            pixel_depth: screen.pixelDepth,
            device_pixel_ratio: window.devicePixelRatio,
            viewport_width: window.innerWidth,
            viewport_height: window.innerHeight,
            document_width: document.documentElement.scrollWidth,
            document_height: document.documentElement.scrollHeight,
            orientation_type: orient ? orient.type : null,
            orientation_angle: orient ? orient.angle : null,

            hw_concurrency: nav.hw_concurrency,
            device_memory: nav.device_memory,
            max_touch_points: nav.max_touch_points,

            gpu_renderer_raw: gpu ? gpu.renderer : null,
            gpu_vendor_raw: gpu ? gpu.vendor : null,

            nav_dns_ms: perf.nav_dns_ms,
            nav_tcp_ms: perf.nav_tcp_ms,
            nav_ttfb_ms: perf.nav_ttfb_ms,
            nav_download_ms: perf.nav_download_ms,
            nav_dom_ready_ms: perf.nav_dom_ready_ms,
            nav_load_ms: perf.nav_load_ms,
            nav_first_paint_ms: perf.nav_first_paint_ms,
            nav_fcp_ms: perf.nav_fcp_ms,

            js_heap_used_mb: heap ? heap.js_heap_used_mb : null,
            js_heap_total_mb: heap ? heap.js_heap_total_mb : null,
            js_heap_limit_mb: heap ? heap.js_heap_limit_mb : null,

            net_effective_type: net ? net.net_effective_type : null,
            net_rtt_ms: net ? net.net_rtt_ms : null,
            net_downlink_mbps: net ? net.net_downlink_mbps : null,
            net_save_data: net ? net.net_save_data : null,

            prefers_dark: safe(function () { return window.matchMedia('(prefers-color-scheme: dark)').matches; }),
            prefers_reduced_motion: safe(function () { return window.matchMedia('(prefers-reduced-motion: reduce)').matches; }),

            nav_lang: nav.lang,
            nav_languages: nav.languages,
            nav_platform: nav.platform,
            nav_vendor: nav.vendor,
            nav_cookie_enabled: nav.cookie_enabled,
            nav_online: nav.online,

            visibility_state: document.visibilityState,
            degraded_capture: missing.length > 0 ? 1 : 0,
            collector_errors: missing.length > 0 ? missing.join(',') : null,
            missing_fields_bm: 0
        };
    }

    function send(payload) {
        var url = endpoint();
        var body = JSON.stringify(payload);
        payload.payload_size_bytes = body.length;
        body = JSON.stringify(payload);
        if (navigator.sendBeacon) {
            try {
                var blob = new Blob([body], { type: 'application/json' });
                navigator.sendBeacon(url, blob);
                return;
            } catch (e) { /* fall through */ }
        }
        if (window.fetch) {
            fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: body,
                keepalive: true
            }).catch(function () {});
        }
    }

    /**
     * Async battery fetch (returns a payload merge). Best-effort; many
     * browsers (Brave, Firefox, recent Safari) just don't expose this.
     */
    function fetchBattery() {
        if (!navigator.getBattery) return Promise.resolve(null);
        return navigator.getBattery().then(function (b) {
            return {
                bat_level_pct: Math.round(b.level * 100),
                bat_charging: !!b.charging,
                bat_charging_time_s: isFinite(b.chargingTime) ? b.chargingTime : null,
                bat_discharging_time_s: isFinite(b.dischargingTime) ? b.dischargingTime : null
            };
        }, function () { return null; });
    }

    /**
     * Top-level entry: collect + send on `load`, then send a follow-up
     * with battery once it resolves, then again with Core Web Vitals
     * later (LCP after interaction, CLS after pagehide, INP after typing).
     */
    function start() {
        var uid = getRequestUid();
        if (!uid) return; // No correlation possible — bail out silently.

        var base = buildBasePayload(uid, 'load');

        // Phase 1: ship immediately.
        send(base);

        // Phase 2: battery (async). Re-POST with the same request_uid;
        // server upserts.
        fetchBattery().then(function (bat) {
            if (!bat) return;
            send(Object.assign({ request_uid: uid, page_lifecycle: 'load' }, bat, { capture_ts_ms: Date.now() }));
        });

        // Phase 3: Core Web Vitals via PerformanceObserver (no extra lib,
        // works in Chromium/Edge/recent Firefox/Safari).
        observeCwv(uid);
    }

    function observeCwv(uid) {
        function rating(name, value) {
            if (name === 'LCP') return value <= 2500 ? 'good' : value <= 4000 ? 'needs-improvement' : 'poor';
            if (name === 'CLS') return value <= 0.1 ? 'good' : value <= 0.25 ? 'needs-improvement' : 'poor';
            if (name === 'INP') return value <= 200 ? 'good' : value <= 500 ? 'needs-improvement' : 'poor';
            return null;
        }
        function ship(field, value) {
            var p = { request_uid: uid, page_lifecycle: 'load', capture_ts_ms: Date.now() };
            p[field] = value;
            p[field.replace('_ms', '_rating').replace('cwv_cls', 'cwv_cls_rating')] = rating(field.indexOf('lcp') >= 0 ? 'LCP' : field.indexOf('cls') >= 0 ? 'CLS' : 'INP', value);
            send(p);
        }
        try {
            new PerformanceObserver(function (list) {
                var entries = list.getEntries();
                if (entries.length === 0) return;
                var last = entries[entries.length - 1];
                ship('cwv_lcp_ms', Math.round(last.startTime));
            }).observe({ type: 'largest-contentful-paint', buffered: true });
        } catch (e) {}
        try {
            var clsValue = 0;
            new PerformanceObserver(function (list) {
                list.getEntries().forEach(function (entry) {
                    if (!entry.hadRecentInput) clsValue += entry.value;
                });
            }).observe({ type: 'layout-shift', buffered: true });
            // CLS is reported on pagehide because it accumulates across the session.
            window.addEventListener('pagehide', function () {
                ship('cwv_cls', Math.round(clsValue * 1000) / 1000);
            }, { once: true });
        } catch (e) {}
        try {
            new PerformanceObserver(function (list) {
                list.getEntries().forEach(function (entry) {
                    if (entry.duration > 0) ship('cwv_inp_ms', Math.round(entry.duration));
                });
            }).observe({ type: 'event', durationThreshold: 40, buffered: true });
        } catch (e) {}
    }

    if (document.readyState === 'complete') {
        setTimeout(start, 0);
    } else {
        window.addEventListener('load', function () { setTimeout(start, 0); }, { once: true });
    }
})();
