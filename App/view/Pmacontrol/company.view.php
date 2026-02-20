<?php
$meta = $meta ?? array();
?>
<div class="container">
    <div class="section">
        <div class="lang-en">
            <h2>About PmaControl</h2>
            <p class="muted">Built by a production DBA/architect with 12+ years of MySQL/MariaDB, Galera, and ProxySQL operations.</p>
            <p class="muted">Hero variants: "Built by DBAs for DBAs." / "Production experience, productized." / "A lighthouse for critical databases."</p>
        </div>
        <div class="lang-fr">
            <h2>À propos de PmaControl</h2>
            <p class="muted">Conçu par un DBA/architecte production avec 12+ ans d'expérience MySQL/MariaDB, Galera et ProxySQL.</p>
            <p class="muted">Variantes hero : "Conçu par des DBA pour des DBA." / "L'expérience production, productisée." / "Un phare pour les bases critiques."</p>
        </div>
    </div>
    <div class="section">
        <div class="card-grid">
            <div class="card"><h3>Values</h3><p class="muted">Reliability, precision, automation, transparency, pragmatism.</p></div>
            <div class="card"><h3>Brand universe</h3><p class="muted">Lighthouse, sea, vigilance, and control. Logo concept: lighthouse + sea-lion.</p></div>
            <div class="card"><h3>Audience</h3><p class="muted">CTO, DBA, SRE, and platform teams running production databases.</p></div>
        </div>
    </div>
    <div class="section">
        <div class="section-title">
            <h2>What you get</h2>
        </div>
        <ul class="list-columns">
            <li>Production-grade expertise baked into workflows.</li>
            <li>Operational clarity for leadership teams.</li>
            <li>Pragmatic automation with auditability.</li>
            <li>Transparent, no-bullshit messaging.</li>
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
