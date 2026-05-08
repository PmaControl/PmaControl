---
title: Aligning IO on a hard disk RAID – the Benchmarks
source:
  name: Percona Blog
  url: https://www.percona.com/blog/aligning-io-on-a-hard-disk-raid-the-benchmarks/
  post_id: 2884
source_author:
  name: Aurimas Mikalauskas
  slug: inner
  url: https://www.percona.com/blog/author/inner/
  website: http://www.speedemy.com/
published_at: '2011-06-09T07:00:01'
published_at_gmt: '2011-06-09T07:00:01'
modified_at: '2026-04-28T21:26:57'
modified_at_gmt: '2026-04-28T21:26:57'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
category_slugs:
- mysql
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/chart_1.png
image_count: 9
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Aligning IO on a hard disk RAID – the Benchmarks

Source: [Percona Blog](https://www.percona.com/blog/aligning-io-on-a-hard-disk-raid-the-benchmarks/)

Auteur source: [Aurimas Mikalauskas](https://www.percona.com/blog/author/inner/)

Publication: 2011-06-09T07:00:01

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In the first part of this article I have showed how I align IO, now I want to share results of the benchmark that I have been running to see how much benefit can we get from a proper IO alignment on a 4-disk RAID1+0 with 64k stripe element. I haven’t been running any benchmarks … Continued

## Structure detectee

- H2: The environment
- H2: File IO benchmark
- H2: OLTP benchmark
- H2: Benchmark scenarios
- H2: Benchmark results
- H3: File IO benchmark results
- H3: OLTP benchmark results
- H2: Summary

## Images et graphiques reperes

- featured / image: [Aligning IO on a hard disk RAID – the Benchmarks](https://www.percona.com/wp-content/uploads/2026/03/chart_1.png)
- content / image: [:)](https://www.percona.com/wp-content/uploads/2026/03/icon_smile.gif)
- content / image: [1GB seqwr WB cache](https://www.percona.com/wp-content/uploads/2026/03/chart_2.png)
- content / image: [1GB seqwr WT vs WB](https://www.percona.com/wp-content/uploads/2026/03/chart_3.png)
- content / image: [16GB rndrd](https://www.percona.com/wp-content/uploads/2026/03/chart_4.png)
- content / image: [16 rndwr WT cache](https://www.percona.com/wp-content/uploads/2026/03/chart_5.png)
- content / image: [16 rndwr WB cache](https://www.percona.com/wp-content/uploads/2026/03/chart_6.png)
- content / image: [sysbench OLTP 20M rows, WT cache](https://www.percona.com/wp-content/uploads/2026/03/chart_7.png)
- content / image: [sysbench OLTP 20M rows, WB cache](https://www.percona.com/wp-content/uploads/2026/03/chart_8.png)

## Auteur source

Aurimas joined Percona as a first MySQL Performance Consultant in 2006, a few months after Peter and Vadim founded the company. His primary focus is on high performance, but he also specializes in full text search, high availability, content caching techniques and MySQL data recovery.
