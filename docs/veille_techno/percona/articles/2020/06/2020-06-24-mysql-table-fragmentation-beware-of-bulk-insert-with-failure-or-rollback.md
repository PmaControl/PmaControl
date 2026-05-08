---
title: 'MySQL Table Fragmentation: Beware of Bulk INSERT with FAILURE or ROLLBACK'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-table-fragmentation-beware-of-bulk-insert-with-failure-or-rollback/
  post_id: 22562
source_author:
  name: Sri Sakthivel
  slug: sri-sakthivel
  url: https://www.percona.com/blog/author/sri-sakthivel/
  website: ''
published_at: '2020-06-24T18:30:34'
published_at_gmt: '2020-06-24T18:30:34'
modified_at: '2026-05-05T16:29:59'
modified_at_gmt: '2026-05-05T16:29:59'
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
- insight for DBAs
- MySQL
- mysql-and-variants
tag_slugs:
- insight-for-dbas
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Table-Fragmentation-Insert.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL Table Fragmentation: Beware of Bulk INSERT with FAILURE or ROLLBACK

Source: [Percona Blog](https://www.percona.com/blog/mysql-table-fragmentation-beware-of-bulk-insert-with-failure-or-rollback/)

Auteur source: [Sri Sakthivel](https://www.percona.com/blog/author/sri-sakthivel/)

Publication: 2020-06-24T18:30:34

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Usually, database people are familiar with table fragmentation with DELETE statements. Whenever doing a huge delete, in most cases, they are always rebuilding the table to reclaim the disk space. But, are you thinking only DELETEs can cause table fragmentation? (Answer: NO). In this blog post, I am going to explain how table fragmentation is … Continued

## Structure detectee

- H2: Test Environment
- H3: Case 1: INSERT with ROLLBACK
- H3: Case 2: Failed INSERT Statement
- H4: Case 3: Fragmentation with Page-Splits
- H4: Conclusion

## Images et graphiques reperes

- featured / image: [MySQL Table Fragmentation: Beware of Bulk INSERT with FAILURE or ROLLBACK](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Table-Fragmentation-Insert.png)
- content / image: [MySQL Table Fragmentation Insert](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Table-Fragmentation-Insert-300x168.png)

## Auteur source

Oracle certified MySQL DBA. Working on MySQL and related technologies to ensures database performance. Handling multi client projects round the clock. Currently focusing on MySQL Cluster technologies like Galera and Group replication/InnoDB cluster. Active MySQL Blogger.
