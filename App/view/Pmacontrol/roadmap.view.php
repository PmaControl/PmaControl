<?php
$meta = $meta ?? array();
?>
<div class="container">
    <div class="section">
        <div class="lang-en">
            <h2>Public roadmap</h2>
            <p class="muted">Now / Next / Later product milestones.</p>
            <p class="muted">Hero variants: "Transparent priorities for DBA teams." / "See what ships next." / "Roadmap built with production feedback."</p>
        </div>
        <div class="lang-fr">
            <h2>Roadmap publique</h2>
            <p class="muted">Maintenant / Ensuite / Plus tard.</p>
            <p class="muted">Variantes hero : "Des priorités transparentes." / "Voir ce qui arrive." / "Roadmap guidée par la production."</p>
        </div>
    </div>
    <div class="section">
        <div class="card-grid">
            <div class="card">
                <h3>Now</h3>
                <ol>
                    <li>Unified monitoring dashboard</li>
                    <li>Schema drift reporting</li>
                    <li>ProxySQL rule simulator</li>
                    <li>Backup validation drills</li>
                </ol>
            </div>
            <div class="card">
                <h3>Next</h3>
                <ol>
                    <li>AI-assisted index suggestions (opt-in)</li>
                    <li>Galera maintenance automation</li>
                    <li>SSO (OIDC/SAML) integration</li>
                    <li>Compliance export center</li>
                </ol>
            </div>
            <div class="card">
                <h3>Later</h3>
                <ol>
                    <li>Managed SaaS control plane</li>
                    <li>Multi-region fleet manager</li>
                    <li>Plugin marketplace</li>
                    <li>Automated migration factory</li>
                </ol>
            </div>
        </div>
    </div>
    <div class="section">
        <div class="section-title">
            <h2>What you get</h2>
        </div>
        <ul class="list-columns">
            <li>Transparent roadmap alignment.</li>
            <li>Visibility into upcoming capabilities.</li>
            <li>Opportunity to influence priorities.</li>
            <li>Clear differentiation between present and future.</li>
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
