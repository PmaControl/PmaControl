<?php
$meta = $meta ?? array();
?>
<div class="container">
    <div class="section">
        <div class="lang-en">
            <h2>Blog article template</h2>
            <p class="muted">Use this structure for production-ready content.</p>
            <p class="muted">Hero variants: "Template for production-grade DBA stories." / "Turn incidents into reusable knowledge." / "Structure that teams can follow."</p>
        </div>
        <div class="lang-fr">
            <h2>Template d'article</h2>
            <p class="muted">Utilisez cette structure pour un contenu orienté production.</p>
            <p class="muted">Variantes hero : "Template pour récits DBA production." / "Transformer les incidents en savoir réutilisable." / "Structure claire pour les équipes."</p>
        </div>
    </div>
    <div class="section">
        <div class="card">
            <h3>Structure</h3>
            <ol>
                <li>Problem statement (business impact)</li>
                <li>Symptoms and detection</li>
                <li>Root cause analysis</li>
                <li>Actionable fix (step-by-step)</li>
                <li>Preventive measures</li>
                <li>Checklist and runbook snippet</li>
            </ol>
        </div>
    </div>
    <div class="section">
        <div class="section-title">
            <h2>What you get</h2>
        </div>
        <ul class="list-columns">
            <li>Consistent structure for technical content.</li>
            <li>Clear business impact.</li>
            <li>Actionable remediation steps.</li>
            <li>Reusable runbook snippets.</li>
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
