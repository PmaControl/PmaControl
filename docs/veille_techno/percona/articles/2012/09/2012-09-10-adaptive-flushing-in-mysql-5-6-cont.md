---
title: Adaptive flushing in MySQL 5.6 – cont
source:
  name: Percona Blog
  url: https://www.percona.com/blog/adaptive-flushing-in-mysql-5-6-cont/
  post_id: 3773
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2012-09-10T18:05:23'
published_at_gmt: '2012-09-10T18:05:23'
modified_at: '2026-03-23T22:26:56'
modified_at_gmt: '2026-03-23T22:26:56'
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
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/res_10sec.png
image_count: 6
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Adaptive flushing in MySQL 5.6 – cont

Source: [Percona Blog](https://www.percona.com/blog/adaptive-flushing-in-mysql-5-6-cont/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2012-09-10T18:05:23

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This is to continue my previous experiments on adaptive flushing in MySQL 5.6.6. Now I am running Ubuntu 12.04, which seems to provide a better throughput than previous system (CentOS 6.3), it also changes the profile of results. So, as previous I run tpcc-mysql 2500W, against MySQL 5.6.6 with innodb_buffer_pool_size 150GB, and now I vary … Continued

## Images et graphiques reperes

- featured / image: [Adaptive flushing in MySQL 5.6 – cont](https://www.percona.com/wp-content/uploads/2026/03/res_10sec.png)
- content / image: [res_10sec1.png](https://www.percona.com/wp-content/uploads/2026/03/res_10sec1.png)
- content / image: [res_60sec.png](https://www.percona.com/wp-content/uploads/2026/03/res_60sec.png)
- content / image: [res_1sec_2.png](https://www.percona.com/wp-content/uploads/2026/03/res_1sec_2.png)
- content / image: [res_1sec_zoom.png](https://www.percona.com/wp-content/uploads/2026/03/res_1sec_zoom.png)
- content / image: [ckpt.png](https://www.percona.com/wp-content/uploads/2026/03/ckpt.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
