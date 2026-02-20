<?php
$meta = $meta ?? array();
?>
<div class="container">
    <div class="section">
        <div class="lang-en">
            <h2>AI features (optional)</h2>
            <p class="muted">AI helps summarize slow query patterns, propose index suggestions, and explain schema drift. Humans remain in control.</p>
            <p class="muted">Hero variants: "AI suggestions with DBA approval." / "Speed up analysis without losing control." / "Explainability for every recommendation."</p>
        </div>
        <div class="lang-fr">
            <h2>Fonctionnalités IA (optionnelles)</h2>
            <p class="muted">L'IA résume les requêtes lentes, propose des index, et explique la dérive de schéma. L'humain garde le contrôle.</p>
            <p class="muted">Variantes hero : "Des suggestions IA validées par le DBA." / "Accélérer l'analyse sans perdre le contrôle." / "Explicabilité pour chaque recommandation."</p>
        </div>
    </div>
    <div class="section">
        <div class="card-grid">
            <div class="card"><h3>Index suggestions</h3><p class="muted">AI highlights candidate indexes with estimated impact.</p></div>
            <div class="card"><h3>Query insights</h3><p class="muted">Summaries of top regressions and lock hotspots.</p></div>
            <div class="card"><h3>Schema drift explanations</h3><p class="muted">Plain-language descriptions of schema changes.</p></div>
        </div>
    </div>
    <div class="section">
        <div class="alert">
            <strong>Disclaimer:</strong> AI outputs are suggestions only. A DBA must review and approve all changes.
        </div>
    </div>
    <div class="section">
        <div class="section-title">
            <h2>What you get</h2>
        </div>
        <ul class="list-columns">
            <li>Faster triage without losing human control.</li>
            <li>Clear explanations for non-DBA stakeholders.</li>
            <li>Safer index decisions.</li>
            <li>Reduced cognitive load during incidents.</li>
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
