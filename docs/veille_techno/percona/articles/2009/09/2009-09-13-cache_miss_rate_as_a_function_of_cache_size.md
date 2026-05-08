---
title: Cache Miss Rate as a function of Cache Size
source:
  name: Percona Blog
  url: https://www.percona.com/blog/cache_miss_rate_as_a_function_of_cache_size/
  post_id: 9488
source_author:
  name: kuszmaul
  slug: kuszmaul
  url: https://www.percona.com/blog/author/kuszmaul/
  website: ''
published_at: '2009-09-13T04:07:00'
published_at_gmt: '2009-09-13T04:07:00'
modified_at: '2026-03-25T18:13:38'
modified_at_gmt: '2026-03-25T18:13:38'
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

# Cache Miss Rate as a function of Cache Size

Source: [Percona Blog](https://www.percona.com/blog/cache_miss_rate_as_a_function_of_cache_size/)

Auteur source: [kuszmaul](https://www.percona.com/blog/author/kuszmaul/)

Publication: 2009-09-13T04:07:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I saw Mark Callaghan’s post, and his graph showing miss rate as a function of cache size for InnoDB running MySQL. He plots miss rate against cache size and compares it to two simple models: A linear model where the miss rate is (1-C/D)/50, and A inverse-proportional model where the miss rate is D/(1000C). He … Continued
