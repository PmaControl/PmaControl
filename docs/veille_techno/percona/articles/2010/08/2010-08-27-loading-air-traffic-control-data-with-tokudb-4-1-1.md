---
title: Loading Air Traffic Control Data with TokuDB 4.1.1
source:
  name: Percona Blog
  url: https://www.percona.com/blog/loading-air-traffic-control-data-with-tokudb-4-1-1/
  post_id: 9528
source_author:
  name: Rich.Prohaska
  slug: rich-prohaska
  url: https://www.percona.com/blog/author/rich-prohaska/
  website: ''
published_at: '2010-08-27T13:36:47'
published_at_gmt: '2010-08-27T13:36:47'
modified_at: '2026-03-25T18:15:26'
modified_at_gmt: '2026-03-25T18:15:26'
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
- Fractal Tree
- MySQL
- parallelism
- TokuDB
tag_slugs:
- fractal-tree
- mysql
- parallelism
- tokudb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Loading Air Traffic Control Data with TokuDB 4.1.1

Source: [Percona Blog](https://www.percona.com/blog/loading-air-traffic-control-data-with-tokudb-4-1-1/)

Auteur source: [Rich.Prohaska](https://www.percona.com/blog/author/rich-prohaska/)

Publication: 2010-08-27T13:36:47

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

TokuDB has a big advantage over B-trees when trickle loading data into existing tables. However, it is possible to preprocess the data when bulk loading into empty tables or when new indexes are created. TokuDB release 4 now uses a parallel algorithm to speed up these types of bulk insertions. How does the parallel loader … Continued
