---
title: Making Deletions Fast, by Avoiding Disk Seeks
source:
  name: Percona Blog
  url: https://www.percona.com/blog/making-deletions-fast-by-avoiding-disk-seeks/
  post_id: 9515
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2010-06-08T16:19:41'
published_at_gmt: '2010-06-08T16:19:41'
modified_at: '2026-04-28T22:28:39'
modified_at_gmt: '2026-04-28T22:28:39'
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
- delete
- disk seek
- Fractal Trees
- MySQL
- TokuDB
tag_slugs:
- b-tree
- delete
- disk-seek
- fractal-trees
- mysql
- tokudb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Making Deletions Fast, by Avoiding Disk Seeks

Source: [Percona Blog](https://www.percona.com/blog/making-deletions-fast-by-avoiding-disk-seeks/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2010-06-08T16:19:41

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In my last post, I discussed how fractal tree data structures can be up to two orders of magnitude faster on deletions over B-trees. I focused on the deletions where the row entry is known (the storage engine API handler::delete_row), but I did not fully analyze how MySQL delete statements can be fast. In this … Continued
