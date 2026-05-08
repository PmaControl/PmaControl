---
title: How rows_sent can be more than rows_examined?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/rows_sent-can-rows_examined/
  post_id: 7878
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2014-02-28T11:00:55'
published_at_gmt: '2014-02-28T11:00:55'
modified_at: '2026-04-28T22:02:26'
modified_at_gmt: '2026-04-28T22:02:26'
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
- rows_examined
- rows_sent
tag_slugs:
- rows_examined
- rows_sent
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How rows_sent can be more than rows_examined?

Source: [Percona Blog](https://www.percona.com/blog/rows_sent-can-rows_examined/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2014-02-28T11:00:55

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

When looking at queries that are candidates for optimization I often recommend that people look at rows_sent and rows_examined values as available in the slow query log (as well as some other places). If rows_examined is by far larger than rows_sent, say 100 larger, then the query is a great candidate for optimization. Optimization could … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
