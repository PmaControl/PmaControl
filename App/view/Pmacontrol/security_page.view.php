<?php
$meta = $meta ?? array();
?>
<div class="container">
    <div class="section">
        <div class="lang-en">
            <h2>Security</h2>
            <p class="muted">Responsible disclosure, data handling, and operational security.</p>
            <p class="muted">Hero variants: "Security you can audit." / "Operational trust for DBA teams." / "Transparent security practices."</p>
        </div>
        <div class="lang-fr">
            <h2>Sécurité</h2>
            <p class="muted">Divulgation responsable, gestion des données et sécurité opérationnelle.</p>
            <p class="muted">Variantes hero : "Sécurité auditable." / "Confiance opérationnelle pour DBA." / "Pratiques de sécurité transparentes."</p>
        </div>
    </div>

    <div class="section">
        <div class="card-grid">
            <div class="card"><h3>Responsible disclosure</h3><p class="muted">Report issues to security@pmacontrol.local with a 90-day disclosure window.</p></div>
            <div class="card"><h3>Data handling</h3><p class="muted">On-prem deployments keep data under your control.</p></div>
            <div class="card"><h3>Auditability</h3><p class="muted">All actions logged with user, timestamp, and context.</p></div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">
            <h2>What you get</h2>
        </div>
        <ul class="list-columns">
            <li>Clear security policies.</li>
            <li>Transparent vulnerability reporting.</li>
            <li>Auditable operational actions.</li>
            <li>Controlled data ownership.</li>
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
