---
title: Why Unique Indexes are Bad
source:
  name: Percona Blog
  url: https://www.percona.com/blog/why-unique-indexes-are-bad/
  post_id: 9799
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2013-07-15T15:56:21'
published_at_gmt: '2013-07-15T15:56:21'
modified_at: '2026-03-25T18:26:31'
modified_at_gmt: '2026-03-25T18:26:31'
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
- Fractal Tree™ indexes
- indexing
- MongoDB
- MySQL
- Performance
- TokuDB
- tokumx
tag_slugs:
- b-tree
- fractal-tree-indexes
- indexing
- mongodb
- mysql
- performance
- tokudb
- tokumx
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Why Unique Indexes are Bad

Source: [Percona Blog](https://www.percona.com/blog/why-unique-indexes-are-bad/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2013-07-15T15:56:21

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Before creating a unique index in TokuMX or TokuDB, ask yourself, “does my application really depend on the database enforcing uniqueness of this key?” If the answer is ANYTHING other than yes, do not declare the index to be unique. Why? Because unique indexes may kill your write performance. In this post, I’ll explain why. … Continued
