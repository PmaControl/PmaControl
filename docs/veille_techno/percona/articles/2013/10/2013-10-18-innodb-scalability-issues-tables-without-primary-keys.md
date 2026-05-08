---
title: InnoDB scalability issues due to tables without primary keys
source:
  name: Percona Blog
  url: https://www.percona.com/blog/innodb-scalability-issues-tables-without-primary-keys/
  post_id: 7458
source_author:
  name: Ovais Tariq
  slug: ovaistariq
  url: https://www.percona.com/blog/author/ovaistariq/
  website: http://www.percona.com/blog/
published_at: '2013-10-18T05:00:48'
published_at_gmt: '2013-10-18T05:00:48'
modified_at: '2026-05-04T20:55:04'
modified_at_gmt: '2026-05-04T20:55:04'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- contention
- data dictionary
- dict_sys
- InnoDB
- insert performance
- Locking
- mutex
- primary key
- row-id
tag_slugs:
- contention
- data-dictionary
- dict_sys
- innodb
- insert-performance
- locking
- mutex
- primary-key
- row-id
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/writes_per_second-64_threads.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# InnoDB scalability issues due to tables without primary keys

Source: [Percona Blog](https://www.percona.com/blog/innodb-scalability-issues-tables-without-primary-keys/)

Auteur source: [Ovais Tariq](https://www.percona.com/blog/author/ovaistariq/)

Publication: 2013-10-18T05:00:48

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Each day there is probably work done to improve performance of the InnoDB storage engine and remove bottlenecks and scalability issues. Hence there was another one I wanted to highlight: Scalability issues due to tables without primary keys This scalability issue is caused by the usage of tables without primary keys. This issue typically shows … Continued

## Structure detectee

- H3: Scalability issues due to tables without primary keys
- H3: Benchmarking affects of non-presence of primary keys
- H4: Hardware
- H4: MySQL
- H4: Single-row INSERTs
- H4: Bulk Loads
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [InnoDB scalability issues due to tables without primary keys](https://www.percona.com/wp-content/uploads/2026/03/writes_per_second-64_threads.png)
- content / image: [Writes per second 16 threads](https://www.percona.com/wp-content/uploads/2026/03/writes_per_second-16_threads.png)
- content / image: [Writes per second 32 threads](https://www.percona.com/wp-content/uploads/2026/03/writes_per_second-32_threads.png)
- content / image: [Parallel Bulk Loading of Tables](https://www.percona.com/wp-content/uploads/2026/03/parallel_bulk_loading_of_tables.png)
