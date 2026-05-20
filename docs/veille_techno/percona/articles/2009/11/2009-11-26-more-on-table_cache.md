---
title: More on table_cache
source:
  name: Percona Blog
  url: https://www.percona.com/blog/more-on-table_cache/
  post_id: 2139
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2009-11-26T23:07:54'
published_at_gmt: '2009-11-26T23:07:54'
modified_at: '2026-03-23T21:36:31'
modified_at_gmt: '2026-03-23T21:36:31'
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
tags:
- MyISAM
tag_slugs:
- myisam
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# More on table_cache

Source: [Percona Blog](https://www.percona.com/blog/more-on-table_cache/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2009-11-26T23:07:54

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In my previous post I looked into how large table_cache actually can decrease performance. The “miss” path is getting more expensive very quickly as table cache growths so if you’re going to have high miss ratio anyway you’re better off with small table cache. What I have not checked though is how does table_cache (or … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
