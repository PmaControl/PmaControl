---
title: How clustering indexes sometimes help UPDATE and DELETE performance
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how_clustering_indexes_sometimes_help_update_and_delete_performance/
  post_id: 9469
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2009-06-04T23:35:00'
published_at_gmt: '2009-06-04T23:35:00'
modified_at: '2026-04-28T22:24:21'
modified_at_gmt: '2026-04-28T22:24:21'
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
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How clustering indexes sometimes help UPDATE and DELETE performance

Source: [Percona Blog](https://www.percona.com/blog/how_clustering_indexes_sometimes_help_update_and_delete_performance/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2009-06-04T23:35:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I recently posted a blog entry on clustering indexes, which are good for speeding up queries. Eric Day brought up the concern that clustering indexes might degrade update performance. This is often true, since any update will require updating the clustering index as well. However, there are some cases in TokuDB for MySQL, where … Continued
