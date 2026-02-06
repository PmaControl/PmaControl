<?php
$meta = $meta ?? array();
?>
<div class="container">
    <div class="section">
        <div class="lang-en">
            <h2>Documentation</h2>
            <p class="muted">Installation, permissions, backups, troubleshooting, and release notes.</p>
            <p class="muted">Hero variants: "Docs for DBAs who run production." / "Every step, documented and repeatable." / "From install to incident response."</p>
        </div>
        <div class="lang-fr">
            <h2>Documentation</h2>
            <p class="muted">Installation, permissions, backups, dépannage, et notes de version.</p>
            <p class="muted">Variantes hero : "Docs pour DBA en production." / "Chaque étape documentée et reproductible." / "De l'installation à la reprise d'incident."</p>
        </div>
    </div>

    <div class="section">
        <div class="card-grid">
            <div class="card"><h3>Getting started</h3><p class="muted">Connect first MySQL/MariaDB server and create environment tags.</p></div>
            <div class="card"><h3>Installation</h3><p class="muted">Docker, Debian/Ubuntu packages, and air-gapped guidelines.</p></div>
            <div class="card"><h3>Connect servers</h3><p class="muted">MySQL/MariaDB, Galera, ProxySQL connectors.</p></div>
            <div class="card"><h3>Least privilege</h3><p class="muted">Sample grants and role-based access.</p></div>
            <div class="card"><h3>Backups</h3><p class="muted">Mydumper/myloader orchestration and validation.</p></div>
            <div class="card"><h3>Troubleshooting</h3><p class="muted">Logs, diagnostics, and health checks.</p></div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">
            <h2>Design system & components</h2>
            <p class="muted">Reusable UI building blocks for the marketing site.</p>
        </div>
        <div class="card">
            <h3>Design tokens</h3>
            <div class="code-block">
                <code>
:root {
  --pmac-primary: #00E5FF;
  --pmac-secondary: #20E3B2;
  --pmac-warning: #FFD166;
  --pmac-dark: #0B0D10;
  --pmac-card: #0F1318;
  --pmac-line: #212734;
  --pmac-text: #E6EDF3;
  --pmac-muted: #9AA7B5;
  --pmac-radius: 20px;
}
                </code>
            </div>
            <h3>Components list</h3>
            <ul>
                <li>Header, Footer, Hero, FeatureGrid, ComparisonTable</li>
                <li>Testimonial, PricingCards, FAQAccordion</li>
                <li>DocsSidebar, BlogCard, CTA band</li>
                <li>Screenshot gallery with captions</li>
            </ul>
            <h3>Style guide</h3>
            <p class="muted">Spacing: 24px base, radius 2xl, subtle shadows, card-based layout, pill buttons.</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">
            <h2>What you get</h2>
        </div>
        <ul class="list-columns">
            <li>Clear onboarding for new DBAs.</li>
            <li>Repeatable installation steps.</li>
            <li>Documentation aligned with production needs.</li>
            <li>Reusable components for marketing pages.</li>
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
