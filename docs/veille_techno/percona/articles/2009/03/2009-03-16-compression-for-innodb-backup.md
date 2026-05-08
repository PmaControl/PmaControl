---
title: Compression for InnoDB backup
source:
  name: Percona Blog
  url: https://www.percona.com/blog/compression-for-innodb-backup/
  post_id: 1862
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2009-03-16T22:34:26'
published_at_gmt: '2009-03-16T22:34:26'
modified_at: '2026-03-23T21:25:25'
modified_at_gmt: '2026-03-23T21:25:25'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- XtraBackup
matched_filters:
- search:percona-xtrabackup
- search:xtrabackup
categories:
- Benchmarks
- Insight for DBAs
category_slugs:
- benchmarks
- insight-for-dbas
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

# Compression for InnoDB backup

Source: [Percona Blog](https://www.percona.com/blog/compression-for-innodb-backup/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2009-03-16T22:34:26

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Playing with last version of xtrabackup and compress it I noticed that gzip is unacceptable slow for both compression and decompression operations. Actually Peter wrote about it some time ago, but I wanted to review that data having some new information. In current multi-core word the compression utility should utilize several CPU to speedup operation, … Continued

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
