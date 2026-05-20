---
title: Small innodb_page_size as a performance boost for SSD
source:
  name: Percona Blog
  url: https://www.percona.com/blog/small-innodb_page_size-performance-boost-ssd/
  post_id: 15524
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2016-08-11T00:13:44'
published_at_gmt: '2016-08-11T00:13:44'
modified_at: '2026-05-05T23:47:43'
modified_at_gmt: '2026-05-05T23:47:43'
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
- InnoDB
- innodb_page_size
- MySQL
- Performance
- SSD
tag_slugs:
- innodb
- innodb_page_size
- mysql
- performance
- ssd
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/performance-boost-for-SSD.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Small innodb_page_size as a performance boost for SSD

Source: [Percona Blog](https://www.percona.com/blog/small-innodb_page_size-performance-boost-ssd/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2016-08-11T00:13:44

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll discuss how a small innodb_page_size can create a performance boost for SSD. In my previous post Testing Samsung storage in tpcc-mysql benchmark of Percona Server I compared different Samsung devices. Most solid state drives (SSDs) use 4KiB as an internal page size, and the InnoDB default page size is 16KiB. … Continued

## Images et graphiques reperes

- featured / image: [Small innodb_page_size as a performance boost for SSD](https://www.percona.com/wp-content/uploads/2026/03/performance-boost-for-SSD.png)
- content / image: [performance boost for SSD](https://www.percona.com/wp-content/uploads/2026/03/unnamed-chunk-4-1-scaled.png)
- content / image: [performance boost for SSD](https://www.percona.com/wp-content/uploads/2026/03/unnamed-chunk-5-1-scaled.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
