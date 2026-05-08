---
title: Drop table performance
source:
  name: Percona Blog
  url: https://www.percona.com/blog/drop-table-performance/
  post_id: 2818
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2011-04-21T03:49:50'
published_at_gmt: '2011-04-21T03:49:50'
modified_at: '2026-04-28T21:26:09'
modified_at_gmt: '2026-04-28T21:26:09'
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
- Percona Software
category_slugs:
- benchmarks
- insight-for-dbas
- insight-for-developers
- mysql
- percona-software
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/drop_table.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Drop table performance

Source: [Percona Blog](https://www.percona.com/blog/drop-table-performance/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2011-04-21T03:49:50

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

There have been recent discussions about DROP TABLE performance in InnoDB. (You can refer to Peter’s post https://www.percona.com/blog/2011/02/03/performance-problem-with-innodb-and-drop-table/ and these bug reports: http://bugs.mysql.com/bug.php?id=51325 and http://bugs.mysql.com/bug.php?id=56332.) It may not sound that serious, but if your workload often uses DROP TABLE and you have a big buffer pool, it may be a significant issue. This can get … Continued

## Images et graphiques reperes

- featured / image: [Drop table performance](https://www.percona.com/wp-content/uploads/2026/03/drop_table.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
