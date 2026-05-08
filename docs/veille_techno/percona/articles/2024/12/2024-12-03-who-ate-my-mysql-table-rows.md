---
title: Who Ate My MySQL Table Rows?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/who-ate-my-mysql-table-rows/
  post_id: 29114
source_author:
  name: Dmitry Lenev
  slug: dmitry-lenev
  url: https://www.percona.com/blog/author/dmitry-lenev/
  website: ''
published_at: '2024-12-03T16:07:22'
published_at_gmt: '2024-12-03T16:07:22'
modified_at: '2026-03-26T20:25:52'
modified_at_gmt: '2026-03-26T20:25:52'
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
- alter table
- InnoDB
- MySQL
- mysql-and-variants
tag_slugs:
- alter-table
- innodb
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/ALTER-TABLE-and-OPTIMIZE-TABLE-on-an-InnoDB-table.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Who Ate My MySQL Table Rows?

Source: [Percona Blog](https://www.percona.com/blog/who-ate-my-mysql-table-rows/)

Auteur source: [Dmitry Lenev](https://www.percona.com/blog/author/dmitry-lenev/)

Publication: 2024-12-03T16:07:22

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

TL;DR ALTER TABLE and OPTIMIZE TABLE on an InnoDB table, which rebuilds the table without blocking concurrent changes to it (i.e., executed using INPLACE algorithm) and concurrent DML or purge activity on the table can occasionally lead to two significant problems: ALTER/OPTIMIZE TABLE failing with an unnecessary duplicate key error (even though there are no … Continued

## Structure detectee

- H2: Longer story
- H2: Why do we think these issues are important?
- H2: What can you do to avoid this problem?

## Images et graphiques reperes

- featured / image: [Who Ate My MySQL Table Rows?](https://www.percona.com/wp-content/uploads/2026/03/ALTER-TABLE-and-OPTIMIZE-TABLE-on-an-InnoDB-table.jpg)
- content / image: [mysql-performance-tuning-1.png](https://www.percona.com/wp-content/uploads/2026/03/mysql-performance-tuning-1.png)
