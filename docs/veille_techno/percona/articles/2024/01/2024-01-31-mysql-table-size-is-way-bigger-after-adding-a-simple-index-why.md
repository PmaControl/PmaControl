---
title: MySQL Table Size Is Way Bigger After Adding a Simple Index; Why?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-table-size-is-way-bigger-after-adding-a-simple-index-why/
  post_id: 28020
source_author:
  name: Przemysław Malkowski
  slug: przemek-malkowski
  url: https://www.percona.com/blog/author/przemek-malkowski/
  website: ''
published_at: '2024-01-31T14:00:03'
published_at_gmt: '2024-01-31T14:00:03'
modified_at: '2026-03-26T20:26:43'
modified_at_gmt: '2026-03-26T20:26:43'
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
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- MySQL
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Table-Size.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL Table Size Is Way Bigger After Adding a Simple Index; Why?

Source: [Percona Blog](https://www.percona.com/blog/mysql-table-size-is-way-bigger-after-adding-a-simple-index-why/)

Auteur source: [Przemysław Malkowski](https://www.percona.com/blog/author/przemek-malkowski/)

Publication: 2024-01-31T14:00:03

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

It is a known good practice to keep only necessary indexes to reduce the write performance and disk space overhead. This simple rule is mentioned briefly in the official MySQL Documentation: https://dev.mysql.com/doc/refman/8.0/en/optimization-indexes.html However, in some cases, the overhead from adding a new index can be way above the expectations! Recently, I’ve been analyzing a customer … Continued

## Images et graphiques reperes

- featured / image: [MySQL Table Size Is Way Bigger After Adding a Simple Index; Why?](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Table-Size.jpg)
- content / image: [db1_t1_free_PK_SK.png](https://www.percona.com/wp-content/uploads/2026/03/db1_t1_free_PK_SK.png)

## Auteur source

Przemek joined Support Team at Percona in August 2012. Before that he spent over five years working for Wikia.com (Quantcast Top 50) as System Administrator where he was a key person responsible for seamless building up MySQL powered database infrastructure. Besides MySQL he worked on maintaining all other parts of LAMP stack, with main focus on automation, monitoring and backups.
