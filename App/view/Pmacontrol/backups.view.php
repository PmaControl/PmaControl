<?php
$meta = $meta ?? array();
?>
<div class="container">
    <div class="section">
        <div class="lang-en">
            <h2>Backups & Recovery</h2>
            <p class="muted">Orchestrate mydumper/myloader workflows, validate backups, and execute restore runbooks with confidence.</p>
            <p class="muted">Hero variants: "Backups you can actually restore." / "Recover with confidence, not hope."</p>
        </div>
        <div class="lang-fr">
            <h2>Backups & Reprise</h2>
            <p class="muted">Orchestrer mydumper/myloader, valider les backups, et exécuter des runbooks de restauration fiables.</p>
            <p class="muted">Variantes hero : "Des backups réellement restaurables." / "Restaurer avec confiance, pas avec espoir."</p>
        </div>
    </div>
    <div class="section">
        <div class="card-grid">
            <div class="card"><h3>Orchestration</h3><p class="muted">Scheduled backups with environment-aware retention policies.</p></div>
            <div class="card"><h3>Validation</h3><p class="muted">Automated restore checks and checksum validation.</p></div>
            <div class="card"><h3>PITR-ready</h3><p class="muted">Capture binlog positions and create point-in-time workflows.</p></div>
            <div class="card"><h3>Runbooks</h3><p class="muted">Generate mydumper restore plans and execute safely.</p></div>
        </div>
    </div>
    <div class="section">
        <div class="section-title">
            <h2>What you get</h2>
        </div>
        <ul class="list-columns">
            <li>Recoverable backups with verification evidence.</li>
            <li>Faster incident recovery and less downtime.</li>
            <li>Standardized restore processes across teams.</li>
            <li>Audit-ready backup logs.</li>
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
