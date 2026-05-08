---
title: Measuring Percona Server for MySQL On-Disk Decryption Overhead
source:
  name: Percona Blog
  url: https://www.percona.com/blog/measuring-percona-server-for-mysql-on-disk-decryption-overhead/
  post_id: 20046
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2019-02-22T12:38:31'
published_at_gmt: '2019-02-22T12:38:31'
modified_at: '2026-03-20T22:14:11'
modified_at_gmt: '2026-03-20T22:14:11'
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
- encryption
- io overhead
- Performance
tag_slugs:
- encryption
- io-overhead
- performance
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/benchmark-heavy-IO-percona-server-for-mysql-8-encryption.png
image_count: 3
graph_or_chart_count: 2
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Measuring Percona Server for MySQL On-Disk Decryption Overhead

Source: [Percona Blog](https://www.percona.com/blog/measuring-percona-server-for-mysql-on-disk-decryption-overhead/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2019-02-22T12:38:31

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona Server for MySQL 8.0 comes with enterprise grade total data encryption features. However, there is always the question of how much overhead – or performance penalty – comes with the data decryption. As we saw in my networking performance post, SSL under high concurrency might be problematic. Is this the case for data decryption? … Continued

## Structure detectee

- H2: Benchmark N1, heavy read IO
- H2: Benchmark N2, data in memory, no read IO
- H2: Observations

## Images et graphiques reperes

- featured / graph_or_chart: [Measuring Percona Server for MySQL On-Disk Decryption Overhead](https://www.percona.com/wp-content/uploads/2026/03/benchmark-heavy-IO-percona-server-for-mysql-8-encryption.png)
- content / image: [MySQL decryption schematic](https://www.percona.com/wp-content/uploads/2026/03/MySQL-decryption-schematic.png)
- content / graph_or_chart: [benchmark data in memory percona server for mysql 8 encryption](https://www.percona.com/wp-content/uploads/2026/03/benchmark-data-in-memory-percona-server-for-mysql-8-encryption.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
