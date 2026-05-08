---
title: 'TokuDB v6.0: Getting Rid of Slave Lag'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/tokudb-v6-0-getting-rid-of-slave-lag/
  post_id: 9652
source_author:
  name: Martin.FarachColton
  slug: martin-farachcolton
  url: https://www.percona.com/blog/author/martin-farachcolton/
  website: ''
published_at: '2012-04-10T15:04:48'
published_at_gmt: '2012-04-10T15:04:48'
modified_at: '2026-03-25T18:21:28'
modified_at_gmt: '2026-03-25T18:21:28'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
category_slugs:
- mysql
tags:
- Announcement
- Big Data
- Fractal Tree™ indexes
- MySQL
- NewSQL
- percona live
- slave lag
- Storage Engine
- TokuDB
- Tokutek
tag_slugs:
- announcement
- big-data
- fractal-tree-indexes
- mysql
- newsql
- percona-live
- slave-lag
- storage-engine
- tokudb
- tokutek
featured_image_url: https://www.percona.com/blog/wp-content/uploads/2012/04/Slave-Lag-Benchmark.png
image_count: 1
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# TokuDB v6.0: Getting Rid of Slave Lag

Source: [Percona Blog](https://www.percona.com/blog/tokudb-v6-0-getting-rid-of-slave-lag/)

Auteur source: [Martin.FarachColton](https://www.percona.com/blog/author/martin-farachcolton/)

Publication: 2012-04-10T15:04:48

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Master/slave replication is an important tool that gets used in many ways: distributing read loads among many slaves for performance, using a slave for backups so the master can handle live load, geographically distributed disaster recovery, etc. The Achilles’ Heal of slave performance is that slave workloads are single-threaded. The master can have many clients … Continued

## Images et graphiques reperes

- content / graph_or_chart: [Slave Complete Time](https://www.percona.com/blog/wp-content/uploads/2012/04/Slave-Lag-Benchmark.png)
