<?php
$meta = $meta ?? array();
?>
<div class="container">
    <div class="section">
        <div class="lang-en">
            <h2>Pricing</h2>
            <p class="muted">Choose SaaS (coming soon) or self-hosted licenses with support plans.</p>
            <p class="muted">Hero variants: "Plan your database operations with clear pricing." / "Self-hosted or SaaS, built for production." / "Pricing aligned with your fleet size."</p>
        </div>
        <div class="lang-fr">
            <h2>Tarification</h2>
            <p class="muted">Choisissez la SaaS (coming soon) ou le self-hosted avec plans de support.</p>
            <p class="muted">Variantes hero : "Une tarification claire pour vos opérations DB." / "Self-hosted ou SaaS, prêt pour la prod." / "Des plans adaptés à la taille de votre flotte."</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">
            <h2>SaaS tiers (coming soon)</h2>
        </div>
        <div class="card-grid">
            <div class="card"><h3>Starter</h3><p class="muted">Up to 10 nodes, core monitoring, community support.</p></div>
            <div class="card"><h3>Pro</h3><p class="muted">Up to 100 nodes, performance toolkit, Slack alerts, SSO.</p></div>
            <div class="card"><h3>Enterprise</h3><p class="muted">Unlimited nodes, advanced automation, dedicated support.</p></div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">
            <h2>Self-hosted / On-prem licenses</h2>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Plan</th>
                    <th>Community</th>
                    <th>Pro</th>
                    <th>Enterprise</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Nodes</td>
                    <td>Up to 5</td>
                    <td>Up to 100</td>
                    <td>Unlimited</td>
                </tr>
                <tr>
                    <td>Monitoring & dashboards</td>
                    <td>Core</td>
                    <td>Advanced</td>
                    <td>Advanced + custom</td>
                </tr>
                <tr>
                    <td>Performance toolkit</td>
                    <td>Limited</td>
                    <td>Full</td>
                    <td>Full + AI options</td>
                </tr>
                <tr>
                    <td>Automation & runbooks</td>
                    <td>Basic</td>
                    <td>Standard</td>
                    <td>Premium</td>
                </tr>
                <tr>
                    <td>Support</td>
                    <td>Community</td>
                    <td>Business hours</td>
                    <td>24/7 & dedicated</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">
            <h2>Pricing FAQ</h2>
        </div>
        <div class="card">
            <ol>
                <li>How is pricing calculated? By number of database nodes and features.</li>
                <li>Can I start with Community and upgrade later? Yes, licenses are upgradeable.</li>
                <li>Is SaaS available today? Not yet, join the waitlist.</li>
                <li>Do you offer enterprise trials? Yes, contact us for a guided trial.</li>
                <li>Is support included? Pro and Enterprise include support plans.</li>
                <li>Do you have discounts for nonprofits? Contact us.</li>
                <li>Is there an air-gapped option? Yes, on-prem supports air-gapped deployments.</li>
                <li>Can we use our own SSO? Yes, Pro/Enterprise support OIDC/SAML.</li>
                <li>What about multi-region? Supported in Enterprise plans.</li>
                <li>Do you provide migration assistance? Yes, via professional services.</li>
            </ol>
        </div>
    </div>

    <div class="section">
        <div class="section-title">
            <h2>What you get</h2>
        </div>
        <ul class="list-columns">
            <li>Predictable pricing based on fleet size.</li>
            <li>Support aligned with criticality.</li>
            <li>Self-hosted control with optional SaaS migration.</li>
            <li>Clear feature tiers for planning.</li>
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
