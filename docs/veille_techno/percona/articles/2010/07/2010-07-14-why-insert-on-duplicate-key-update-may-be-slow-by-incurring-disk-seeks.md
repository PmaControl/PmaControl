---
title: Why "insert … on duplicate key update" May Be Slow, by Incurring Disk Seeks
source:
  name: Percona Blog
  url: https://www.percona.com/blog/why-insert-on-duplicate-key-update-may-be-slow-by-incurring-disk-seeks/
  post_id: 9521
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2010-07-14T19:17:49'
published_at_gmt: '2010-07-14T19:17:49'
modified_at: '2026-03-25T18:15:03'
modified_at_gmt: '2026-03-25T18:15:03'
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
- insert ignore
- insert on duplicate key update
- MySQL
- replace into
- TokuDB
tag_slugs:
- b-tree
- disk-seek
- fractal-trees
- insert
- insert-ignore
- insert-on-duplicate-key-update
- mysql
- replace-into
- tokudb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Why "insert … on duplicate key update" May Be Slow, by Incurring Disk Seeks

Source: [Percona Blog](https://www.percona.com/blog/why-insert-on-duplicate-key-update-may-be-slow-by-incurring-disk-seeks/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2010-07-14T19:17:49

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In my post on June 18th, I explained why the semantics of normal ad-hoc insertions with a primary key are expensive because they require disk seeks on large data sets. I previously explained why it would be better to use “replace into” or to use “insert ignore” over normal inserts. In this post, I explain … Continued
