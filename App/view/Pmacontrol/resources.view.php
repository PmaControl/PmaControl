<?php
$meta = $meta ?? array();
?>
<div class="container">
    <div class="section">
        <div class="lang-en">
            <h2>Resources</h2>
            <p class="muted">Blog, case studies, whitepapers, and workshops for production DBAs.</p>
            <p class="muted">Hero variants: "Production-grade resources for DB teams." / "Playbooks, workshops, and deep dives." / "Everything to run reliable databases."</p>
        </div>
        <div class="lang-fr">
            <h2>Ressources</h2>
            <p class="muted">Blog, études de cas, livres blancs et workshops pour DBA production.</p>
            <p class="muted">Variantes hero : "Ressources production-grade pour équipes DB." / "Playbooks, workshops et analyses." / "Tout pour opérer des bases fiables."</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">
            <h2>Blog content plan (20 ideas)</h2>
        </div>
        <div class="card">
            <ol class="list-columns">
                <li>How to detect unused indexes safely in production MySQL</li>
                <li>ProxySQL rule simulator: avoid routing disasters</li>
                <li>Galera flow control: what it means and how to tune it</li>
                <li>Schema drift detection across multi-env MySQL fleets</li>
                <li>Production checklist for MySQL/MariaDB upgrades</li>
                <li>Top 10 slow query patterns and fixes</li>
                <li>Designing backup validation drills</li>
                <li>Buffer pool myths: what really improves cache hit rates</li>
                <li>Reducing MTTR with standardized DBA runbooks</li>
                <li>ProxySQL hostgroup governance best practices</li>
                <li>Galera bootstrap pitfalls and how to avoid them</li>
                <li>MariaDB replication lag: causes and remediation</li>
                <li>Comparing schema differences with SQL diff tooling</li>
                <li>Building an audit trail for database changes</li>
                <li>MySQL index advisor: human-in-control approach</li>
                <li>Disk space crisis playbook for DBAs</li>
                <li>From alerts to action: designing DBA dashboards</li>
                <li>Standardizing migrations with ProxySQL routing</li>
                <li>Security least privilege grants for MySQL</li>
                <li>ProxySQL + Galera: operational checklist</li>
            </ol>
        </div>
    </div>

    <div class="section">
        <div class="section-title">
            <h2>Global FAQ (10)</h2>
        </div>
        <div class="card">
            <ol>
                <li>Is PmaControl self-hosted? Yes, on-prem is available today.</li>
                <li>Which databases are supported? MySQL, MariaDB, ProxySQL, Galera.</li>
                <li>Does it support multi-environments? Yes, with tags and groups.</li>
                <li>Can we manage variable schemas? Yes, with drift detection.</li>
                <li>Is automation safe? Yes, with approvals and audit trails.</li>
                <li>Can we integrate with Grafana? Yes via exporters.</li>
                <li>Does it support backups? Yes, with mydumper/myloader orchestration.</li>
                <li>Is SaaS available? Coming soon.</li>
                <li>Do you provide professional services? Yes, for migrations and tuning.</li>
                <li>Does PmaControl include AI features? Optional, human-in-control.</li>
            </ol>
        </div>
    </div>

    <div class="section">
        <div class="section-title">
            <h2>Security FAQ (10)</h2>
        </div>
        <div class="card">
            <ol>
                <li>Is data encrypted in transit? Yes, TLS everywhere.</li>
                <li>Is data encrypted at rest? Supported via your storage layer.</li>
                <li>Do you support RBAC? Yes, granular roles and scopes.</li>
                <li>Are audit logs immutable? Logs can be exported to secure storage.</li>
                <li>Do you support SSO? OIDC/SAML in Pro/Enterprise.</li>
                <li>Can we use Vault? Yes, optional integration.</li>
                <li>How do you handle secrets? Scoped credentials and rotation.</li>
                <li>Is there a responsible disclosure policy? Yes on the Security page.</li>
                <li>Is the on-prem version air-gapped? Yes.</li>
                <li>How are updates handled? Controlled upgrade workflows.</li>
            </ol>
        </div>
    </div>

    <div class="section">
        <div class="section-title">
            <h2>What you get</h2>
        </div>
        <ul class="list-columns">
            <li>Focused resources for DBA and CTO audiences.</li>
            <li>Content aligned with production challenges.</li>
            <li>Security and compliance guidance.</li>
            <li>Actionable playbooks and workshops.</li>
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
