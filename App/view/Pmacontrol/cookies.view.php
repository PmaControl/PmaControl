<?php
$meta = $meta ?? array();
?>
<div class="container">
    <div class="section">
        <h2>Cookie policy</h2>
        <p class="muted">We use minimal cookies for language and theme preferences. Optional analytics can be enabled.</p>
        <p class="muted">Hero variants: "Minimal cookies, maximum transparency." / "Your preferences, respected." / "No tracking by default."</p>
    </div>
    <div class="section">
        <div class="card">
            <ul>
                <li>Functional cookies: theme, language, session.</li>
                <li>Analytics cookies: opt-in only.</li>
                <li>You can clear cookies in your browser settings.</li>
            </ul>
        </div>
    </div>
    <div class="section">
        <div class="section-title">
            <h2>What you get</h2>
        </div>
        <ul class="list-columns">
            <li>Transparency on cookie usage.</li>
            <li>Minimal tracking by default.</li>
            <li>Respect for user choices.</li>
            <li>Simple opt-in for analytics.</li>
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
