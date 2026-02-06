<?php
$meta = $meta ?? array();
?>
<div class="container">
    <div class="section">
        <div class="lang-en">
            <h2>ProxySQL governance</h2>
            <p class="muted">Visualize hostgroups, routing rules, mirroring, and healthchecks. Simulate changes before rollout.</p>
            <p class="muted">Hero variants: "ProxySQL rules without blind spots." / "Routing visibility for every query path."</p>
        </div>
        <div class="lang-fr">
            <h2>Gouvernance ProxySQL</h2>
            <p class="muted">Visualiser hostgroups, règles de routage, mirroring et healthchecks. Simuler les changements avant déploiement.</p>
            <p class="muted">Variantes hero : "Des règles ProxySQL sans angle mort." / "Visibilité du routage pour chaque requête."</p>
        </div>
    </div>
    <div class="section">
        <div class="card-grid">
            <div class="card"><h3>Hostgroup map</h3><p class="muted">Topology view and load distribution by hostgroup.</p></div>
            <div class="card"><h3>Rule simulator</h3><p class="muted">Test queries against rules before deployment.</p></div>
            <div class="card"><h3>Healthchecks</h3><p class="muted">Track offline/online status and response times.</p></div>
            <div class="card"><h3>Mirroring</h3><p class="muted">Observe mirror traffic and compare results.</p></div>
        </div>
    </div>
    <div class="section">
        <div class="section-title">
            <h2>What you get</h2>
        </div>
        <ul class="list-columns">
            <li>Fewer routing errors and faster rollbacks.</li>
            <li>Audit-ready rule changes.</li>
            <li>Better separation of read/write traffic.</li>
            <li>Confidence in multi-hostgroup governance.</li>
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
