---
title: Addressing Hot Schema Changes in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/addressing-hot-schema-changes-in-mysql/
  post_id: 9683
source_author:
  name: Rich.Prohaska
  slug: rich-prohaska
  url: https://www.percona.com/blog/author/rich-prohaska/
  website: ''
published_at: '2012-06-14T14:28:19'
published_at_gmt: '2012-06-14T14:28:19'
modified_at: '2026-03-25T18:22:30'
modified_at_gmt: '2026-03-25T18:22:30'
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
- MySQL
- NewSQL
- Storage Engine
- TokuDB
- Tokutek
tag_slugs:
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

# Addressing Hot Schema Changes in MySQL

Source: [Percona Blog](https://www.percona.com/blog/addressing-hot-schema-changes-in-mysql/)

Auteur source: [Rich.Prohaska](https://www.percona.com/blog/author/rich-prohaska/)

Publication: 2012-06-14T14:28:19

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

As ones data model evolves changing the database schema becomes painful, especially for big databases where the table must be taken offline. Fortunately, Tokutek introduced online schema changes starting in TokuDB v5.0. A typical schema change involves adding or deleting a column from a table. These operations usually require the table to be rebuilt offline since the … Continued
