---
title: Better Primary Keys, a Benefit to TokuDB’s Auto Increment Semantics
source:
  name: Percona Blog
  url: https://www.percona.com/blog/better_primary_keys_a_benefit_to_tokudbs_auto_increment_semantics/
  post_id: 9486
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2009-08-04T00:55:00'
published_at_gmt: '2009-08-04T00:55:00'
modified_at: '2026-04-28T22:25:21'
modified_at_gmt: '2026-04-28T22:25:21'
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

# Better Primary Keys, a Benefit to TokuDB’s Auto Increment Semantics

Source: [Percona Blog](https://www.percona.com/blog/better_primary_keys_a_benefit_to_tokudbs_auto_increment_semantics/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2009-08-04T00:55:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In our last post, Bradley described how auto increment works in TokuDB. In this post, I explain one of our implementation’s big benefits, the ability to combine better primary keys with clustered primary keys. In working with customers, the following scenario has come up frequently. The user has data that is streamed into the … Continued
