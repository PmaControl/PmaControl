---
title: MySQL January 2026 Performance Review
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-january-2026-performance-review/
  post_id: 35601
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2026-01-23T13:31:47'
published_at_gmt: '2026-01-23T13:31:47'
modified_at: '2026-03-26T20:25:02'
modified_at_gmt: '2026-03-26T20:25:02'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MariaDB
- MySQL
matched_filters:
- category:mariadb:1281
- category:mysql:83
categories:
- Benchmarks
- MariaDB
- MySQL
category_slugs:
- benchmarks
- mariadb
- mysql
tags:
- benchmark
- Benchmarking
- MariaDB
- MySQL
- mysql-and-variants
- Performance
- TPCC
tag_slugs:
- benchmark
- benchmarking
- mariadb
- mysql
- mysql-and-variants
- performance
- tpcc
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-January-2026-Performance-Review.jpg
image_count: 13
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL January 2026 Performance Review

Source: [Percona Blog](https://www.percona.com/blog/mysql-january-2026-performance-review/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2026-01-23T13:31:47

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This article is focused on describing the latest performance benchmarking executed on the latest releases of Community MySQL, Percona Server for MySQL and MariaDB. In this set of tests I have used the machine described here. Assumptions There are many ways to run tests, and we know that results may vary depending on how you … Continued

## Structure detectee

- H3: Assumptions
- H3: What tests do we run?
- H3: Why do I (normally) only publish TPC-C tests?
- H2: Results
- H1: Conclusions

## Images et graphiques reperes

- featured / image: [MySQL January 2026 Performance Review](https://www.percona.com/wp-content/uploads/2026/03/MySQL-January-2026-Performance-Review.jpg)
- content / image: [run_tpcc_RepeatableRead_8-9-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/run_tpcc_RepeatableRead_8-9-scaled.png)
- content / image: [run_tpcc_ReadCommitted_8-9-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/run_tpcc_ReadCommitted_8-9-scaled.png)
- content / image: [run_tpcc_RepeatableRead-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/run_tpcc_RepeatableRead-scaled.png)
- content / image: [run_tpcc_ReadCommitted-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/run_tpcc_ReadCommitted-scaled.png)
- content / image: [select_run_range_all_small_Ordered_data_operations_s-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/select_run_range_all_small_Ordered_data_operations_s-scaled.png)
- content / image: [select_run_range_all_small_Unordered_data_operations_s-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/select_run_range_all_small_Unordered_data_operations_s-scaled.png)
- content / image: [write_run_inlist_update_hotspot-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/write_run_inlist_update_hotspot-scaled.png)
- content / image: [write_run_inlist_update-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/write_run_inlist_update-scaled.png)
- content / image: [write_run_insert_delete_multi-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/write_run_insert_delete_multi-scaled.png)
- content / image: [write_run_insert_delete_single-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/write_run_insert_delete_single-scaled.png)
- content / image: [write_run_rw_50__writes_notrx-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/write_run_rw_50__writes_notrx-scaled.png)
- content / image: [write_run_rw_50__writes_trx-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/write_run_rw_50__writes_trx-scaled.png)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
