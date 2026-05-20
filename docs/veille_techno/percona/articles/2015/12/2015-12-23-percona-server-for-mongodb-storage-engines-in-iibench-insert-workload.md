---
title: Percona Server for MongoDB storage engines in iiBench insert workload
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-server-for-mongodb-storage-engines-in-iibench-insert-workload/
  post_id: 10343
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2015-12-23T17:39:10'
published_at_gmt: '2015-12-23T17:39:10'
modified_at: '2026-03-26T20:22:53'
modified_at_gmt: '2026-03-26T20:22:53'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MongoDB
- MySQL
category_slugs:
- mongodb
- mysql
tags:
- MongoDB
- storage engines
tag_slugs:
- mongodb
- storage-engines
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/shutterstock_245052700.jpg
image_count: 6
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona Server for MongoDB storage engines in iiBench insert workload

Source: [Percona Blog](https://www.percona.com/blog/percona-server-for-mongodb-storage-engines-in-iibench-insert-workload/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2015-12-23T17:39:10

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

We recently released the GA version of Percona Server for MongoDB, which comes with a variety of storage engines: RocksDB, PerconaFT and WiredTiger. Both RocksDB and PerconaFT are write-optimized engines, so I wanted to compare all engines in a workload oriented to data ingestions. For a benchmark I used iiBench-mongo (https://github.com/mdcallag/iibench-mongodb), and I inserted one billion … Continued

## Images et graphiques reperes

- featured / image: [Percona Server for MongoDB storage engines in iiBench insert workload](https://www.percona.com/wp-content/uploads/2026/03/shutterstock_245052700.jpg)
- content / image: [storage engines](https://www.percona.com/wp-content/uploads/2026/03/shutterstock_245052700-300x214.jpg)
- content / image: [engines-timeline](https://www.percona.com/wp-content/uploads/2026/03/engines-timeline.png)
- content / image: [wt-3.0](https://www.percona.com/wp-content/uploads/2026/03/wt-3.0.png)
- content / image: [rocks-perconaft-3.0](https://www.percona.com/wp-content/uploads/2026/03/rocks-perconaft-3.0.png)
- content / image: [rocks-3.0-dyn12M](https://www.percona.com/wp-content/uploads/2026/03/rocks-3.0-dyn12M.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
