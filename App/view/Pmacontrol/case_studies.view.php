<?php
$meta = $meta ?? array();
?>
<div class="container">
    <div class="section">
        <div class="lang-en">
            <h2>Case studies</h2>
            <p class="muted">Placeholder examples with before/after metrics.</p>
            <p class="muted">Hero variants: "Proof from production teams." / "Before & after metrics that matter." / "Operational wins, measured."</p>
        </div>
        <div class="lang-fr">
            <h2>Études de cas</h2>
            <p class="muted">Exemples placeholders avec métriques avant/après.</p>
            <p class="muted">Variantes hero : "Preuves issues de la production." / "Métriques avant/après qui comptent." / "Gains opérationnels mesurés."</p>
        </div>
    </div>
    <div class="section">
        <div class="card-grid">
            <div class="card">
                <h3>FinTech EU</h3>
                <p class="muted"><strong>Before:</strong> MTTR 2h, weekly incidents.</p>
                <p class="muted"><strong>After:</strong> MTTR 45min, incident rate -30%.</p>
                <p class="muted">Actions: standardized runbooks, query regression tracking.</p>
            </div>
            <div class="card">
                <h3>E-commerce</h3>
                <p class="muted"><strong>Before:</strong> slow query backlog, schema drift across regions.</p>
                <p class="muted"><strong>After:</strong> 50% faster migrations, drift resolved in 2 weeks.</p>
                <p class="muted">Actions: schema diff, proxy routing previews.</p>
            </div>
            <div class="card">
                <h3>SaaS B2B</h3>
                <p class="muted"><strong>Before:</strong> Galera maintenance failures.</p>
                <p class="muted"><strong>After:</strong> zero failed rejoin operations over 6 months.</p>
                <p class="muted">Actions: donor controls, flow control monitoring.</p>
            </div>
        </div>
    </div>
    <div class="section">
        <div class="section-title">
            <h2>What you get</h2>
        </div>
        <ul class="list-columns">
            <li>Clear, measurable outcomes.</li>
            <li>Operational visibility with evidence.</li>
            <li>Faster, safer migrations.</li>
            <li>Stable production clusters.</li>
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
