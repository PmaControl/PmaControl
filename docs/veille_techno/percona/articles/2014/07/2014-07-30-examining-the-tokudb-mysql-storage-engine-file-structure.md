---
title: Examining the TokuDB MySQL storage engine file structure
source:
  name: Percona Blog
  url: https://www.percona.com/blog/examining-the-tokudb-mysql-storage-engine-file-structure/
  post_id: 8416
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2014-07-30T15:07:08'
published_at_gmt: '2014-07-30T15:07:08'
modified_at: '2026-04-28T22:09:03'
modified_at_gmt: '2026-04-28T22:09:03'
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
- MySQL 5.6
- MySQL file structure
- MySQL Storage Engine
- Peter Zaitsev
- TokuDB
tag_slugs:
- mysql-5-6
- mysql-file-structure
- mysql-storage-engine
- peter-zaitsev
- tokudb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Examining the TokuDB MySQL storage engine file structure

Source: [Percona Blog](https://www.percona.com/blog/examining-the-tokudb-mysql-storage-engine-file-structure/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2014-07-30T15:07:08

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

As we know different storage engines in MySQL have different file structures. Every table in MySQL 5.6 must have a .frm file in the database directory matching the table name. But where the rest of the data resides depends on the storage engine. For MyISAM we have .MYI and .MYD files in the database directory … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
