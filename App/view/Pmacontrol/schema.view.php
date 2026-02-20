<?php
$meta = $meta ?? array();
?>
<div class="container">
    <div class="section">
        <div class="lang-en">
            <h2>Schema drift & diff</h2>
            <p class="muted">Detect differences across environments, generate diff reports, and apply safe migrations.</p>
            <p class="muted">Hero variants: "Keep schemas aligned across environments." / "Detect drift before it breaks production."</p>
        </div>
        <div class="lang-fr">
            <h2>Dérive & diff de schémas</h2>
            <p class="muted">Détecter les différences entre environnements, produire des rapports de diff et appliquer des migrations sûres.</p>
            <p class="muted">Variantes hero : "Des schémas alignés sur tous les environnements." / "Détecter la dérive avant la prod."</p>
        </div>
    </div>
    <div class="section">
        <div class="card-grid">
            <div class="card"><h3>Diff reports</h3><p class="muted">Generate HTML/PDF reports for audit and review.</p></div>
            <div class="card"><h3>Drift detection</h3><p class="muted">Alert when schema diverges from standard definitions.</p></div>
            <div class="card"><h3>Apply migrations</h3><p class="muted">Export change scripts with approvals.</p></div>
            <div class="card"><h3>Variable schema support</h3><p class="muted">Work with heterogenous schemas while tracking deviations.</p></div>
        </div>
    </div>
    <div class="section">
        <div class="section-title">
            <h2>What you get</h2>
        </div>
        <ul class="list-columns">
            <li>Reduced schema-related incidents.</li>
            <li>Controlled rollouts across environments.</li>
            <li>Audit trails for schema changes.</li>
            <li>Faster migrations with clear diffs.</li>
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
