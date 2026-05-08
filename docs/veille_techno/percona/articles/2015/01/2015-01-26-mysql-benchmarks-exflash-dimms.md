---
title: MySQL benchmarks on eXFlash DIMMs
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-benchmarks-exflash-dimms/
  post_id: 8958
source_author:
  name: Peter Boros
  slug: peter-boros
  url: https://www.percona.com/blog/author/peter-boros/
  website: ''
published_at: '2015-01-26T13:00:37'
published_at_gmt: '2015-01-26T13:00:37'
modified_at: '2026-03-25T17:49:31'
modified_at_gmt: '2026-03-25T17:49:31'
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
- eXFlash DIMMs
- MySQL benchmarks
- Peter Boros
- Primary
- sysbench
tag_slugs:
- exflash-dimms
- mysql-benchmarks
- peter-boros
- primary
- sysbench
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/exflash_sysbench_oltp_tp_partial.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL benchmarks on eXFlash DIMMs

Source: [Percona Blog](https://www.percona.com/blog/mysql-benchmarks-exflash-dimms/)

Auteur source: [Peter Boros](https://www.percona.com/blog/author/peter-boros/)

Publication: 2015-01-26T13:00:37

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we will discuss MySQL performance on eXFlash DIMMs. Earlier we measured the IO performance of these storage devices with sysbench fileio. Environment The benchmarking environment was the same as the one we did sysbench fileio in. CPU: 2x Intel Xeon E5-2690 (hyper threading enabled)FusionIO driver version: 3.2.6 build 1212Operating system: CentOS … Continued

## Structure detectee

- H1: Environment
- H1: Sysbench OLTP write workload
- H2: Sysbench OLTP throughput
- H2: Sysbench OLTP response time
- H2: CPU idle percentage

## Images et graphiques reperes

- featured / image: [MySQL benchmarks on eXFlash DIMMs](https://www.percona.com/wp-content/uploads/2026/03/exflash_sysbench_oltp_tp_partial.png)

## Auteur source

Peter is a Principal Architect at Percona's European consulting team, his special interests are performance tuning and automation for large scale systems. Before Percona, he worked at Zuora, Dropbox, and Sun microsystems, also taught MySQL courses for Oracle University. He currently lives in Debrecen, Hungary with his wife and kids.
