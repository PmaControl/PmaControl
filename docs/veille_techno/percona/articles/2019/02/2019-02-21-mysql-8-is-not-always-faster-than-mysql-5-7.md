---
title: MySQL 8 is not always faster than MySQL 5.7
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-8-is-not-always-faster-than-mysql-5-7/
  post_id: 20043
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2019-02-21T18:10:14'
published_at_gmt: '2019-02-21T18:10:14'
modified_at: '2026-05-05T16:19:41'
modified_at_gmt: '2026-05-05T16:19:41'
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
- MySQL
category_slugs:
- benchmarks
- mysql
tags:
- database performance
tag_slugs:
- database-performance
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/mysql-8-slower-than-mysql-5.7-tps.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL 8 is not always faster than MySQL 5.7

Source: [Percona Blog](https://www.percona.com/blog/mysql-8-is-not-always-faster-than-mysql-5-7/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2019-02-21T18:10:14

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MySQL 8.0.15 performs worse in sysbench oltp_read_write than MySQL 5.7.25 Initially I was testing group replication performance and was puzzled why MySQL 8.0.15 performs consistently worse than MySQL 5.7.25. It appears that a single server instance is affected by a performance degradation. My testing setup Hardware details: Bare metal server provided by packet.net, instance size: … Continued

## Structure detectee

- H4: My testing setup
- H4: Benchmark
- H4: Summary: MySQL 8.0.15 is persistently worse than MySQL 5.7.25.
- H3: Update:
- H3: Appendix:

## Images et graphiques reperes

- featured / image: [MySQL 8 is not always faster than MySQL 5.7](https://www.percona.com/wp-content/uploads/2026/03/mysql-8-slower-than-mysql-5.7-tps.jpg)
- content / image: [mysql 8 slower than mysql 5.7 sysbench](https://www.percona.com/wp-content/uploads/2026/03/mysql-8-slower-than-mysql-5.7-tps-300x169.jpg)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
