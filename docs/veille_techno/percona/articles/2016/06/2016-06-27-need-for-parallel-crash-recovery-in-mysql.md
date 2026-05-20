---
title: The need for parallel crash recovery in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/need-for-parallel-crash-recovery-in-mysql/
  post_id: 15369
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2016-06-27T18:14:40'
published_at_gmt: '2016-06-27T18:14:40'
modified_at: '2026-03-20T21:05:29'
modified_at_gmt: '2026-03-20T21:05:29'
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
- MySQL
category_slugs:
- mysql
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/parallel-crash-recovery-in-MySQL.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# The need for parallel crash recovery in MySQL

Source: [Percona Blog](https://www.percona.com/blog/need-for-parallel-crash-recovery-in-mysql/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2016-06-27T18:14:40

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog, I will discuss how parallel crash recovery in MySQL benefits several processes. I recently filed an Oracle feature request to make crash recovery faster by running in multiple threads. This might not seem very important, because MySQL does not crash that often. When it does crash, however, crash recovery can take 45 … Continued

## Images et graphiques reperes

- featured / image: [The need for parallel crash recovery in MySQL](https://www.percona.com/wp-content/uploads/2026/03/parallel-crash-recovery-in-MySQL.jpg)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
