---
title: 'DROP TABLE and stalls: Lazy Drop Table in Percona Server and the new fixes in MySQL'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/drop-table-and-stalls-lazy-drop-table-in-percona-server-and-the-new-fixes-in-mysql/
  post_id: 3651
source_author:
  name: Ovais Tariq
  slug: ovaistariq
  url: https://www.percona.com/blog/author/ovaistariq/
  website: http://www.percona.com/blog/
published_at: '2012-06-22T09:50:13'
published_at_gmt: '2012-06-22T09:50:13'
modified_at: '2026-04-28T21:36:58'
modified_at_gmt: '2026-04-28T21:36:58'
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
- MySQL
category_slugs:
- benchmarks
- insight-for-dbas
- mysql
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/no_drop_table.png
image_count: 5
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# DROP TABLE and stalls: Lazy Drop Table in Percona Server and the new fixes in MySQL

Source: [Percona Blog](https://www.percona.com/blog/drop-table-and-stalls-lazy-drop-table-in-percona-server-and-the-new-fixes-in-mysql/)

Auteur source: [Ovais Tariq](https://www.percona.com/blog/author/ovaistariq/)

Publication: 2012-06-22T09:50:13

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Suppose you have turned on innodb_file_per_table (which means that each table has its own tablespace), and you have to drop tables in a background every hour or every day. If its once every day then you can probably schedule the table dropping process to run during off-peak hours. But I have seen cases where the … Continued

## Structure detectee

- H3: Implementation
- H4: Lazy Drop Table in Percona Server
- H4: Drop Table in Oracle MySQL >= 5.5.23
- H3: Benchmarks
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [DROP TABLE and stalls: Lazy Drop Table in Percona Server and the new fixes in MySQL](https://www.percona.com/wp-content/uploads/2026/03/no_drop_table.png)
- content / image: [no_drop_table1.png](https://www.percona.com/wp-content/uploads/2026/03/no_drop_table1.png)
- content / image: [drop_table_55151.png](https://www.percona.com/wp-content/uploads/2026/03/drop_table_55151.png)
- content / image: [drop_table_55231.png](https://www.percona.com/wp-content/uploads/2026/03/drop_table_55231.png)
- content / image: [lazy_drop_table1.png](https://www.percona.com/wp-content/uploads/2026/03/lazy_drop_table1.png)
