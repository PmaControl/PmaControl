---
title: Virtualization and IO Modes = Extra Complexity
source:
  name: Percona Blog
  url: https://www.percona.com/blog/virtualization-and-io-modes-extra-complexity/
  post_id: 2762
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2011-03-22T00:15:28'
published_at_gmt: '2011-03-22T00:15:28'
modified_at: '2026-04-28T21:24:36'
modified_at_gmt: '2026-04-28T21:24:36'
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
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Virtualization and IO Modes = Extra Complexity

Source: [Percona Blog](https://www.percona.com/blog/virtualization-and-io-modes-extra-complexity/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2011-03-22T00:15:28

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

It has taken a years to get a proper integration between operating system kernel, device driver and hardware to get behavior with caches and IO modes correctly. I remember us having a lot of troubles with fsync() not flushing hard drive write cache and so potential hard drives can be lost on power failure. Happily … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
