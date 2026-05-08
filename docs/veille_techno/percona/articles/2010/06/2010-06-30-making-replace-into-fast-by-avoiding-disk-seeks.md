---
title: Making "Replace Into" Fast, by Avoiding Disk Seeks
source:
  name: Percona Blog
  url: https://www.percona.com/blog/making-replace-into-fast-by-avoiding-disk-seeks/
  post_id: 9518
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2010-06-30T04:10:02'
published_at_gmt: '2010-06-30T04:10:02'
modified_at: '2026-04-28T22:37:39'
modified_at_gmt: '2026-04-28T22:37:39'
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
- B-Tree
- disk seek
- Fractal Trees
- Insert
- MySQL
- replace
- replace into
- TokuDB
- update
tag_slugs:
- b-tree
- disk-seek
- fractal-trees
- insert
- mysql
- replace
- replace-into
- tokudb
- update
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Making "Replace Into" Fast, by Avoiding Disk Seeks

Source: [Percona Blog](https://www.percona.com/blog/making-replace-into-fast-by-avoiding-disk-seeks/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2010-06-30T04:10:02

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this post two weeks ago, I explained why the semantics of normal ad-hoc insertions with a primary key are expensive because they require disk seeks on large data sets. Towards the end of the post, I claimed that it would be better to use “replace into” or “insert ignore” over normal inserts, because the … Continued
