---
title: 'MySQL with Diagrams Part Three: The Life Story of the Writing Process'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-with-diagrams-part-three-the-life-story-of-the-writing-process/
  post_id: 35263
source_author:
  name: Yunus Uyanik
  slug: yunus-uyanik
  url: https://www.percona.com/blog/author/yunus-uyanik/
  website: ''
published_at: '2025-09-17T13:27:49'
published_at_gmt: '2025-09-17T13:27:49'
modified_at: '2026-03-26T20:25:16'
modified_at_gmt: '2026-03-26T20:25:16'
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
- InnoDB
- MySQL
- mysql-and-variants
tag_slugs:
- innodb
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/How-MySQL-writes-work.jpg
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL with Diagrams Part Three: The Life Story of the Writing Process

Source: [Percona Blog](https://www.percona.com/blog/mysql-with-diagrams-part-three-the-life-story-of-the-writing-process/)

Auteur source: [Yunus Uyanik](https://www.percona.com/blog/author/yunus-uyanik/)

Publication: 2025-09-17T13:27:49

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

When you run a simple write, …it may look simple, but under the hood, MySQL’s InnoDB engine kicks off a pretty complex sequence to ensure your data stays safe, consistent, and crash-recoverable. In the top-left corner of the diagram, we see exactly where this begins — the moment the query is executed: Transact-SQL mysql> UPDATE testdb.t1 SET col1=1 WHERE id=1; 1 mysql > UPDATE testdb . t1 SET col1 = 1 WHERE id = 1 ; The log buffer: … Continued

## Structure detectee

- H3: The log buffer: First stop for changes
- H3: Buffer pool & dirty pages: The real data
- H3: Undo logs and undo segments
- H3: Redo log usage triggers checkpoints
- H3: Enter the binary log
- H3: Flush timing – innodb_flush_log_at_trx_commit
- H3: Key takeaways

## Images et graphiques reperes

- featured / image: [MySQL with Diagrams Part Three: The Life Story of the Writing Process](https://www.percona.com/wp-content/uploads/2026/03/How-MySQL-writes-work.jpg)
- content / image: [MySQL-Vector-Search-Survey.png](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Vector-Search-Survey.png)
- content / image: [run a simple write](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2025-09-15-at-17.39.49-1024x680.png)
