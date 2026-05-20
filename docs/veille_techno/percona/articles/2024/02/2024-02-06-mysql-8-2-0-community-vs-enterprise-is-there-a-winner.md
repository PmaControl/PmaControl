---
title: MySQL 8.2.0 Community vs. Enterprise; Is There a Winner?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-8-2-0-community-vs-enterprise-is-there-a-winner/
  post_id: 28053
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2024-02-06T18:13:33'
published_at_gmt: '2024-02-06T18:13:33'
modified_at: '2026-03-26T20:26:39'
modified_at_gmt: '2026-03-26T20:26:39'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
categories:
- Benchmarks
- Insight for DBAs
- Insight for Developers
- MySQL
category_slugs:
- benchmarks
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- Benchmarking
- MySQL
- MySQL Enterprise
- mysql-and-variants
- Percona Server for MySQL
tag_slugs:
- benchmarking
- mysql
- mysql-enterprise
- mysql-and-variants
- percona-server
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.2.0-Community-vs.-Enterprise.jpg
image_count: 33
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL 8.2.0 Community vs. Enterprise; Is There a Winner?

Source: [Percona Blog](https://www.percona.com/blog/mysql-8-2-0-community-vs-enterprise-is-there-a-winner/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2024-02-06T18:13:33

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

To be honest, the comparison between the two MySQL distributions is not something that excited me a lot. Mainly because from my MySQL memories, I knew that there is not a real difference between the two distributions when talking about the code base. To my knowledge the differences in the enterprise version are in the … Continued

## Structure detectee

- H2: Test environment
- H2: The tests
- H2: Tests results
- H3: Sysbench reads first
- H3: TPC-c summary
- H2: Let us dig a bit!
- H3: select_run_inlist
- H3: select_run_inlist_hotspot
- H3: select_run_point_select
- H3: select_run_range_all
- H3: select_run_range_distinct
- H3: select_run_range_order
- H3: select_run_range_simple
- H3: select_run_range_sum
- H3: select_run_select_scan
- H3: Writes
- H3: write_run_write_all_with_trx
- H3: write_run_write_all_no_trx
- H2: Tpc-c like
- H2: Marco comment
- H3: Conclusions
- H4: References

## Images et graphiques reperes

- featured / image: [MySQL 8.2.0 Community vs. Enterprise; Is There a Winner?](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.2.0-Community-vs.-Enterprise.jpg)
- content / image: [animal-house-speech_2.jpg](https://www.percona.com/wp-content/uploads/2026/03/animal-house-speech_2.jpg)
- content / image: [MySQL 8.2.0 Community vs Enterprise](https://www.percona.com/wp-content/uploads/2026/03/sysbench_summary.jpg)
- content / image: [tpc_summary.jpg](https://www.percona.com/wp-content/uploads/2026/03/tpc_summary.jpg)
- content / image: [sysbench_select_small-1024x241.jpg](https://www.percona.com/wp-content/uploads/2026/03/sysbench_select_small-1024x241.jpg)
- content / image: [sysbench_select_large-1024x304.jpg](https://www.percona.com/wp-content/uploads/2026/03/sysbench_select_large-1024x304.jpg)
- content / image: [sysbench_writes_small-1024x308.jpg](https://www.percona.com/wp-content/uploads/2026/03/sysbench_writes_small-1024x308.jpg)
- content / image: [sysbench_writes_large.jpg](https://www.percona.com/wp-content/uploads/2026/03/sysbench_writes_large.jpg)
- content / image: [TPC-c summary](https://www.percona.com/wp-content/uploads/2026/03/tpc_details.jpg)
- content / image: [MySQL-8.2-Enterprise-VS-Community_2024_01_03_Small-select_run_inlist-reads-sec_25.jpg](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.2-Enterprise-VS-Community_2024_01_03_Small-select_run_inlist-reads-sec_25.jpg)
- content / image: [MySQL-8.2-Enterprise-VS-Community_2024_01_03_Large-select_run_inlist-reads-sec_25.jpg](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.2-Enterprise-VS-Community_2024_01_03_Large-select_run_inlist-reads-sec_25.jpg)
- content / image: [MySQL-8.2-Enterprise-VS-Community_2024_01_03_Small-select_run_inlist_hotspot-reads-sec_29.jpg](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.2-Enterprise-VS-Community_2024_01_03_Small-select_run_inlist_hotspot-reads-sec_29.jpg)
- content / image: [MySQL-8.2-Enterprise-VS-Community_2024_01_03_Large-select_run_inlist_hotspot-reads-sec_29.jpg](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.2-Enterprise-VS-Community_2024_01_03_Large-select_run_inlist_hotspot-reads-sec_29.jpg)
- content / image: [MySQL-8.2-Enterprise-VS-Community_2024_01_03_Small-select_run_point_select-reads-sec_33.jpg](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.2-Enterprise-VS-Community_2024_01_03_Small-select_run_point_select-reads-sec_33.jpg)
- content / image: [MySQL-8.2-Enterprise-VS-Community_2024_01_03_Large-select_run_point_select-reads-sec_33.jpg](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.2-Enterprise-VS-Community_2024_01_03_Large-select_run_point_select-reads-sec_33.jpg)
- content / image: [MySQL-8.2-Enterprise-VS-Community_2024_01_03_Small-select_run_range_all-reads-sec_3.jpg](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.2-Enterprise-VS-Community_2024_01_03_Small-select_run_range_all-reads-sec_3.jpg)
- content / image: [MySQL-8.2-Enterprise-VS-Community_2024_01_03_Large-select_run_range_all-reads-sec_3.jpg](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.2-Enterprise-VS-Community_2024_01_03_Large-select_run_range_all-reads-sec_3.jpg)
- content / image: [MySQL-8.2-Enterprise-VS-Community_2024_01_03_Small-select_run_range_distinct-reads-sec_5.jpg](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.2-Enterprise-VS-Community_2024_01_03_Small-select_run_range_distinct-reads-sec_5.jpg)
- content / image: [MySQL-8.2-Enterprise-VS-Community_2024_01_03_Large-select_run_range_distinct-reads-sec_5.jpg](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.2-Enterprise-VS-Community_2024_01_03_Large-select_run_range_distinct-reads-sec_5.jpg)
- content / image: [MySQL-8.2-Enterprise-VS-Community_2024_01_03_Small-select_run_range_order-reads-sec_11.jpg](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.2-Enterprise-VS-Community_2024_01_03_Small-select_run_range_order-reads-sec_11.jpg)
- content / image: [MySQL-8.2-Enterprise-VS-Community_2024_01_03_Large-select_run_range_order-reads-sec_11.jpg](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.2-Enterprise-VS-Community_2024_01_03_Large-select_run_range_order-reads-sec_11.jpg)
- content / image: [MySQL-8.2-Enterprise-VS-Community_2024_01_03_Small-select_run_range_simple-reads-sec_15.jpg](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.2-Enterprise-VS-Community_2024_01_03_Small-select_run_range_simple-reads-sec_15.jpg)
- content / image: [MySQL-8.2-Enterprise-VS-Community_2024_01_03_Large-select_run_range_simple-reads-sec_15.jpg](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.2-Enterprise-VS-Community_2024_01_03_Large-select_run_range_simple-reads-sec_15.jpg)
- content / image: [MySQL-8.2-Enterprise-VS-Community_2024_01_03_Small-select_run_range_sum-reads-sec_19.jpg](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.2-Enterprise-VS-Community_2024_01_03_Small-select_run_range_sum-reads-sec_19.jpg)
- content / image: [MySQL-8.2-Enterprise-VS-Community_2024_01_03_Large-select_run_range_sum-reads-sec_19.jpg](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.2-Enterprise-VS-Community_2024_01_03_Large-select_run_range_sum-reads-sec_19.jpg)
- content / image: [MySQL-8.2-Enterprise-VS-Community_2024_01_03_Small-select_run_select_scan-Execution-Time-sec-by-increasing-number-of-threads-lower-is-better_21.jpg](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.2-Enterprise-VS-Community_2024_01_03_Small-select_run_select_scan-Execution-Time-sec-by-increasing-number-of-threads-lower-is-better_21.jpg)
- content / image: [MySQL-8.2-Enterprise-VS-Community_2024_01_03_Large-select_run_select_scan-Execution-Time-sec-by-increasing-number-of-threads-lower-is-better_21.jpg](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.2-Enterprise-VS-Community_2024_01_03_Large-select_run_select_scan-Execution-Time-sec-by-increasing-number-of-threads-lower-is-better_21.jpg)
- content / image: [MySQL-8.2-Enterprise-VS-Community_2024_01_03_Small-write_run_write_all_with_trx_special-writes-sec_67.jpg](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.2-Enterprise-VS-Community_2024_01_03_Small-write_run_write_all_with_trx_special-writes-sec_67.jpg)
- content / image: [MySQL-8.2-Enterprise-VS-Community_2024_01_03_Large-write_run_write_all_with_trx-writes-sec_63.jpg](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.2-Enterprise-VS-Community_2024_01_03_Large-write_run_write_all_with_trx-writes-sec_63.jpg)
- content / image: [MySQL-8.2-Enterprise-VS-Community_2024_01_03_Small-write_run_write_all_no_trx-writes-sec_59.jpg](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.2-Enterprise-VS-Community_2024_01_03_Small-write_run_write_all_no_trx-writes-sec_59.jpg)
- content / image: [MySQL-8.2-Enterprise-VS-Community_2024_01_03_Large-write_run_write_all_no_trx-writes-sec_59.jpg](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.2-Enterprise-VS-Community_2024_01_03_Large-write_run_write_all_no_trx-writes-sec_59.jpg)
- content / image: [threads_2024_01_03_TPCC-Repeatable-Reads-writes-sec_9.jpg](https://www.percona.com/wp-content/uploads/2026/03/threads_2024_01_03_TPCC-Repeatable-Reads-writes-sec_9.jpg)
- content / image: [threads_2024_01_03_TPCC-Read-Committed-writes-sec_7.jpg](https://www.percona.com/wp-content/uploads/2026/03/threads_2024_01_03_TPCC-Read-Committed-writes-sec_7.jpg)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
