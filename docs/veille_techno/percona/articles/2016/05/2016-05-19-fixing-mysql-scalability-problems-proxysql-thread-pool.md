---
title: Fixing MySQL scalability problems with ProxySQL or thread pool
source:
  name: Percona Blog
  url: https://www.percona.com/blog/fixing-mysql-scalability-problems-proxysql-thread-pool/
  post_id: 15170
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2016-05-19T20:58:35'
published_at_gmt: '2016-05-19T20:58:35'
modified_at: '2026-05-05T23:42:56'
modified_at_gmt: '2026-05-05T23:42:56'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- ProxySQL
matched_filters:
- category:mysql:83
- search:proxysql
categories:
- MySQL
category_slugs:
- mysql
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Fixing-MySQL-scalability-problems.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Fixing MySQL scalability problems with ProxySQL or thread pool

Source: [Percona Blog](https://www.percona.com/blog/fixing-mysql-scalability-problems-proxysql-thread-pool/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2016-05-19T20:58:35

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll discuss fixing MySQL scalability problems using either ProxySQL or thread pool. In the previous post I showed that even MySQL 5.7 in read-write workloads is not able to maintain throughput. Oracle’s recommendation to play black magic with innodb_thread_concurrency and innodb_spin_wait_delay doesn’t always help. We need a different solution to deal … Continued

## Images et graphiques reperes

- featured / image: [Fixing MySQL scalability problems with ProxySQL or thread pool](https://www.percona.com/wp-content/uploads/2026/03/Fixing-MySQL-scalability-problems.png)
- content / image: [Fixing MySQL scalability problems](https://www.percona.com/wp-content/uploads/2026/03/proxysql-1-2-scaled.png)
- content / image: [proxysqllat-1-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/proxysqllat-1-scaled.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
