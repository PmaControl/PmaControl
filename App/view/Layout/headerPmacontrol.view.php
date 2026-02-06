<?php

use \Glial\I18n\I18n;

$language = I18n::Get();
$meta = $meta ?? array();
$metaTitle = $meta['title'] ?? strip_tags($GLIALE_TITLE);
$metaDescription = $meta['description'] ?? '';
$metaKeywords = $meta['keywords'] ?? '';
$metaSlug = $meta['slug'] ?? '';

echo "<!DOCTYPE html>\n";
echo "<html lang=\"".$language."\">";
?>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="Keywords" content="<?= htmlspecialchars($metaKeywords, ENT_QUOTES); ?>" />
    <meta name="Description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES); ?>" />
    <meta name="robots" content="index,follow,all" />
    <meta name="generator" content="GLIALE 1.1" />
    <title><?= htmlspecialchars($metaTitle, ENT_QUOTES); ?> - <?= SITE_NAME ?> <?= SITE_VERSION ?></title>
    <link rel="shortcut icon" href="favicon.ico" />
    <link rel="stylesheet" type="text/css" href="<?= CSS ?>bootstrap.css">
    <link rel="stylesheet" type="text/css" href="<?= CSS ?>font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="<?= CSS ?>pmacontrol-marketing.css">
</head>
<body data-theme="dark" data-lang="<?= htmlspecialchars($language, ENT_QUOTES); ?>">
    <header class="marketing-header">
        <div class="container">
            <a class="logo" href="<?= LINK ?>pmacontrol/index">
                <img src="<?= IMG ?>icon/pmacontrol_b.svg" alt="PmaControl" />
                <span>PmaControl</span>
                <small>Production-grade MySQL/MariaDB control plane</small>
            </a>
            <nav class="primary-nav">
                <div class="nav-group">
                    <span class="nav-title">Product</span>
                    <a href="<?= LINK ?>pmacontrol/product">Overview</a>
                    <a href="<?= LINK ?>pmacontrol/monitoring">Monitoring</a>
                    <a href="<?= LINK ?>pmacontrol/performance">Performance</a>
                    <a href="<?= LINK ?>pmacontrol/backups">Backups</a>
                    <a href="<?= LINK ?>pmacontrol/galera">Galera</a>
                    <a href="<?= LINK ?>pmacontrol/proxysql">ProxySQL</a>
                    <a href="<?= LINK ?>pmacontrol/schema">Schema Drift</a>
                    <a href="<?= LINK ?>pmacontrol/security">Security</a>
                    <a href="<?= LINK ?>pmacontrol/automation">Automation</a>
                    <a href="<?= LINK ?>pmacontrol/ai">AI features</a>
                </div>
                <div class="nav-group">
                    <span class="nav-title">Solutions</span>
                    <a href="<?= LINK ?>pmacontrol/solutions">Use-cases</a>
                    <a href="<?= LINK ?>pmacontrol/integrations">Integrations</a>
                    <a href="<?= LINK ?>pmacontrol/pricing">Pricing</a>
                </div>
                <div class="nav-group">
                    <span class="nav-title">Resources</span>
                    <a href="<?= LINK ?>pmacontrol/docs">Docs</a>
                    <a href="<?= LINK ?>pmacontrol/resources">Resources</a>
                    <a href="<?= LINK ?>pmacontrol/blog">Blog</a>
                    <a href="<?= LINK ?>pmacontrol/case_studies">Case studies</a>
                </div>
                <div class="nav-group">
                    <span class="nav-title">Company</span>
                    <a href="<?= LINK ?>pmacontrol/company">About</a>
                    <a href="<?= LINK ?>pmacontrol/roadmap">Roadmap</a>
                    <a href="<?= LINK ?>pmacontrol/security_page">Security</a>
                    <a href="<?= LINK ?>pmacontrol/contact">Contact</a>
                </div>
            </nav>
            <div class="header-actions">
                <button class="btn btn-ghost" data-action="toggle-theme">Dark / Light</button>
                <button class="btn btn-ghost" data-action="toggle-lang">EN / FR</button>
                <a class="btn btn-outline" href="<?= LINK ?>pmacontrol/pricing">Pricing</a>
                <a class="btn btn-primary" href="<?= LINK ?>pmacontrol/contact">Request a demo</a>
            </div>
        </div>
    </header>
    <section class="hero-banner">
        <div class="container">
            <div>
                <p class="breadcrumb"><?= htmlspecialchars($metaSlug, ENT_QUOTES); ?></p>
                <h1><?= $GLIALE_TITLE; ?></h1>
            </div>
            <div class="hero-actions">
                <a class="btn btn-primary" href="<?= LINK ?>pmacontrol/contact">Book a call</a>
                <a class="btn btn-outline" href="<?= LINK ?>pmacontrol/docs">See docs</a>
                <a class="btn btn-ghost" href="<?= LINK ?>pmacontrol/pricing">Download (self-hosted)</a>
            </div>
        </div>
    </section>
