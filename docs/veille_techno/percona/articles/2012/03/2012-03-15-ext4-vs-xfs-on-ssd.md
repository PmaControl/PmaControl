---
title: ext4 vs xfs on SSD
source:
  name: Percona Blog
  url: https://www.percona.com/blog/ext4-vs-xfs-on-ssd/
  post_id: 3422
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2012-03-15T19:02:38'
published_at_gmt: '2012-03-15T19:02:38'
modified_at: '2026-04-28T21:33:47'
modified_at_gmt: '2026-04-28T21:33:47'
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
- MySQL
category_slugs:
- benchmarks
- mysql
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# ext4 vs xfs on SSD

Source: [Percona Blog](https://www.percona.com/blog/ext4-vs-xfs-on-ssd/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2012-03-15T19:02:38

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

As ext4 is a standard de facto filesystem for many modern Linux system, I am getting a lot of question if this is good for SSD, or something else (i.e. xfs) should be used. Traditionally our recommendation is xfs, and it comes to known problem in ext3, where IO gets serialized per i_node in O_DIRECT … Continued

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
