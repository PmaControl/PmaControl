<?php
$meta = $meta ?? array();
?>
<div class="container">
    <div class="section">
        <div class="lang-switch">
            <span class="lang-pill">EN / FR available</span>
        </div>
        <div class="lang-en">
            <span class="badge">Production-grade control plane</span>
            <h2>One cockpit for MySQL/MariaDB + ProxySQL + Galera: observe, diagnose, fix, automate.</h2>
            <p class="muted">PmaControl is built by a production DBA with 12+ years of MySQL/MariaDB, Galera, and ProxySQL firefighting experience. It gives CTOs, DBAs, and SREs a precise, audit-ready view of their database fleets with runbook automation.</p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="<?= LINK ?>pmacontrol/contact">Request a demo</a>
                <a class="btn btn-outline" href="<?= LINK ?>pmacontrol/pricing">Download (self-hosted)</a>
                <a class="btn btn-ghost" href="<?= LINK ?>pmacontrol/docs">See docs</a>
            </div>
            <p class="muted">Hero variants: "Production-grade control for MySQL fleets." / "Stop firefighting: standardize your DBA operations." / "From dashboards to one-click runbooks, everything in one cockpit."</p>
        </div>
        <div class="lang-fr">
            <span class="badge">Plateforme de contrôle production-grade</span>
            <h2>Un cockpit unique pour MySQL/MariaDB + ProxySQL + Galera : observer, diagnostiquer, corriger, automatiser.</h2>
            <p class="muted">PmaControl est conçu par un DBA production (12+ ans) spécialisé MySQL/MariaDB, Galera et ProxySQL. Il apporte aux CTO, DBA et SRE une visibilité fiable, des audits, et des runbooks d'automatisation concrets.</p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="<?= LINK ?>pmacontrol/contact">Demander une démo</a>
                <a class="btn btn-outline" href="<?= LINK ?>pmacontrol/pricing">Télécharger (self-hosted)</a>
                <a class="btn btn-ghost" href="<?= LINK ?>pmacontrol/docs">Voir la doc</a>
            </div>
            <p class="muted">Variantes hero : "Contrôle production-grade pour flottes MySQL." / "Stopper le firefighting : standardiser les opérations DBA." / "Du dashboard au runbook en un clic, tout dans un cockpit."</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">
            <h2>Trust & positioning</h2>
            <p class="muted">Designed for teams who run critical MySQL/MariaDB and need a reliable lighthouse for operations.</p>
        </div>
        <div class="card-grid">
            <div class="card">
                <h3>Production-grade by design</h3>
                <p class="muted">Built for real incidents: slow queries, replication drift, disk pressure, broken failovers, and risky Galera operations.</p>
            </div>
            <div class="card">
                <h3>Human-in-control automation</h3>
                <p class="muted">One-click actions with confirmations, runbooks, and audit trails. No black-box automation.</p>
            </div>
            <div class="card">
                <h3>Works with variable schemas</h3>
                <p class="muted">Compare and standardize schema versions across servers and environments, even when drift exists.</p>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">
            <h2>Benefits that impact the business</h2>
            <p class="muted">Reduce MTTR, prevent incidents, and shorten migrations while giving DBAs a reproducible workflow.</p>
        </div>
        <div class="card-grid">
            <div class="card">
                <h3>MTTR down 35%</h3>
                <p class="muted">Unified dashboards + runbooks cut detection-to-fix time during incidents.</p>
            </div>
            <div class="card">
                <h3>Incident rate down 25%</h3>
                <p class="muted">Performance toolkit flags unused indexes, table bloat, and risky queries before they blow up.</p>
            </div>
            <div class="card">
                <h3>Time-to-migrate down 40%</h3>
                <p class="muted">Schema diff, ProxySQL rule simulator, and Galera rejoin playbooks accelerate migrations.</p>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">
            <h2>Features snapshot</h2>
            <p class="muted">Monitoring, performance, security, and automation in one cockpit.</p>
        </div>
        <div class="card-grid">
            <div class="card">
                <h3>Inventory & tagging</h3>
                <p class="muted">Multi-env inventory (DEV/INT/PREPROD/PROD), tags, ownership, compliance fields.</p>
            </div>
            <div class="card">
                <h3>Monitoring & dashboards</h3>
                <p class="muted">Queries, latency, locks, replication, Galera health, ProxySQL routing stats.</p>
            </div>
            <div class="card">
                <h3>Performance toolkit</h3>
                <p class="muted">Slow logs, top queries, index advisor (AI optional), buffer pool insights.</p>
            </div>
            <div class="card">
                <h3>Backup & recovery</h3>
                <p class="muted">Mydumper/myloader orchestration, validation, PITR-ready runbooks.</p>
            </div>
            <div class="card">
                <h3>Security & RBAC</h3>
                <p class="muted">Granular permissions, audit logs, TLS enforcement, Vault integration.</p>
            </div>
            <div class="card">
                <h3>Automation & runbooks</h3>
                <p class="muted">Scheduled jobs, approvals, playbooks for routine DBA tasks.</p>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">
            <h2>Screenshot placeholders</h2>
            <p class="muted">Six key views to illustrate the product.</p>
        </div>
        <div class="card-grid">
            <div class="card"><h3>Monitoring dashboard</h3><p class="muted">/img/product/monitoring-dashboard.png — latency, locks, replication, and alerts.</p></div>
            <div class="card"><h3>Performance lab</h3><p class="muted">/img/product/performance-toolkit.png — slow query triage and index advisor.</p></div>
            <div class="card"><h3>Galera operations</h3><p class="muted">/img/product/galera-operations.png — flow control, donor selection, rejoin safety.</p></div>
            <div class="card"><h3>ProxySQL routing</h3><p class="muted">/img/product/proxysql-routing.png — hostgroups and rule simulator.</p></div>
            <div class="card"><h3>Schema diff</h3><p class="muted">/img/product/schema-diff.png — drift detection across environments.</p></div>
            <div class="card"><h3>Runbooks</h3><p class="muted">/img/product/runbook-automation.png — one-click jobs with approvals.</p></div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">
            <h2>Use-cases</h2>
            <p class="muted">Solutions mapped to CTO/DBA/DevOps pain points.</p>
        </div>
        <div class="card-grid">
            <div class="card">
                <h3>Reduce incidents (MTTR)</h3>
                <p class="muted">Unified incident timeline, alert routing, and guided recovery steps.</p>
            </div>
            <div class="card">
                <h3>Scale Galera safely</h3>
                <p class="muted">Bootstrap/rejoin workflows, donor controls, and health-gated maintenance.</p>
            </div>
            <div class="card">
                <h3>ProxySQL governance</h3>
                <p class="muted">Ruleset reviews, routing visualization, and safe rollout steps.</p>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">
            <h2>Testimonials (placeholders)</h2>
        </div>
        <div class="card-grid">
            <div class="card"><p class="muted">"PmaControl brought order to a chaotic MySQL fleet." — FinTech EU, Head of Engineering</p></div>
            <div class="card"><p class="muted">"We cut recovery time dramatically by standardizing runbooks." — E-commerce, DBA Lead</p></div>
            <div class="card"><p class="muted">"ProxySQL governance finally became auditable." — SaaS B2B, SRE Manager</p></div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">
            <h2>Self-hosted vs SaaS</h2>
            <p class="muted">Choose your deployment model. SaaS is coming soon.</p>
        </div>
        <div class="card-grid">
            <div class="card">
                <h3>Self-hosted / On-prem</h3>
                <p class="muted">Full control, air-gapped ready, integrates with your existing security stack.</p>
                <a class="btn btn-outline" href="<?= LINK ?>pmacontrol/pricing">Download & pricing</a>
            </div>
            <div class="card">
                <h3>SaaS (coming soon)</h3>
                <p class="muted">Managed control plane, multi-tenant alerting, and automatic upgrades.</p>
                <a class="btn btn-ghost" href="<?= LINK ?>pmacontrol/contact">Join the waitlist</a>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">
            <h2>Lead magnet</h2>
            <p class="muted">Download the “MySQL/MariaDB Production Checklist” PDF.</p>
        </div>
        <div class="card">
            <p class="muted">Includes: replication readiness, ProxySQL safety checks, Galera maintenance checklist, backup validation steps.</p>
            <a class="btn btn-primary" href="<?= LINK ?>pmacontrol/contact">Get the checklist</a>
        </div>
    </div>

    <div class="section">
        <div class="section-title">
            <h2>What you get</h2>
            <p class="muted">Measured outcomes from teams using PmaControl.</p>
        </div>
        <ul class="list-columns">
            <li>35% faster incident resolution with standardized runbooks.</li>
            <li>25% fewer production incidents from proactive query analysis.</li>
            <li>40% faster migrations with schema diff + ProxySQL routing previews.</li>
            <li>Audit-ready changes with RBAC and approval workflows.</li>
            <li>Confidence that variable schemas are detected and documented.</li>
        </ul>
    </div>

    <div class="section">
        <div class="section-title">
            <h2>Sitemap & mega menu</h2>
        </div>
        <div class="card">
            <p class="muted">Product: Overview, Monitoring, Performance, Backups, Galera, ProxySQL, Schema Drift, Security, Automation, AI Features.</p>
            <p class="muted">Solutions: Reduce incidents, Scale Galera, Standardize DBA ops, ProxySQL governance, Migration factory.</p>
            <p class="muted">Resources: Docs, Blog, Case studies, Whitepapers, Webinars.</p>
            <p class="muted">Company: About, Roadmap, Security, Contact, Legal.</p>
        </div>
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
