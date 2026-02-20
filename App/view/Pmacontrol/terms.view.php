<?php
$meta = $meta ?? array();
?>
<div class="container">
    <div class="section">
        <h2>Terms of service</h2>
        <p class="muted">Usage terms for PmaControl software and services.</p>
        <p class="muted">Hero variants: "Clear terms for production teams." / "No surprises in licensing." / "Transparent service commitments."</p>
    </div>
    <div class="section">
        <div class="card">
            <ul>
                <li>License scope defined by plan and node count.</li>
                <li>Support SLAs defined in contract.</li>
                <li>Customer responsible for backups and data access.</li>
                <li>Security updates provided per release cycle.</li>
            </ul>
        </div>
    </div>
    <div class="section">
        <div class="section-title">
            <h2>What you get</h2>
        </div>
        <ul class="list-columns">
            <li>Clear license boundaries.</li>
            <li>Defined responsibilities.</li>
            <li>Transparent service commitments.</li>
            <li>Predictable upgrade cadence.</li>
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
