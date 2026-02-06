<?php
$meta = $meta ?? array();
?>
<div class="container">
    <div class="section">
        <div class="lang-en">
            <h2>Blog</h2>
            <p class="muted">Production stories, DBA playbooks, and technical deep dives.</p>
            <p class="muted">Hero variants: "Lessons from real production incidents." / "DBA playbooks without fluff." / "Technical deep dives for MySQL teams."</p>
        </div>
        <div class="lang-fr">
            <h2>Blog</h2>
            <p class="muted">Histoires de production, playbooks DBA et analyses techniques.</p>
            <p class="muted">Variantes hero : "Leçons d'incidents réels." / "Playbooks DBA sans bullshit." / "Analyses techniques pour équipes MySQL."</p>
        </div>
    </div>
    <div class="section">
        <div class="card-grid">
            <div class="card"><h3>Placeholder article</h3><p class="muted">How to detect unused indexes safely in production.</p></div>
            <div class="card"><h3>Placeholder article</h3><p class="muted">ProxySQL rule simulator: avoid routing disasters.</p></div>
            <div class="card"><h3>Placeholder article</h3><p class="muted">Galera flow control explained.</p></div>
        </div>
    </div>
    <div class="section">
        <div class="section-title">
            <h2>What you get</h2>
        </div>
        <ul class="list-columns">
            <li>Focused DBA content.</li>
            <li>Hands-on troubleshooting guides.</li>
            <li>Migration and tuning playbooks.</li>
            <li>Operational best practices.</li>
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
