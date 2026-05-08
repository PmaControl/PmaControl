---
title: InnoDB, InnoDB-plugin vs XtraDB on fast storage
source:
  name: Percona Blog
  url: https://www.percona.com/blog/innodb-innodb-plugin-vs-xtradb-on-fast-storage/
  post_id: 2182
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2010-01-13T08:52:38'
published_at_gmt: '2010-01-13T08:52:38'
modified_at: '2026-03-23T21:37:47'
modified_at_gmt: '2026-03-23T21:37:47'
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
- InnoDB
- XtraDB
tag_slugs:
- innodb
- xtradb
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/innodb_logs_on_fusionio.png
image_count: 6
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# InnoDB, InnoDB-plugin vs XtraDB on fast storage

Source: [Percona Blog](https://www.percona.com/blog/innodb-innodb-plugin-vs-xtradb-on-fast-storage/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2010-01-13T08:52:38

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

To continue fun with FusionIO cards, I wanted to check how MySQL / InnoDB performs here. For benchmark I took MySQL 5.1.42 with built-in InnoDB, InnoDB-plugin 1.0.6, and XtraDB 1.0.6-9 ( InnoDB with Percona patches).As benchmark engine I used tpcc-mysql with 1000 warehouses ( which gives around 90GB of data + indexes) on my workhourse … Continued

## Images et graphiques reperes

- featured / image: [InnoDB, InnoDB-plugin vs XtraDB on fast storage](https://www.percona.com/wp-content/uploads/2026/03/innodb_logs_on_fusionio.png)
- content / image: [innodb_vs_plugin](https://www.percona.com/wp-content/uploads/2026/03/innodb_vs_plugin.png)
- content / image: [innodb_vs_xtradb](https://www.percona.com/wp-content/uploads/2026/03/innodb_vs_xtradb.png)
- content / image: [disk_bo](https://www.percona.com/wp-content/uploads/2026/03/disk_bo.png)
- content / image: [cpu_usage](https://www.percona.com/wp-content/uploads/2026/03/cpu_usage.png)
- content / image: [xtradb_fusionio_vs_raid10](https://www.percona.com/wp-content/uploads/2026/03/xtradb_fusionio_vs_raid10.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
