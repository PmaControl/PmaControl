<?php
$meta = $meta ?? array();
?>
<div class="container">
    <div class="section">
        <div class="lang-en">
            <h2>Contact & request a demo</h2>
            <p class="muted">Tell us about your database fleet and goals. We'll respond within 1 business day.</p>
            <p class="muted">Hero variants: "Talk to a production DBA." / "Book a demo tailored to your fleet." / "Get a plan for safer operations."</p>
        </div>
        <div class="lang-fr">
            <h2>Contact & demande de démo</h2>
            <p class="muted">Parlez-nous de votre flotte et de vos objectifs. Réponse sous 1 jour ouvré.</p>
            <p class="muted">Variantes hero : "Parler à un DBA production." / "Réserver une démo adaptée à votre flotte." / "Obtenir un plan pour des opérations sûres."</p>
        </div>
    </div>

    <div class="section">
        <div class="card-grid">
            <div class="card">
                <h3>Request demo form</h3>
                <ul>
                    <li>Name / Prénom</li>
                    <li>Email (work)</li>
                    <li>Company</li>
                    <li>Role (CTO/DBA/SRE/DevOps)</li>
                    <li>Fleet size (# nodes)</li>
                    <li>Stack (MySQL/MariaDB/ProxySQL/Galera)</li>
                    <li>Goals (MTTR, migration, performance, security)</li>
                    <li>Preferred schedule</li>
                </ul>
                <p class="muted">Validation: required fields, email format, consent checkbox.</p>
            </div>
            <div class="card">
                <h3>Contact form</h3>
                <ul>
                    <li>Name</li>
                    <li>Email</li>
                    <li>Message</li>
                    <li>Consent to be contacted</li>
                </ul>
                <p class="muted">Success message: "Thanks, we'll be in touch shortly."</p>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">
            <h2>What you get</h2>
        </div>
        <ul class="list-columns">
            <li>Fast response from a DBA expert.</li>
            <li>Tailored demo based on your fleet.</li>
            <li>Clear next steps and planning.</li>
            <li>Access to onboarding checklists.</li>
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
