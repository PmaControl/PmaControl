---
title: How many fsync / sec FusionIO can handle
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-many-fsync-sec-fusionio-can-handle/
  post_id: 2251
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2010-03-04T16:09:44'
published_at_gmt: '2010-03-04T16:09:44'
modified_at: '2026-04-28T21:08:23'
modified_at_gmt: '2026-04-28T21:08:23'
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

# How many fsync / sec FusionIO can handle

Source: [Percona Blog](https://www.percona.com/blog/how-many-fsync-sec-fusionio-can-handle/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2010-03-04T16:09:44

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I recently was asked how many fsync / sec ( and therefore durable transactions / sec) we can get on FusionIO card. It should be easy to test, let’s take sysbench fileio benchmark and run, the next command should make it: ./sysbench --test=fileio --file-num=1 --file-total-size=50G --file-fsync-all=on --file-test-mode=seqrewr --max-time=100 --file-block-size=4096 --max-requests=0 run 1 . / sysbench -- test = fileio -- file - num = 1 -- file - total - size = 50G -- file - fsync - all = on -- file - test - mode = seqrewr -- max - time = 100 -- file - block - size = 4096 -- max - requests = 0 run Operations performed: 0 Read, 922938 Write, 922938 Other = 1845876 Total Read 0b Written 3.5207Gb Total transferred 3.5207Gb (36.052Mb/sec) 9229.35 Requests/sec executed 1 2 3 Operations performed : 0 Read , 922938 Write , 922938 Other = 1845876 Total Read 0b Written 3.5207Gb Total transferred 3.5...

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
