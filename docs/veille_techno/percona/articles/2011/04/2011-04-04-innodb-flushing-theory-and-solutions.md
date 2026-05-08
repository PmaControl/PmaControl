---
title: 'InnoDB Flushing: Theory and Solutions'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/innodb-flushing-theory-and-solutions/
  post_id: 2782
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2011-04-04T15:17:20'
published_at_gmt: '2011-04-04T15:17:20'
modified_at: '2026-03-23T21:55:55'
modified_at_gmt: '2026-03-23T21:55:55'
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
- Insight for Developers
- MySQL
category_slugs:
- benchmarks
- insight-for-dbas
- insight-for-developers
- mysql
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/line_log_1.png
image_count: 5
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# InnoDB Flushing: Theory and Solutions

Source: [Percona Blog](https://www.percona.com/blog/innodb-flushing-theory-and-solutions/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2011-04-04T15:17:20

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I mentioned problems with InnoDB flushing in a previous post. Before getting to ideas on a solution, let’s define some terms and take a look into theory. The two most important parameters for InnoDB performance are innodb_buffer_pool_size and innodb_log_file_size. InnoDB works with data in memory, and all changes to data are performed in memory. In … Continued

## Structure detectee

- H2: Log Size, Checkpoints, and Flushing
- H2: Using a Target Checkpoint Age

## Images et graphiques reperes

- featured / image: [InnoDB Flushing: Theory and Solutions](https://www.percona.com/wp-content/uploads/2026/03/line_log_1.png)
- content / image: [line_log_2.png](https://www.percona.com/wp-content/uploads/2026/03/line_log_2.png)
- content / image: [line_log_3.png](https://www.percona.com/wp-content/uploads/2026/03/line_log_3.png)
- content / image: [line_log_4.png](https://www.percona.com/wp-content/uploads/2026/03/line_log_4.png)
- content / image: [stable_flush.png](https://www.percona.com/wp-content/uploads/2026/03/stable_flush.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
