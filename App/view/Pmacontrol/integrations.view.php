<?php
$meta = $meta ?? array();
?>
<div class="container">
    <div class="section">
        <div class="lang-en">
            <h2>Integrations</h2>
            <p class="muted">Plug PmaControl into your monitoring, chat, and identity stack.</p>
            <p class="muted">Hero variants: "Connect to the tools you already trust." / "Observability and alerts in one flow." / "Identity-ready for enterprise teams."</p>
        </div>
        <div class="lang-fr">
            <h2>Intégrations</h2>
            <p class="muted">Connectez PmaControl à votre stack monitoring, chat et identité.</p>
            <p class="muted">Variantes hero : "Branché sur vos outils existants." / "Observabilité et alertes unifiées." / "Prêt pour l'identité entreprise."</p>
        </div>
    </div>
    <div class="section">
        <div class="card-grid">
            <div class="card"><h3>Prometheus & Grafana</h3><p class="muted">Exporters and dashboards for operational KPIs.</p></div>
            <div class="card"><h3>Alertmanager</h3><p class="muted">Routing rules and deduplication for noisy alerts.</p></div>
            <div class="card"><h3>Slack / Teams</h3><p class="muted">Webhook notifications for incidents and approvals.</p></div>
            <div class="card"><h3>SSO (OIDC/SAML)</h3><p class="muted">Centralized identity and access policies.</p></div>
            <div class="card"><h3>Vault (optional)</h3><p class="muted">Secrets lifecycle management for credentials.</p></div>
            <div class="card"><h3>API & Webhooks</h3><p class="muted">Automate workflows and integrate with CI/CD.</p></div>
        </div>
    </div>
    <div class="section">
        <div class="section-title">
            <h2>What you get</h2>
        </div>
        <ul class="list-columns">
            <li>Lower alert fatigue with routed notifications.</li>
            <li>Faster onboarding with SSO.</li>
            <li>Better observability in existing dashboards.</li>
            <li>Secure secrets handling.</li>
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
