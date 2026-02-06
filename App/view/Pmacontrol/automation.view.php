<?php
$meta = $meta ?? array();
?>
<div class="container">
    <div class="section">
        <div class="lang-en">
            <h2>Automation & Runbooks</h2>
            <p class="muted">Schedule routine DBA tasks and run one-click actions with confirmation steps and audit logs.</p>
            <p class="muted">Hero variants: "Automation with guardrails." / "Runbooks you can trust."</p>
        </div>
        <div class="lang-fr">
            <h2>Automatisation & Runbooks</h2>
            <p class="muted">Planifiez les tâches DBA et exécutez des actions en un clic avec validations et audit.</p>
            <p class="muted">Variantes hero : "Automatiser avec garde-fous." / "Des runbooks fiables."</p>
        </div>
    </div>
    <div class="section">
        <div class="card-grid">
            <div class="card"><h3>Scheduled jobs</h3><p class="muted">Health checks, backups, schema comparisons, and reports.</p></div>
            <div class="card"><h3>One-click actions</h3><p class="muted">Restart replication, rotate logs, or enforce ProxySQL rules.</p></div>
            <div class="card"><h3>Approvals</h3><p class="muted">Confirmations with role-based validation.</p></div>
            <div class="card"><h3>Playbooks</h3><p class="muted">Reusable workflows for migrations and maintenance.</p></div>
        </div>
    </div>
    <div class="section">
        <div class="section-title">
            <h2>What you get</h2>
        </div>
        <ul class="list-columns">
            <li>Reduced manual toil for DBAs and SREs.</li>
            <li>Fewer risky manual changes.</li>
            <li>Consistent operational outcomes.</li>
            <li>Compliance-ready approvals.</li>
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
