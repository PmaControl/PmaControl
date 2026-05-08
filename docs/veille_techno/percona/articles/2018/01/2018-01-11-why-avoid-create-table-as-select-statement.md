---
title: SQL – "CREATE TABLE AS SELECT" Statement
source:
  name: Percona Blog
  url: https://www.percona.com/blog/why-avoid-create-table-as-select-statement/
  post_id: 17816
source_author:
  name: Alexander Rubin
  slug: alexanderrubin
  url: https://www.percona.com/blog/author/alexanderrubin/
  website: http://www.percona.com/blog
published_at: '2018-01-11T00:58:23'
published_at_gmt: '2018-01-11T00:58:23'
modified_at: '2026-05-05T20:18:34'
modified_at_gmt: '2026-05-05T20:18:34'
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
- create table as select
- metadata locks
- MySQL
- open source database
- row locking
- table locking
tag_slugs:
- create-table-as-select
- metadata-locks
- mysql
- open-source-database
- row-locking
- table-locking
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Create-Table-As-Select.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# SQL – "CREATE TABLE AS SELECT" Statement

Source: [Percona Blog](https://www.percona.com/blog/why-avoid-create-table-as-select-statement/)

Auteur source: [Alexander Rubin](https://www.percona.com/blog/author/alexanderrubin/)

Publication: 2018-01-11T00:58:23

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, I’ll provide an explanation of why you should avoid using the CREATE TABLE AS SELECT statement. The SQL statement “create table <table_name> as select …” is used to create a normal or temporary table and materialize the result of the select. Some applications use this construct to create a copy of … Continued

## Structure detectee

- H2: CREATE TABLE AS SELECT statement can break things very badly
- H3: GTID issue

## Images et graphiques reperes

- featured / image: [SQL – "CREATE TABLE AS SELECT" Statement](https://www.percona.com/wp-content/uploads/2026/03/Create-Table-As-Select.jpg)
- content / image: [Create Table As Select](https://www.percona.com/wp-content/uploads/2026/03/Create-Table-As-Select-300x200.jpg)

## Auteur source

Alexander joined Percona in 2013. Alexander worked with MySQL since 2000 as DBA and Application Developer. Before joining Percona he was doing MySQL consulting as a principal consultant for over 7 years (started with MySQL AB in 2006, then Sun Microsystems and then Oracle). He has helped many customers design large, scalable and highly available MySQL systems and optimize MySQL performance. Alexander has also helped customers design Big Data stores with Apache Hadoop and related technologies.
