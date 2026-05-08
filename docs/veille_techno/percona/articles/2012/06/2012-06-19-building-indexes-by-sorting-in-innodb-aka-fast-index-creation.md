---
title: Building Indexes by Sorting In Innodb (AKA Fast Index Creation)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/building-indexes-by-sorting-in-innodb-aka-fast-index-creation/
  post_id: 3637
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2012-06-19T16:11:58'
published_at_gmt: '2012-06-19T16:11:58'
modified_at: '2026-04-28T21:36:26'
modified_at_gmt: '2026-04-28T21:36:26'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Benchmarks
- Insight for DBAs
- MySQL
category_slugs:
- benchmarks
- insight-for-dbas
- mysql
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Building Indexes by Sorting In Innodb (AKA Fast Index Creation)

Source: [Percona Blog](https://www.percona.com/blog/building-indexes-by-sorting-in-innodb-aka-fast-index-creation/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2012-06-19T16:11:58

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Innodb can indexes built by sort since Innodb Plugin for MySQL 5.1 which is a lot faster than building them through insertion, especially for tables much larger than memory and large uncorrelated indexes you might be looking at 10x difference or more. Yet for some reason Innodb team has chosen to use very small (just … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
