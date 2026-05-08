---
title: A Case for Write Optimizations in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/a-case-for-write-optimizations-in-mysql/
  post_id: 9620
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2011-11-21T15:22:33'
published_at_gmt: '2011-11-21T15:22:33'
modified_at: '2026-03-25T18:20:19'
modified_at_gmt: '2026-03-25T18:20:19'
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
category_slugs:
- mysql
tags:
- Fractal Tree™ indexes
- InnoDB
- MySQL
- Storage Engine
- TokuDB
- Tokutek
tag_slugs:
- fractal-tree-indexes
- innodb
- mysql
- storage-engine
- tokudb
- tokutek
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# A Case for Write Optimizations in MySQL

Source: [Percona Blog](https://www.percona.com/blog/a-case-for-write-optimizations-in-mysql/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2011-11-21T15:22:33

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

As a storage engine developer, I am excited for MySQL 5.6. Looking at http://dev.mysql.com/tech-resources/articles/whats-new-in-mysql-5.6.html, there has been plenty of work done to improve the performance of reads in MySQL for all storage engines (provided they take advantage of the new APIs). What would be great to add is API improvements to increase the performance of … Continued
