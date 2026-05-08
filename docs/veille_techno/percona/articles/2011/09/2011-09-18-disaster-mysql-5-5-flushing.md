---
title: 'Disaster: MySQL 5.5 Flushing'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/disaster-mysql-5-5-flushing/
  post_id: 3051
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2011-09-18T17:52:34'
published_at_gmt: '2011-09-18T17:52:34'
modified_at: '2026-03-23T22:05:29'
modified_at_gmt: '2026-03-23T22:05:29'
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
- Hardware and Storage
- MySQL
category_slugs:
- benchmarks
- hardware-and-storage
- mysql
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/init.sysbench1.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Disaster: MySQL 5.5 Flushing

Source: [Percona Blog](https://www.percona.com/blog/disaster-mysql-5-5-flushing/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2011-09-18T17:52:34

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

We raised topic of problems with flushing in InnoDB several times, some links: InnoDB Flushing theory and solutionsMySQL 5.5.8 in search of stability This was not often recurring problem so far, however in my recent experiments, I observe it in very simple sysbench workload on hardware which can be considered as typical nowadays.

## Images et graphiques reperes

- featured / image: [Disaster: MySQL 5.5 Flushing](https://www.percona.com/wp-content/uploads/2026/03/init.sysbench1.png)
- content / image: [init.sysbench.pct303.png](https://www.percona.com/wp-content/uploads/2026/03/init.sysbench.pct303.png)
- content / image: [init.sysbench.log1283.png](https://www.percona.com/wp-content/uploads/2026/03/init.sysbench.log1283.png)
- content / image: [init.sysbench.bp391.png](https://www.percona.com/wp-content/uploads/2026/03/init.sysbench.bp391.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
