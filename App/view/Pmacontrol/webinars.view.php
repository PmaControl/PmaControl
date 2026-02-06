<?php
$meta = $meta ?? array();
?>
<div class="container">
    <div class="section">
        <div class="lang-en">
            <h2>Webinars & Workshops</h2>
            <p class="muted">Hands-on training: 3-day Galera + ProxySQL workshop.</p>
            <p class="muted">Hero variants: "Train your DB team with real labs." / "Workshops built for production incidents." / "Learn Galera and ProxySQL the hard way."</p>
        </div>
        <div class="lang-fr">
            <h2>Webinars & Workshops</h2>
            <p class="muted">Formation pratique : workshop 3 jours Galera + ProxySQL.</p>
            <p class="muted">Variantes hero : "Former vos équipes DB avec des labs réels." / "Workshops basés sur incidents prod." / "Apprendre Galera et ProxySQL en profondeur."</p>
        </div>
    </div>
    <div class="section">
        <div class="card-grid">
            <div class="card"><h3>Day 1: Galera fundamentals</h3><p class="muted">Quorum, SST/IST, flow control.</p></div>
            <div class="card"><h3>Day 2: ProxySQL governance</h3><p class="muted">Routing rules, hostgroups, simulations.</p></div>
            <div class="card"><h3>Day 3: Migration lab</h3><p class="muted">Schema drift, routing cutovers, rollback plans.</p></div>
        </div>
    </div>
    <div class="section">
        <div class="section-title">
            <h2>What you get</h2>
        </div>
        <ul class="list-columns">
            <li>Hands-on labs for DBAs.</li>
            <li>Reusable runbooks and checklists.</li>
            <li>Operational confidence for production clusters.</li>
            <li>Workshops tailored to your fleet.</li>
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
