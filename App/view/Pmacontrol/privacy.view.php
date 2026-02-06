<?php
$meta = $meta ?? array();
?>
<div class="container">
    <div class="section">
        <h2>Privacy policy</h2>
        <p class="muted">We collect only what is needed for demos, support, and service improvements. No data is sold. On-prem deployments keep data under your control.</p>
        <p class="muted">Hero variants: "Privacy-first operations." / "Your data stays yours." / "Transparent data handling."</p>
    </div>
    <div class="section">
        <div class="card">
            <ul>
                <li>Contact data: name, email, company.</li>
                <li>Usage data: optional anonymized analytics.</li>
                <li>Data retention: 24 months unless requested otherwise.</li>
                <li>Rights: access, rectification, deletion upon request.</li>
            </ul>
        </div>
    </div>
    <div class="section">
        <div class="section-title">
            <h2>What you get</h2>
        </div>
        <ul class="list-columns">
            <li>Transparent data handling.</li>
            <li>Privacy-first approach.</li>
            <li>Clear rights management.</li>
            <li>On-prem data ownership.</li>
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
