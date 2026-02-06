<?php
$meta = $meta ?? array();
?>
<div class="container">
    <div class="section">
        <div class="lang-en">
            <h2>Performance toolkit for DBAs</h2>
            <p class="muted">Diagnose slow queries, find missing or unused indexes, and understand buffer pool behavior. Optional AI provides suggestions while keeping humans in control.</p>
            <p class="muted">Hero variants: "Turn slow queries into safe fixes." / "Performance workbench built for DBAs."</p>
        </div>
        <div class="lang-fr">
            <h2>Boîte à outils performance pour DBA</h2>
            <p class="muted">Diagnostiquer les requêtes lentes, trouver les index manquants ou inutiles, comprendre le buffer pool. L'IA est optionnelle et reste sous contrôle humain.</p>
            <p class="muted">Variantes hero : "Transformer les requêtes lentes en corrections sûres." / "Workbench performance pour DBA."</p>
        </div>
    </div>
    <div class="section">
        <div class="card-grid">
            <div class="card"><h3>Slow log triage</h3><p class="muted">Filter by schema, host, or service; annotate regressions.</p></div>
            <div class="card"><h3>Top queries</h3><p class="muted">CPU, IO, or lock heavy queries with regression history.</p></div>
            <div class="card"><h3>Index advisor</h3><p class="muted">Detect missing indexes and remove unused ones safely.</p></div>
            <div class="card"><h3>Table bloat</h3><p class="muted">Track fragmentation, space pressure, and compaction opportunities.</p></div>
            <div class="card"><h3>Buffer pool insights</h3><p class="muted">Cache hit ratios, hot pages, and memory hotspots.</p></div>
            <div class="card"><h3>AI optional</h3><p class="muted">Summaries and suggestions with a human-in-control disclaimer.</p></div>
        </div>
    </div>
    <div class="section">
        <div class="alert">
            <strong>Human-in-control:</strong> AI suggestions never execute automatically. Every change is reviewed and approved by a DBA.
        </div>
    </div>
    <div class="section">
        <div class="section-title">
            <h2>What you get</h2>
        </div>
        <ul class="list-columns">
            <li>Faster query optimization cycles.</li>
            <li>Reduced risk through safe index recommendations.</li>
            <li>Visibility into buffer pool and storage pressure.</li>
            <li>Actionable performance reporting for stakeholders.</li>
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
