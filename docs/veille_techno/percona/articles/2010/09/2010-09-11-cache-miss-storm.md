---
title: Cache Miss Storm
source:
  name: Percona Blog
  url: https://www.percona.com/blog/cache-miss-storm/
  post_id: 2440
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2010-09-11T00:07:35'
published_at_gmt: '2010-09-11T00:07:35'
modified_at: '2026-03-23T21:44:52'
modified_at_gmt: '2026-03-23T21:44:52'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for Developers
- MySQL
category_slugs:
- insight-for-developers
- mysql
tags:
- Production
tag_slugs:
- production
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Cache Miss Storm

Source: [Percona Blog](https://www.percona.com/blog/cache-miss-storm/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2010-09-11T00:07:35

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I worked on the problem recently which showed itself as rather low MySQL load (probably 5% CPU usage and close to zero IO) would spike to have hundreds instances of threads running at the same time, causing intense utilization spike and server very unresponsive for anywhere from half a minute to ten minutes until everything … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
