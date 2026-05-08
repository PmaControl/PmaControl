---
title: The MySQL ARCHIVE storage engine – Alternatives
source:
  name: Percona Blog
  url: https://www.percona.com/blog/the-mysql-archive-storage-engine-alternatives/
  post_id: 7646
source_author:
  name: Przemysław Malkowski
  slug: przemek-malkowski
  url: https://www.percona.com/blog/author/przemek-malkowski/
  website: ''
published_at: '2014-02-24T16:01:02'
published_at_gmt: '2014-02-24T16:01:02'
modified_at: '2026-05-04T22:14:03'
modified_at_gmt: '2026-05-04T22:14:03'
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
category_slugs:
- benchmarks
- insight-for-dbas
- mysql
tags:
- archive storage engine
- bigdata
- InnoDB
- MyISAM
- Przemysław Malkowski
- TokuDB
tag_slugs:
- archive-storage-engine
- bigdata
- innodb
- myisam
- przemyslaw-malkowski
- tokudb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# The MySQL ARCHIVE storage engine – Alternatives

Source: [Percona Blog](https://www.percona.com/blog/the-mysql-archive-storage-engine-alternatives/)

Auteur source: [Przemysław Malkowski](https://www.percona.com/blog/author/przemek-malkowski/)

Publication: 2014-02-24T16:01:02

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In my previous post I pointed out that the existing ARCHIVE storage engine in MySQL may not be the one that will satisfy your needs when it comes to effectively storing large and/or old data. But are there any good alternatives? As the primary purpose of this engine is to store rarely accessed data in disk … Continued

## Structure detectee

- H3: ARCHIVE storage engine
- H3: TokuDB engine, default compression
- H3: TokuDB engine, highest compression
- H3: InnoDB engine, uncompressed
- H3: InnoDB engine, compressed with default page size (8kB)
- H3: InnoDB engine, compressed with 4kB page size
- H3: MyISAM engine, uncompressed
- H3: MyISAM engine, compressed (myisampack)
- H2: Summary

## Auteur source

Przemek joined Support Team at Percona in August 2012. Before that he spent over five years working for Wikia.com (Quantcast Top 50) as System Administrator where he was a key person responsible for seamless building up MySQL powered database infrastructure. Besides MySQL he worked on maintaining all other parts of LAMP stack, with main focus on automation, monitoring and backups.
