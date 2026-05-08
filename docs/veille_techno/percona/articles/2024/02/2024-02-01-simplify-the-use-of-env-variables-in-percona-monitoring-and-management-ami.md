---
title: Simplify the Use of ENV Variables in Percona Monitoring and Management AMI
source:
  name: Percona Blog
  url: https://www.percona.com/blog/simplify-the-use-of-env-variables-in-percona-monitoring-and-management-ami/
  post_id: 28025
source_author:
  name: Nurlan Moldomurov
  slug: nurlan-moldomurov
  url: https://www.percona.com/blog/author/nurlan-moldomurov/
  website: ''
published_at: '2024-02-01T18:26:41'
published_at_gmt: '2024-02-01T18:26:41'
modified_at: '2026-05-05T20:06:38'
modified_at_gmt: '2026-05-05T20:06:38'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- category:monitoring:2104
- search:percona-monitoring-and-management
- search:pmm
- tag:percona-monitoring-and-management:2166
- tag:pmm:2167
categories:
- Monitoring
- Percona Software
category_slugs:
- monitoring
- percona-software
tags:
- AWS
- AWS AMI
- Percona Monitoring and Management
- PMM
tag_slugs:
- aws
- aws-ami
- percona-monitoring-and-management
- pmm
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/ENV-Variables-in-PMM-AMI-1.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Simplify the Use of ENV Variables in Percona Monitoring and Management AMI

Source: [Percona Blog](https://www.percona.com/blog/simplify-the-use-of-env-variables-in-percona-monitoring-and-management-ami/)

Auteur source: [Nurlan Moldomurov](https://www.percona.com/blog/author/nurlan-moldomurov/)

Publication: 2024-02-01T18:26:41

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The Percona Monitoring and Management (PMM) Amazon Machine Image (AMI) currently lacks native support for ENV variables. In this guide, we’ll walk through a straightforward workaround that simplifies the process of using ENV variables in PMM AMI and reapplying them after an upgrade. Step one: Adding ENV variables to /srv/.env Begin by consolidating your ENV … Continued

## Structure detectee

- H3: Step one: Adding ENV variables to /srv/.env
- H3: Step two: Using systemctl edit for systemd service
- H3: Step three: Restarting the supervisord service

## Images et graphiques reperes

- featured / image: [Simplify the Use of ENV Variables in Percona Monitoring and Management AMI](https://www.percona.com/wp-content/uploads/2026/03/ENV-Variables-in-PMM-AMI-1.jpg)
