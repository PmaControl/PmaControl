---
title: How well do your tables fit in buffer pool
source:
  name: Percona Blog
  url: https://www.percona.com/blog/tables-fit-buffer-poo/
  post_id: 2262
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2010-03-26T16:28:55'
published_at_gmt: '2010-03-26T16:28:55'
modified_at: '2026-04-28T21:09:30'
modified_at_gmt: '2026-04-28T21:09:30'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- InnoDB
- XtraDB
tag_slugs:
- innodb
- xtradb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How well do your tables fit in buffer pool

Source: [Percona Blog](https://www.percona.com/blog/tables-fit-buffer-poo/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2010-03-26T16:28:55

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In XtraDB we have the table INNODB_BUFFER_POOL_PAGES_INDEX which shows which pages belong to which indexes in which tables. Using thing information and standard TABLES table we can see how well different tables fit in buffer pool. SELECT d.*, ROUND(100 * cnt * 16384 / ( data_length + index_length ), 2) fit FROM (SELECT schema_name, table_name, COUNT(*) cnt, SUM(dirty), SUM(hashed) FROM INNODB_BUFFER_POOL_PAGES_INDEX GROUP BY schema_name, table_name ORDER BY cnt DESC LIMIT 20) d JOIN TABLES ON ( TABLES.table_schema = d.schema_name AND TABLES.table_name = d.table_name ); +-------------+---------------------+---------+------------+-------------+--------+ | schema_name | table_name | cnt | sum(dirty) | sum(hashed) | fit | +-------------+---------------------+---------+------------+-------------+--------+ | db | table1 | 1699133 | 13296 | 385841 | 87.49 | | db | table2 | 1173272 | 17399 |...

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
