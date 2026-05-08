---
title: 'InnoDB Buffer Pool Resizing: Chunk Change'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/chunk-change-innodb-buffer-pool-resizing/
  post_id: 18510
source_author:
  name: David Ducos
  slug: david-ducos
  url: https://www.percona.com/blog/author/david-ducos/
  website: ''
published_at: '2018-06-19T18:02:33'
published_at_gmt: '2018-06-19T18:02:33'
modified_at: '2026-05-05T19:12:07'
modified_at_gmt: '2026-05-05T19:12:07'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- buffer pool
- InnoDB buffer pool
- InnoDB buffer pool size
- InnoDB Performance
- memory
- Memory Usage
- MySQL Performance
tag_slugs:
- buffer-pool
- innodb-buffer-pool
- innodb-buffer-pool-size
- innodb-performance
- memory
- memory-usage
- mysql-performance
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/buffer-pool-chunk-size.jpg
image_count: 3
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# InnoDB Buffer Pool Resizing: Chunk Change

Source: [Percona Blog](https://www.percona.com/blog/chunk-change-innodb-buffer-pool-resizing/)

Auteur source: [David Ducos](https://www.percona.com/blog/author/david-ducos/)

Publication: 2018-06-19T18:02:33

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Since MySQL 5.7.5, we have been able to resize dynamically the InnoDB Buffer Pool. This new feature also introduced a new variable — innodb_buffer_pool_chunk_size — which defines the chunk size by which the buffer pool is enlarged or reduced. This variable is not dynamic and if it is incorrectly configured, could lead to undesired situations. … Continued

## Structure detectee

- H2: Reducing the buffer pool
- H2: Increasing the buffer pool
- H2: Interesting scenarios
- H3: Increasing size in the config file
- H2: Increasing instances and chunk size
- H2: What is the best setting?
- H3: You May Also Like

## Images et graphiques reperes

- featured / image: [InnoDB Buffer Pool Resizing: Chunk Change](https://www.percona.com/wp-content/uploads/2026/03/buffer-pool-chunk-size.jpg)
- content / graph_or_chart: [InnoDB Buffer Pool](https://www.percona.com/wp-content/uploads/2026/03/Untitled-Diagram-2.jpg)
- content / image: [bp8instances.png](https://www.percona.com/wp-content/uploads/2026/03/bp8instances.png)

## Auteur source

David studied Computer Science in National University of La Plata and has worked as a DBA consultant since 2008. For the past 3 years he worked with a worldwide platform of free classifieds up until he joined Percona's consulting team in November 2014. David lives near Buenos Aires, Argentina and in his free time loves to spend time with his family.
