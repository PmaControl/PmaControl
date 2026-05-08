---
title: Multi Column indexes vs Index Merge
source:
  name: Percona Blog
  url: https://www.percona.com/blog/multi-column-indexes-vs-index-merge/
  post_id: 2018
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2009-09-19T16:10:48'
published_at_gmt: '2009-09-19T16:10:48'
modified_at: '2026-04-28T21:02:41'
modified_at_gmt: '2026-04-28T21:02:41'
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
tags:
- Optimizer
tag_slugs:
- optimizer
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Multi Column indexes vs Index Merge

Source: [Percona Blog](https://www.percona.com/blog/multi-column-indexes-vs-index-merge/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2009-09-19T16:10:48

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The mistake I commonly see among MySQL users is how indexes are created. Quite commonly people just index individual columns as they are referenced in where clause thinking this is the optimal indexing strategy. For example if I would have something like AGE=18 AND STATE=’CA’ they would create 2 separate indexes on AGE and STATE … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
