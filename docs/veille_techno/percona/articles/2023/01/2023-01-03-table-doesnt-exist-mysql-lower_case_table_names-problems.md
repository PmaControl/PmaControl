---
title: 'Table Doesn’t Exist: MySQL lower_case_table_names Problems'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/table-doesnt-exist-mysql-lower_case_table_names-problems/
  post_id: 26442
source_author:
  name: Bhuvanes Waran
  slug: bhuvanes-waran
  url: https://www.percona.com/blog/author/bhuvanes-waran/
  website: ''
published_at: '2023-01-03T12:57:41'
published_at_gmt: '2023-01-03T12:57:41'
modified_at: '2026-03-26T20:30:22'
modified_at_gmt: '2026-03-26T20:30:22'
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
- MySQL
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Table-Doesnt-Exist-MySQL.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Table Doesn’t Exist: MySQL lower_case_table_names Problems

Source: [Percona Blog](https://www.percona.com/blog/table-doesnt-exist-mysql-lower_case_table_names-problems/)

Auteur source: [Bhuvanes Waran](https://www.percona.com/blog/author/bhuvanes-waran/)

Publication: 2023-01-03T12:57:41

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In Managed Services, we have many customers, and as each has a different kind of config and environment, working on their environment is always fun and interesting. In this blog post, I will showcase an issue we faced when dropping a table and how it was resolved. There was a ticket to drop a table … Continued

## Structure detectee

- H2: Scenario One: Create table when lower_case_table_names=0 and drop when lower_case_table_names=1
- H2: Scenario Two: Create table when lower_case_table_names=1 and drop when lower_case_table_names=0
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Table Doesn’t Exist: MySQL lower_case_table_names Problems](https://www.percona.com/wp-content/uploads/2026/03/Table-Doesnt-Exist-MySQL.png)
- content / image: [Table Doesn't Exist MySQL](https://www.percona.com/wp-content/uploads/2026/03/Table-Doesnt-Exist-MySQL-300x157.png)

## Auteur source

Bhuvan works as a MySQL DBA in Percona since 2021.
