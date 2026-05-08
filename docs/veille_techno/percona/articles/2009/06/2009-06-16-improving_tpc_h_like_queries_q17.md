---
title: Improving TPC-H-like queries – Q17
source:
  name: Percona Blog
  url: https://www.percona.com/blog/improving_tpc_h_like_queries_q17/
  post_id: 9471
source_author:
  name: kuszmaul
  slug: kuszmaul
  url: https://www.percona.com/blog/author/kuszmaul/
  website: ''
published_at: '2009-06-16T01:12:00'
published_at_gmt: '2009-06-16T01:12:00'
modified_at: '2026-04-28T22:24:37'
modified_at_gmt: '2026-04-28T22:24:37'
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

# Improving TPC-H-like queries – Q17

Source: [Percona Blog](https://www.percona.com/blog/improving_tpc_h_like_queries_q17/)

Auteur source: [kuszmaul](https://www.percona.com/blog/author/kuszmaul/)

Publication: 2009-06-16T01:12:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Executive Summary: A query like TPC-H Query 17 can be sped up by large factors by using straight_joins and clustering indexes. (This entry posted by Dave.) In a previous post, we wrote about queries like TPC-H query 2, and the use of straight_join to improve performance. This week, we consider Query 17, described by … Continued
