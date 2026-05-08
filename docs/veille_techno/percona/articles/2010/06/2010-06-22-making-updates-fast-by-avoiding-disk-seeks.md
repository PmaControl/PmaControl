---
title: Making Updates Fast, by Avoiding Disk Seeks
source:
  name: Percona Blog
  url: https://www.percona.com/blog/making-updates-fast-by-avoiding-disk-seeks/
  post_id: 9517
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2010-06-22T14:19:23'
published_at_gmt: '2010-06-22T14:19:23'
modified_at: '2026-04-28T22:34:39'
modified_at_gmt: '2026-04-28T22:34:39'
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
- MySQL
- TokuDB
- update
tag_slugs:
- b-tree
- disk-seek
- fractal-trees
- mysql
- tokudb
- update
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Making Updates Fast, by Avoiding Disk Seeks

Source: [Percona Blog](https://www.percona.com/blog/making-updates-fast-by-avoiding-disk-seeks/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2010-06-22T14:19:23

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The analysis that shows how to make deletions really fast by using clustering keys and TokuDB’s fractal tree based engine also applies to make updates really fast. (I left it out of the last post to keep the story simple). As a quick example, let’s look at the following statement: update foo set price=price+1 where product=toy; 1 update foo set price = price + 1 where product = toy ; Executing this statement … Continued
