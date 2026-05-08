---
title: MyRocks Performance
source:
  name: Percona Blog
  url: https://www.percona.com/blog/a-look-at-myrocks-performance/
  post_id: 18499
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2018-04-30T23:35:19'
published_at_gmt: '2018-04-30T23:35:19'
modified_at: '2026-05-05T20:20:16'
modified_at_gmt: '2026-05-05T20:20:16'
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
- Insight for DBAs
- MySQL
- Storage Engine
category_slugs:
- benchmarks
- insight-for-dbas
- mysql
- storage-engine
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MyRocks-Performance-small.png
image_count: 9
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MyRocks Performance

Source: [Percona Blog](https://www.percona.com/blog/a-look-at-myrocks-performance/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2018-04-30T23:35:19

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, I’ll look at MyRocks performance through some benchmark testing. As the MyRocks storage engine (based on the RocksDB key-value store http://rocksdb.org ) is now available as part of Percona Server for MySQL 5.7, I wanted to take a look at how it performs on a relatively high-end server and SSD storage. … Continued

## Structure detectee

- H2: IO and CPU usage
- H3: What about reads?
- H2: CPU usage
- H2: MyRocks directory size
- H3: Conclusion
- H2: Extras
- H3: Raw results, scripts, and config
- H2: MyRocks Performance Settings
- H3: InnoDB settings
- H3: Hardware spec
- H3: You May Also Like

## Images et graphiques reperes

- featured / image: [MyRocks Performance](https://www.percona.com/wp-content/uploads/2026/03/MyRocks-Performance-small.png)
- content / image: [MyRocks Performance](https://www.percona.com/wp-content/uploads/2026/03/MyRocks-Performance.png)
- content / image: [MyRocks Performance 2](https://www.percona.com/wp-content/uploads/2026/03/MyRocks-Performance-2.png)
- content / image: [MyRocks Performance 3](https://www.percona.com/wp-content/uploads/2026/03/MyRocks-Performance-3.png)
- content / image: [MyRocks Performance 4](https://www.percona.com/wp-content/uploads/2026/03/MyRocks-Performance-4.png)
- content / image: [MyRocks Performance 5](https://www.percona.com/wp-content/uploads/2026/03/MyRocks-Performance-5.png)
- content / image: [MyRocks Performance 6](https://www.percona.com/wp-content/uploads/2026/03/MyRocks-Performance-6.png)
- content / image: [MyRocks Performance 7](https://www.percona.com/wp-content/uploads/2026/03/MyRocks-Performance-7.png)
- content / image: [MyRocks Performance 8](https://www.percona.com/wp-content/uploads/2026/03/MyRocks-Performance-8.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
