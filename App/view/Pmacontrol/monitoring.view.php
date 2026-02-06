<?php
$meta = $meta ?? array();
?>
<div class="container">
    <div class="section">
        <div class="lang-en">
            <h2>Monitoring built for production</h2>
            <p class="muted">Track queries, latency, locks, replication health, Galera status, and ProxySQL routing. Each dashboard includes context and runbook actions.</p>
            <p class="muted">Hero variants: "Observe every layer of your database fleet." / "See incidents before your customers do."</p>
        </div>
        <div class="lang-fr">
            <h2>Monitoring pensé pour la production</h2>
            <p class="muted">Suivez les requêtes, la latence, les locks, la réplication, l'état Galera et le routing ProxySQL. Chaque dashboard propose un contexte et des actions runbook.</p>
            <p class="muted">Variantes hero : "Observer chaque couche de votre flotte." / "Voir l'incident avant vos clients."</p>
        </div>
    </div>
    <div class="section">
        <div class="section-title">
            <h2>Dashboards & alerts</h2>
        </div>
        <div class="card-grid">
            <div class="card"><h3>Query & latency</h3><p class="muted">Heatmaps, P95/P99 latency, slow query sampling, top offenders.</p></div>
            <div class="card"><h3>Replication & Galera</h3><p class="muted">Lag, quorum, flow control, donor health, SST/IST visibility.</p></div>
            <div class="card"><h3>ProxySQL metrics</h3><p class="muted">Hostgroup health, query routing, mirror traffic, and rule hit ratio.</p></div>
            <div class="card"><h3>Storage & bloat</h3><p class="muted">Table growth, fragmentation, disk pressure, buffer pool pressure.</p></div>
        </div>
    </div>
    <div class="section">
        <div class="section-title">
            <h2>Use-case workflows</h2>
        </div>
        <div class="card-grid">
            <div class="card"><h3>Incident triage</h3><p class="muted">One timeline aggregates alerts, deployments, and query spikes.</p></div>
            <div class="card"><h3>Capacity planning</h3><p class="muted">Trending charts project storage, QPS, and connection headroom.</p></div>
            <div class="card"><h3>Compliance reporting</h3><p class="muted">Export weekly health summaries to PDF or HTML.</p></div>
        </div>
    </div>
    <div class="section">
        <div class="section-title">
            <h2>What you get</h2>
        </div>
        <ul class="list-columns">
            <li>Faster detection and contextual alerting.</li>
            <li>Unified timeline for incidents and changes.</li>
            <li>Reliable KPIs for CTO dashboards.</li>
            <li>Evidence of monitoring coverage across environments.</li>
        </ul>
    </div>
    <div class="section">
        <div class="section-title">
            <h2>SEO Pack</h2>
        </div>
        <div class="card">
            <p class="muted"><strong>Meta title:</strong> <?= htmlspecialchars($meta['title'] ?? '', ENT_QUOTES); ?></p>
            <p class="muted"><strong>Meta description:</strong> <?= htmlspecialchars($meta['description'] ?? '', ENT_QUOTES); ?></p>
            <p class="muted"><strong>Slug:</strong> <?= htmlspecialchars($meta['slug'] ?? '', ENT_QUOTES); ?></p>
            <p class="muted"><strong>Keywords:</strong> <?= htmlspecialchars($meta['keywords'] ?? '', ENT_QUOTES); ?></p>
            <p class="muted"><strong>Schema.org:</strong> <?= htmlspecialchars($meta['schema'] ?? '', ENT_QUOTES); ?></p>
        </div>
    </div>
</div>
