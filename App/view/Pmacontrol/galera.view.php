<?php
$meta = $meta ?? array();
?>
<div class="container">
    <div class="section">
        <div class="lang-en">
            <h2>Galera operations without fear</h2>
            <p class="muted">Bootstrap, rejoin, and maintain Galera clusters with guided steps, flow control analysis, and donor safety checks.</p>
            <p class="muted">Hero variants: "Galera maintenance with guardrails." / "Keep quorum healthy, always."</p>
        </div>
        <div class="lang-fr">
            <h2>Opérations Galera sans stress</h2>
            <p class="muted">Bootstrap, rejoin et maintenance de clusters Galera avec étapes guidées, analyse du flow control et contrôles de donor.</p>
            <p class="muted">Variantes hero : "Maintenance Galera avec garde-fous." / "Quorum sain en permanence."</p>
        </div>
    </div>
    <div class="section">
        <div class="card-grid">
            <div class="card"><h3>Bootstrap & rejoin</h3><p class="muted">Safe bootstrap, node rejoin, and membership validation.</p></div>
            <div class="card"><h3>SST/IST visibility</h3><p class="muted">Track state transfer type, donor selection, and transfer duration.</p></div>
            <div class="card"><h3>Flow control analysis</h3><p class="muted">Detect stalls and congestion with clear remediation steps.</p></div>
            <div class="card"><h3>Maintenance mode</h3><p class="muted">Drain traffic safely and verify before reintroducing nodes.</p></div>
        </div>
    </div>
    <div class="section">
        <div class="section-title">
            <h2>What you get</h2>
        </div>
        <ul class="list-columns">
            <li>Safer maintenance with reduced quorum risk.</li>
            <li>Clear visibility into SST/IST behavior.</li>
            <li>Documented runbooks for recovery.</li>
            <li>Confidence when scaling clusters.</li>
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
