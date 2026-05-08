---
title: MySQL 5.5.4 in tpcc-like workload
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-5-5-4-in-tpcc-like-workload/
  post_id: 2287
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2010-04-21T20:12:45'
published_at_gmt: '2010-04-21T20:12:45'
modified_at: '2026-03-23T21:40:31'
modified_at_gmt: '2026-03-23T21:40:31'
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
- FusionIO
- InnoDB
- XtraDB
tag_slugs:
- fusionio
- innodb
- xtradb
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/5.5.4-bp-vary.png
image_count: 4
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL 5.5.4 in tpcc-like workload

Source: [Percona Blog](https://www.percona.com/blog/mysql-5-5-4-in-tpcc-like-workload/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2010-04-21T20:12:45

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MySQL-5.5.4 ® is the great release with performance improvements, let’s see how it performs in tpcc-like workload. The full details are on Wiki pagehttps://www.percona.com/docs/wiki/benchmark:mysql:554-tpcc:start I took MySQL-5.5.4 with InnoDB-1.1, tpcc-mysql benchmark with 200W ( about 18GB worth of data),InnoDB log files are 3.8GB size, and run with different buffer pools from 20GB to 6GB. The … Continued

## Images et graphiques reperes

- featured / image: [MySQL 5.5.4 in tpcc-like workload](https://www.percona.com/wp-content/uploads/2026/03/5.5.4-bp-vary.png)
- content / image: [percona-server-bp-vary.png](https://www.percona.com/wp-content/uploads/2026/03/percona-server-bp-vary.png)
- content / image: [percona-vs-mysql.png](https://www.percona.com/wp-content/uploads/2026/03/percona-vs-mysql.png)
- content / graph_or_chart: [Buffer_pool_24GB](https://www.percona.com/docs/wiki/_media/benchmark:mysql:554-tpcc:5_5_4_200w_24gb_bp.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
