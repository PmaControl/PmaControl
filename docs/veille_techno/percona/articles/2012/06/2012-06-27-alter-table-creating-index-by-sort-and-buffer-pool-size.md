---
title: 'ALTER TABLE: Creating Index by Sort and Buffer Pool Size'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/alter-table-creating-index-by-sort-and-buffer-pool-size/
  post_id: 3669
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2012-06-27T21:36:36'
published_at_gmt: '2012-06-27T21:36:36'
modified_at: '2026-04-28T21:37:29'
modified_at_gmt: '2026-04-28T21:37:29'
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
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/128mb.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# ALTER TABLE: Creating Index by Sort and Buffer Pool Size

Source: [Percona Blog](https://www.percona.com/blog/alter-table-creating-index-by-sort-and-buffer-pool-size/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2012-06-27T21:36:36

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Today I was looking at the ALTER TABLE performance with fast index creation and without it with different buffer pool sizes. Results are pretty interesting. I used modified Sysbench table for these tests because original table as initially created only has index on column K which initially contains only zeros, which means index is very … Continued

## Images et graphiques reperes

- featured / image: [ALTER TABLE: Creating Index by Sort and Buffer Pool Size](https://www.percona.com/wp-content/uploads/2026/03/128mb.png)
- content / image: [6gb.png](https://www.percona.com/wp-content/uploads/2026/03/6gb.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
