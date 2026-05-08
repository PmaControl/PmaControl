---
title: Real World Compression
source:
  name: Percona Blog
  url: https://www.percona.com/blog/real-world-compression/
  post_id: 9700
source_author:
  name: Tim.Callaghan
  slug: tim-callaghan
  url: https://www.percona.com/blog/author/tim-callaghan/
  website: ''
published_at: '2012-08-01T17:25:26'
published_at_gmt: '2012-08-01T17:25:26'
modified_at: '2026-05-05T16:58:56'
modified_at_gmt: '2026-05-05T16:58:56'
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
- Benchmarking
- InnoDB
- MySQL
- NewSQL
- Storage Engine
- TokuDB
- Tokutek
tag_slugs:
- benchmarking
- innodb
- mysql
- newsql
- storage-engine
- tokudb
- tokutek
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Real World Compression

Source: [Percona Blog](https://www.percona.com/blog/real-world-compression/)

Auteur source: [Tim.Callaghan](https://www.percona.com/blog/author/tim-callaghan/)

Publication: 2012-08-01T17:25:26

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Benchmarking is a tricky thing, especially when it comes to compression. Some data compresses quite well while other data does not compress at all. Storing jpeg images in a BLOB column produces 0% compression, but storing the string “AAAAAAAAAAAAAAAAAAAA” in a VARCHAR(20) column produces extremely high (and unrealistic) compression numbers. This week I was assisting … Continued
