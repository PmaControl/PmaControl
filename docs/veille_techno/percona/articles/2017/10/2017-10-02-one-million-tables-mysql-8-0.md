---
title: One Million Tables in MySQL 8.0
source:
  name: Percona Blog
  url: https://www.percona.com/blog/one-million-tables-mysql-8-0/
  post_id: 17457
source_author:
  name: Alexander Rubin
  slug: alexanderrubin
  url: https://www.percona.com/blog/author/alexanderrubin/
  website: http://www.percona.com/blog
published_at: '2017-10-02T02:26:12'
published_at_gmt: '2017-10-02T02:26:12'
modified_at: '2026-05-05T20:17:31'
modified_at_gmt: '2026-05-05T20:17:31'
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
- search:pmm
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- Benchmarks
- InnoDB
- MySQL 8.0
- tables
tag_slugs:
- benchmarks
- innodb
- mysql-8-0
- tables
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.0-e1506728372654.png
image_count: 6
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# One Million Tables in MySQL 8.0

Source: [Percona Blog](https://www.percona.com/blog/one-million-tables-mysql-8-0/)

Auteur source: [Alexander Rubin](https://www.percona.com/blog/author/alexanderrubin/)

Publication: 2017-10-02T02:26:12

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In my previous blog post, I talked about new general tablespaces in MySQL 8.0. Recently MySQL 8.0.3-rc was released, which includes a new data dictionary. My goal is to create one million tables in MySQL and test the performance. Background questions Q: Why million tables in MySQL? Is it even realistic? How does this happen? … Continued

## Structure detectee

- H4: Background questions
- H4: One million tables in MySQL 5.7
- H4: Hardware and config files
- H4: One million tables in MySQL 8.0 + general tablespaces
- H4: Creating one million tables
- H4: Size on disk
- H4: Benchmarking the insert speed in MySQL 8.0
- H4: Conclusion

## Images et graphiques reperes

- featured / image: [One Million Tables in MySQL 8.0](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.0-e1506728372654.png)
- content / image: [MySQL 8.0](https://www.percona.com/wp-content/uploads/2026/03/com_insert.png)
- content / image: [MySQL 8.0](https://www.percona.com/wp-content/uploads/2026/03/table_cache_misses.png)
- content / image: [MySQL 8.0](https://www.percona.com/wp-content/uploads/2026/03/table_open_cache.png)
- content / image: [MySQL 8.0](https://www.percona.com/wp-content/uploads/2026/03/qps_100k_tables.png)
- content / image: [MySQL 8.0](https://www.percona.com/wp-content/uploads/2026/03/table_open_cache_100k.png)

## Auteur source

Alexander joined Percona in 2013. Alexander worked with MySQL since 2000 as DBA and Application Developer. Before joining Percona he was doing MySQL consulting as a principal consultant for over 7 years (started with MySQL AB in 2006, then Sun Microsystems and then Oracle). He has helped many customers design large, scalable and highly available MySQL systems and optimize MySQL performance. Alexander has also helped customers design Big Data stores with Apache Hadoop and related technologies.
