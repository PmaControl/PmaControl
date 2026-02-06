<?php
$meta = $meta ?? array();
?>
<div class="container">
    <div class="section">
        <div class="lang-en">
            <h2>Product overview</h2>
            <p class="muted">PmaControl is the operational cockpit for MySQL/MariaDB, ProxySQL, and Galera. Inventory everything, monitor performance, compare schema drift, automate runbooks, and deliver audit-ready operations.</p>
            <p class="muted">Hero variants: "Everything a DBA needs to run production MySQL." / "Control plane for MySQL, ProxySQL, and Galera." / "From incidents to automation: one cockpit."</p>
        </div>
        <div class="lang-fr">
            <h2>Vue d'ensemble produit</h2>
            <p class="muted">PmaControl est le cockpit opérationnel pour MySQL/MariaDB, ProxySQL et Galera. Inventorier, monitorer, comparer les schémas, automatiser les runbooks et produire des opérations auditables.</p>
            <p class="muted">Variantes hero : "Tout ce qu'un DBA attend pour la prod MySQL." / "Control plane pour MySQL, ProxySQL et Galera." / "De l'incident à l'automatisation : un seul cockpit."</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">
            <h2>Architecture & positioning</h2>
            <p class="muted">Built for CTOs, DBAs, and SRE teams who need predictable operations.</p>
        </div>
        <div class="card-grid">
            <div class="card">
                <h3>Inventory & environment mapping</h3>
                <p class="muted">Track environments (DEV/INT/PREPROD/PROD), tags, ownership, compliance, and contact points.</p>
            </div>
            <div class="card">
                <h3>Unified monitoring</h3>
                <p class="muted">Dashboards combine queries, latency, replication, Galera status, and ProxySQL routing.</p>
            </div>
            <div class="card">
                <h3>Automation with guardrails</h3>
                <p class="muted">One-click actions demand approvals and leave audit trails.</p>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">
            <h2>Key capabilities</h2>
        </div>
        <div class="card-grid">
            <div class="card"><h3>Performance toolkit</h3><p class="muted">Slow logs, top queries, index advisor, unused index detection, buffer pool insights.</p></div>
            <div class="card"><h3>Schema drift & diff</h3><p class="muted">Detect schema divergence and generate apply-ready migration scripts.</p></div>
            <div class="card"><h3>Backup & restore</h3><p class="muted">Mydumper/myloader orchestration, validation, and restore drills.</p></div>
            <div class="card"><h3>Galera & ProxySQL</h3><p class="muted">Bootstrap safety, donor control, routing visualization, and rule simulator.</p></div>
            <div class="card"><h3>Security</h3><p class="muted">RBAC, audit logs, secrets management (Vault option), TLS enforcement.</p></div>
            <div class="card"><h3>Reporting</h3><p class="muted">PDF/HTML exports for audits, compliance, and executive updates.</p></div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">
            <h2>What you get</h2>
        </div>
        <ul class="list-columns">
            <li>Single source of truth for database estates.</li>
            <li>Standardized DBA operations across environments.</li>
            <li>Faster decision-making with clear risk indicators.</li>
            <li>Compliance-ready reporting and traceability.</li>
            <li>Better uptime through proactive monitoring.</li>
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
