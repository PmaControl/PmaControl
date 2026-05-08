---
title: Benchmarking Percona Server TokuDB vs InnoDB
source:
  name: Percona Blog
  url: https://www.percona.com/blog/benchmarking-percona-server-tokudb-vs-innodb/
  post_id: 6905
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2013-05-07T15:34:07'
published_at_gmt: '2013-05-07T15:34:07'
modified_at: '2026-04-28T21:52:35'
modified_at_gmt: '2026-04-28T21:52:35'
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
tags:
- Benchmarking
- InnoDB
- Percona Server for MySQL
- TokuDB
tag_slugs:
- benchmarking
- innodb
- percona-server
- tokudb
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/TokuDB-1.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Benchmarking Percona Server TokuDB vs InnoDB

Source: [Percona Blog](https://www.percona.com/blog/benchmarking-percona-server-tokudb-vs-innodb/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2013-05-07T15:34:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

After compiling Percona Server with TokuDB, of course I wanted to compare InnoDB performance vs TokuDB. I have a particular workload I’m interested in testing – it is an insert-intensive workload (which is TokuDB’s strong suit) with some roll-up aggregation, which should produce updates in-place (I will use INSERT .. ON DUPLICATE KEY UPDATE statements … Continued

## Images et graphiques reperes

- featured / image: [Benchmarking Percona Server TokuDB vs InnoDB](https://www.percona.com/wp-content/uploads/2026/03/TokuDB-1.png)
- content / image: [TokuDB-2](https://www.percona.com/wp-content/uploads/2026/03/TokuDB-2.png)
- content / image: [TokuDB-3](https://www.percona.com/wp-content/uploads/2026/03/TokuDB-3.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
