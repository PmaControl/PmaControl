---
title: How Binary Logs Affect MySQL 8.0 Performance
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-binary-logs-affect-mysql-8-0-performance/
  post_id: 18720
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2018-05-04T22:56:11'
published_at_gmt: '2018-05-04T22:56:11'
modified_at: '2026-05-06T00:05:43'
modified_at_gmt: '2026-05-06T00:05:43'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Benchmarks
- Insight for DBAs
- MySQL
category_slugs:
- benchmarks
- insight-for-dbas
- mysql
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.0-Performance-Small.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How Binary Logs Affect MySQL 8.0 Performance

Source: [Percona Blog](https://www.percona.com/blog/how-binary-logs-affect-mysql-8-0-performance/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2018-05-04T22:56:11

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

As part of my benchmarks of binary logs, I’ve decided to check how the recently released MySQL 8.0 performance is affected in similar scenarios, especially as binary logs are enabled by default. It is also interesting to check how MySQL 8.0 performs against the claimed performance improvements in redo logs subsystem. I will use a … Continued

## Structure detectee

- H2: Servers Comparison
- H2: Binary Log Effect
- H2: Conclusions
- H3: Hardware spec
- H3: Extra Raw Results, Scripts and Config

## Images et graphiques reperes

- featured / image: [How Binary Logs Affect MySQL 8.0 Performance](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.0-Performance-Small.png)
- content / image: [MySQL 8.0 Performance](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.0-Performance-300x213.png)
- content / image: [MySQL 8.0 Performance 2](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.0-Performance-2.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
