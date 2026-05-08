---
title: TokuDB Table Optimization Improvements
source:
  name: Percona Blog
  url: https://www.percona.com/blog/tokudb-table-optimization-improvements/
  post_id: 9916
source_author:
  name: Joe.Laflamme
  slug: joe-laflamme
  url: https://www.percona.com/blog/author/joe-laflamme/
  website: ''
published_at: '2015-02-11T19:20:36'
published_at_gmt: '2015-02-11T19:20:36'
modified_at: '2026-05-05T22:24:41'
modified_at_gmt: '2026-05-05T22:24:41'
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
- Fractal Tree
- MySQL
- TokuDB
tag_slugs:
- b-tree
- fractal-tree
- mysql
- tokudb
featured_image_url: https://www.percona.com/blog/wp-content/uploads/2015/02/Fractal-Tree-with-buffers.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# TokuDB Table Optimization Improvements

Source: [Percona Blog](https://www.percona.com/blog/tokudb-table-optimization-improvements/)

Auteur source: [Joe.Laflamme](https://www.percona.com/blog/author/joe-laflamme/)

Publication: 2015-02-11T19:20:36

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Section I: Fractal Tree and Optimization Overview Tokutek’s Fractal Tree® technology provides fast performance by injecting small messages into buffers inside the Fractal Tree index. This allows writes to be batched, thus eliminating I/O that is required in traditional B-tree indexes for every operation. Additional background information on how Fractal Trees operate can be found … Continued

## Images et graphiques reperes

- content / image: [Fractal Tree with buffers](https://www.percona.com/blog/wp-content/uploads/2015/02/Fractal-Tree-with-buffers.jpg)
