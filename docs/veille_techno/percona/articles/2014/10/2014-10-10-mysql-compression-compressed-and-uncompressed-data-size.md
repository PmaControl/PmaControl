---
title: 'MySQL compression: Compressed and Uncompressed data size'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-compression-compressed-and-uncompressed-data-size/
  post_id: 8634
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2014-10-10T14:34:39'
published_at_gmt: '2014-10-10T14:34:39'
modified_at: '2026-05-04T22:27:36'
modified_at_gmt: '2026-05-04T22:27:36'
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
- Archive
- INFORMATION_SCHEMA TABLES
- InnoDB
- MySQL
- MySQL compression
- Peter Zaitsev
- Primary
- TokuDB
tag_slugs:
- archive
- information_schema-tables
- innodb
- mysql
- mysql-compression
- peter-zaitsev
- primary
- tokudb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL compression: Compressed and Uncompressed data size

Source: [Percona Blog](https://www.percona.com/blog/mysql-compression-compressed-and-uncompressed-data-size/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2014-10-10T14:34:39

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MySQL has information_schema.tables that contain information such as “data_length” or “avg_row_length.” Documentation on this table however is quite poor, making an assumption that those fields are self explanatory – they are not when it comes to tables that employ compression. And this is where inconsistency is born. Lets take a look at the same table … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
