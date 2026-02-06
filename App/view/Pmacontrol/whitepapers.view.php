<?php
$meta = $meta ?? array();
?>
<div class="container">
    <div class="section">
        <div class="lang-en">
            <h2>Whitepapers</h2>
            <p class="muted">Deep dives for production-grade database operations.</p>
            <p class="muted">Hero variants: "Executive-ready DBA playbooks." / "Deep dives on production resilience." / "Research-backed guidance for MySQL fleets."</p>
        </div>
        <div class="lang-fr">
            <h2>Livres blancs</h2>
            <p class="muted">Analyses approfondies pour les opérations DB production.</p>
            <p class="muted">Variantes hero : "Playbooks DBA pour dirigeants." / "Analyses sur la résilience prod." / "Guides concrets pour flottes MySQL."</p>
        </div>
    </div>
    <div class="section">
        <div class="card-grid">
            <div class="card"><h3>MySQL/MariaDB Production Checklist</h3><p class="muted">Step-by-step operational readiness.</p></div>
            <div class="card"><h3>Galera Resilience Playbook</h3><p class="muted">Cluster stability and maintenance.</p></div>
            <div class="card"><h3>ProxySQL Governance Guide</h3><p class="muted">Rules, routing, and audit practices.</p></div>
        </div>
    </div>
    <div class="section">
        <div class="section-title">
            <h2>What you get</h2>
        </div>
        <ul class="list-columns">
            <li>Executive-ready summaries.</li>
            <li>Technical checklists for DBAs.</li>
            <li>Operational best practices.</li>
            <li>Compliance guidance.</li>
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
