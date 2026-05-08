---
title: 'TokuDB v7.5 Read Free Replication : The Benchmark'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/tokudb-v7-5-read-free-replication-the-benchmark/
  post_id: 9891
source_author:
  name: Tim.Callaghan
  slug: tim-callaghan
  url: https://www.percona.com/blog/author/tim-callaghan/
  website: ''
published_at: '2014-09-23T18:58:24'
published_at_gmt: '2014-09-23T18:58:24'
modified_at: '2026-03-25T18:29:33'
modified_at_gmt: '2026-03-25T18:29:33'
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
tags:
- Benchmarking
- Fractal Tree™ indexes
- MariaDB
- MySQL
- TokuDB
tag_slugs:
- benchmarking
- fractal-tree-indexes
- mariadb
- mysql
- tokudb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# TokuDB v7.5 Read Free Replication : The Benchmark

Source: [Percona Blog](https://www.percona.com/blog/tokudb-v7-5-read-free-replication-the-benchmark/)

Auteur source: [Tim.Callaghan](https://www.percona.com/blog/author/tim-callaghan/)

Publication: 2014-09-23T18:58:24

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

New to TokuDB® v7.5 is a feature we’re calling “Read Free Replication” (RFR). RFR allows TokuDB replication slaves to process insert, update, and delete statements with almost no read IO. As a result, the slave can easily keep up with the master (no lag) as well as brings all the read IO capacity of the … Continued

## Structure detectee

- H2: Read Free Replication: The Why and How
- H2: Read Free Replication: Sysbench Benchmark
- H2: To learn more about TokuDB v7.5:
