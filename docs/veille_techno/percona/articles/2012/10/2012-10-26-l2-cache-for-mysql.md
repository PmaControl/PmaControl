---
title: L2 cache for MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/l2-cache-for-mysql/
  post_id: 6434
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2012-10-26T01:26:44'
published_at_gmt: '2012-10-26T01:26:44'
modified_at: '2026-03-23T22:34:13'
modified_at_gmt: '2026-03-23T22:34:13'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-xtrabackup
- search:xtrabackup
categories:
- Benchmarks
- Hardware and Storage
- MySQL
- Percona Software
category_slugs:
- benchmarks
- hardware-and-storage
- mysql
- percona-software
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/l2cache-thrp.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# L2 cache for MySQL

Source: [Percona Blog](https://www.percona.com/blog/l2-cache-for-mysql/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2012-10-26T01:26:44

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The idea to use SSD/Flash as a cache is not new, and there are different solutions for this, both OpenSource like L2ARC for ZFS and Flashcache from Facebook, and proprietary, like directCache from Fusion-io.They all however have some limitations, that’s why I am considering to have L2 cache on a database level, as an extension … Continued

## Images et graphiques reperes

- featured / image: [L2 cache for MySQL](https://www.percona.com/wp-content/uploads/2026/03/l2cache-thrp.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
