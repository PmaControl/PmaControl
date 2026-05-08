---
title: Extend Metrics for Percona Monitoring and Management Without Modifying Code
source:
  name: Percona Blog
  url: https://www.percona.com/blog/extend-metrics-for-percona-monitoring-and-management-without-modifying-code/
  post_id: 19143
source_author:
  name: Vadim Yalovets
  slug: vadim-yalovets
  url: https://www.percona.com/blog/author/vadim-yalovets/
  website: ''
published_at: '2018-08-28T13:56:21'
published_at_gmt: '2018-08-28T13:56:21'
modified_at: '2026-03-26T20:17:41'
modified_at_gmt: '2026-03-26T20:17:41'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:pmm
categories:
- Insight for DBAs
- Insight for Developers
- MongoDB
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- insight-for-developers
- mongodb
- mysql
- percona-software
tags:
- database metrics
- Metrics Monitor
- MySQL metrics
- OS Metrics
tag_slugs:
- database-metrics
- metrics-monitor
- mysql-metrics
- os-metrics
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/pmm-extended-metrics.png
image_count: 3
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Extend Metrics for Percona Monitoring and Management Without Modifying Code

Source: [Percona Blog](https://www.percona.com/blog/extend-metrics-for-percona-monitoring-and-management-without-modifying-code/)

Auteur source: [Vadim Yalovets](https://www.percona.com/blog/author/vadim-yalovets/)

Publication: 2018-08-28T13:56:21

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona Monitoring and Management (PMM) provides an excellent solution for system monitoring. Sometimes, though, you’ll have the need for a metric that’s not present in the list of node_exporter metrics out of the box. In this post, we introduce a simple method and show how to extend the list of available metrics without modifying the node_exporter … Continued

## Structure detectee

- H2: Enable the textfile collector in pmm-client
- H2: Add a crontab task
- H2: Adding the crontab tasks by using a script
- H4: More resources you might enjoy

## Images et graphiques reperes

- featured / image: [Extend Metrics for Percona Monitoring and Management Without Modifying Code](https://www.percona.com/wp-content/uploads/2026/03/pmm-extended-metrics.png)
- content / graph_or_chart: [Look - we got a new metric!](https://www.percona.com/wp-content/uploads/2026/03/Screenshot_20180808_183353.png)
- content / image: [Modifying the cron job - a script](https://www.percona.com/wp-content/uploads/2026/03/Screenshot_20180808_145035.png)

## Auteur source

Software Engineer
