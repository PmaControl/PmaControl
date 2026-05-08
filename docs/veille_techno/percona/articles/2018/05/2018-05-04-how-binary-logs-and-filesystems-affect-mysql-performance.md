---
title: How Binary Logs (and Filesystems) Affect MySQL Performance
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-binary-logs-and-filesystems-affect-mysql-performance/
  post_id: 18707
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2018-05-04T22:50:55'
published_at_gmt: '2018-05-04T22:50:55'
modified_at: '2026-05-06T00:05:13'
modified_at_gmt: '2026-05-06T00:05:13'
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
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Binary-Log-Performance-small.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How Binary Logs (and Filesystems) Affect MySQL Performance

Source: [Percona Blog](https://www.percona.com/blog/how-binary-logs-and-filesystems-affect-mysql-performance/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2018-05-04T22:50:55

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I want to take a closer look at MySQL performance with binary logs enabled on different filesystems, especially as MySQL 8.0 comes with binary logs enabled by default. As part of my benchmarks of the MyRocks storage engine, I’ve noticed an unusual variance in throughput for the InnoDB storage engine, even though we spent a … Continued

## Structure detectee

- H2: Benchmark Setup
- H2: Initial Results
- H2: The Results
- H2: Filesystems
- H3: Hardware Spec
- H3: Extra R aw Results, Scripts and Config

## Images et graphiques reperes

- featured / image: [How Binary Logs (and Filesystems) Affect MySQL Performance](https://www.percona.com/wp-content/uploads/2026/03/Binary-Log-Performance-small.png)
- content / image: [Binary Log Performance](https://www.percona.com/wp-content/uploads/2026/03/Binary-Log-Performance-300x209.png)
- content / image: [Binary Log Performance 1](https://www.percona.com/wp-content/uploads/2026/03/Binary-Log-Performance-1-300x210.png)
- content / image: [Binary Log Performance 2](https://www.percona.com/wp-content/uploads/2026/03/Binary-Log-Performance-2-300x211.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
