<?php
$meta = $meta ?? array();
?>
<div class="container">
    <div class="section">
        <div class="lang-en">
            <h2>Security & RBAC</h2>
            <p class="muted">Granular roles, audit trails, TLS enforcement, and secrets integration (Vault optional). Built for regulated environments.</p>
            <p class="muted">Hero variants: "Security posture for database operations." / "Every change is traceable."</p>
        </div>
        <div class="lang-fr">
            <h2>Sécurité & RBAC</h2>
            <p class="muted">Rôles granulaires, logs d'audit, TLS, intégration secrets (Vault optionnel). Conçu pour les environnements régulés.</p>
            <p class="muted">Variantes hero : "Posture sécurité pour opérations DBA." / "Chaque changement est traçable."</p>
        </div>
    </div>
    <div class="section">
        <div class="card-grid">
            <div class="card"><h3>Role-based access</h3><p class="muted">Separate viewer, operator, and admin roles per environment.</p></div>
            <div class="card"><h3>Audit logging</h3><p class="muted">Track actions, approvals, and export logs for compliance.</p></div>
            <div class="card"><h3>TLS everywhere</h3><p class="muted">Enforce encrypted connections across services.</p></div>
            <div class="card"><h3>Secrets vault</h3><p class="muted">Optionally integrate with HashiCorp Vault.</p></div>
        </div>
    </div>
    <div class="section">
        <div class="section-title">
            <h2>What you get</h2>
        </div>
        <ul class="list-columns">
            <li>Least-privilege operations.</li>
            <li>Compliance-ready audit trails.</li>
            <li>Reduced risk from credential sprawl.</li>
            <li>Security reviews that pass faster.</li>
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
