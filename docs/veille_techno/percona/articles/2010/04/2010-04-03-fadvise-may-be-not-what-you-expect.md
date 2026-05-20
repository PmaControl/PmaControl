---
title: fadvise – may be not what you expect
source:
  name: Percona Blog
  url: https://www.percona.com/blog/fadvise-may-be-not-what-you-expect/
  post_id: 2270
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2010-04-03T06:02:14'
published_at_gmt: '2010-04-03T06:02:14'
modified_at: '2026-04-28T21:09:46'
modified_at_gmt: '2026-04-28T21:09:46'
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
- Tips
tag_slugs:
- tips
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# fadvise – may be not what you expect

Source: [Percona Blog](https://www.percona.com/blog/fadvise-may-be-not-what-you-expect/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2010-04-03T06:02:14

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I often hear suggestion to use fadvise 1 fadvise system call to avoid caching in OS cache. We recently made patch for tar 1 tar , which supposes to create archive without polluting OS cache, as like in case with backup, you do not really expect any benefits from caching. However working on the patch, I noticed, that fadvise 1 fadvise … Continued

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
