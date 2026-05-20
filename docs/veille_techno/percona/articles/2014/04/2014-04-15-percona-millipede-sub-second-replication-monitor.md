---
title: percona-millipede – Sub-second replication monitor
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-millipede-sub-second-replication-monitor/
  post_id: 7948
source_author:
  name: Mike Benshoof
  slug: mbenshoof
  url: https://www.percona.com/blog/author/mbenshoof/
  website: ''
published_at: '2014-04-15T13:00:25'
published_at_gmt: '2014-04-15T13:00:25'
modified_at: '2026-03-25T17:25:59'
modified_at_gmt: '2026-03-25T17:25:59'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
categories:
- MySQL
category_slugs:
- mysql
tags:
- percona-millipede
- Sub-second replication monitor
- Vimeo
tag_slugs:
- percona-millipede
- sub-second-replication-monitor
- vimeo
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Async-Monitor.png
image_count: 3
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# percona-millipede – Sub-second replication monitor

Source: [Percona Blog](https://www.percona.com/blog/percona-millipede-sub-second-replication-monitor/)

Auteur source: [Mike Benshoof](https://www.percona.com/blog/author/mbenshoof/)

Publication: 2014-04-15T13:00:25

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I recently helped a client implement a custom replication delay monitor and wanted to share the experience and discuss some of the iterations and decisions that were made. percona-millipede was developed in conjunction with Vimeo with the following high-level goal in mind: implement a millisecond level replication delay monitor and graph the results. Please visit … Continued

## Structure detectee

- H2: pt-heartbeat
- H2: First Iteration – Async update/monitor
- H2: Final Iteration – ZeroMQ update/monitor

## Images et graphiques reperes

- featured / image: [percona-millipede – Sub-second replication monitor](https://www.percona.com/wp-content/uploads/2026/03/Async-Monitor.png)
- content / image: [zeromq-Monitor](https://www.percona.com/wp-content/uploads/2026/03/zeromq-Monitor.png)
- content / graph_or_chart: [vimeo-delay-graph](https://www.percona.com/wp-content/uploads/2026/03/vimeo-delay-graph.png)

## Auteur source

Michael joined Percona in 2012 as a US based consultant and is currently a Technical Account Manager. Prior to joining Percona, Michael spent several years in a DevOps role maintaining a SaaS application specializing in social networking. His experiences include application development and scaling, systems administration, along with database administration and design. He enjoys designing extensible and flexible solutions to problems. When not working, he enjoys time outdoors, grilling, most sports, and spending time with the family.
