---
title: Data Corruption, DRBD and story of bug
source:
  name: Percona Blog
  url: https://www.percona.com/blog/data-corruption-drbd-and-story-of-bug/
  post_id: 2505
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2010-11-30T05:47:47'
published_at_gmt: '2010-11-30T05:47:47'
modified_at: '2026-04-28T21:20:06'
modified_at_gmt: '2026-04-28T21:20:06'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Hardware and Storage
- MySQL
category_slugs:
- hardware-and-storage
- mysql
tags:
- Bugs
- High Availability
tag_slugs:
- bugs
- high-availability
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Data Corruption, DRBD and story of bug

Source: [Percona Blog](https://www.percona.com/blog/data-corruption-drbd-and-story-of-bug/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2010-11-30T05:47:47

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Working with customer, I faced pretty nasty bug, which is actually not rare situation , but in this particular there are some lessons I would like to share. The case is pretty much described in bug 55981, or in pastebin. Everything below is related to InnoDB-plugin/XtraDB, but not to regular InnoDB ( i.e in MySQL … Continued

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
