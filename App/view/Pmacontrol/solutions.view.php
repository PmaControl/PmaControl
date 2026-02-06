<?php
$meta = $meta ?? array();
?>
<div class="container">
    <div class="section">
        <div class="lang-en">
            <h2>Solutions by use-case</h2>
            <p class="muted">Concrete workflows for CTOs, DBAs, and DevOps teams.</p>
            <p class="muted">Hero variants: "Operational solutions, not slideware." / "Use-cases mapped to real incidents." / "Solve problems before they escalate."</p>
        </div>
        <div class="lang-fr">
            <h2>Solutions par cas d'usage</h2>
            <p class="muted">Des workflows concrets pour CTO, DBA et DevOps.</p>
            <p class="muted">Variantes hero : "Solutions opérationnelles, pas des slides." / "Cas d'usage issus d'incidents réels." / "Résoudre avant l'escalade."</p>
        </div>
    </div>

    <div class="section">
        <div class="card-grid">
            <div class="card">
                <h3>Reduce incidents (MTTR)</h3>
                <p class="muted">Incident timeline, query regression tracking, and guided recovery runbooks.</p>
                <p class="muted">Actions: detect slow queries, isolate noisy neighbors, replay recovery plan.</p>
            </div>
            <div class="card">
                <h3>Scale Galera safely</h3>
                <p class="muted">Flow control analysis, donor selection, and safe maintenance mode.</p>
                <p class="muted">Actions: bootstrap cluster, validate quorum, rejoin with SST/IST checks.</p>
            </div>
            <div class="card">
                <h3>Standardize DBA ops</h3>
                <p class="muted">Automate repetitive tasks with runbooks and approval gates.</p>
                <p class="muted">Actions: scheduled backups, schema drift checks, index cleanups.</p>
            </div>
            <div class="card">
                <h3>ProxySQL governance</h3>
                <p class="muted">Routing rule reviews and query path visibility.</p>
                <p class="muted">Actions: rule simulator, hostgroup health checks, mirror testing.</p>
            </div>
            <div class="card">
                <h3>Migration factory</h3>
                <p class="muted">Standardized migrations with schema diff and ProxySQL routing previews.</p>
                <p class="muted">Actions: compare schema, generate change plan, execute controlled rollout.</p>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">
            <h2>What you get</h2>
        </div>
        <ul class="list-columns">
            <li>Faster incident triage.</li>
            <li>Repeatable migration playbooks.</li>
            <li>Clear operational ownership.</li>
            <li>Reduced operational costs.</li>
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
