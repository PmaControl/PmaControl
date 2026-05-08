---
title: Sakila, Where Are You Going?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/sakila-where-are-you-going/
  post_id: 28640
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2024-06-13T13:13:33'
published_at_gmt: '2024-06-13T13:13:33'
modified_at: '2026-03-26T20:26:18'
modified_at_gmt: '2026-03-26T20:26:18'
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
- Database Trends
- MySQL
category_slugs:
- benchmarks
- database-trends
- mysql
tags:
- AWS
- Google Cloud
- MySQL
- mysql-and-variants
- sakila
tag_slugs:
- aws
- google-cloud
- mysql
- mysql-and-variants
- sakila
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Sakila-MySQL.jpg
image_count: 17
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Sakila, Where Are You Going?

Source: [Percona Blog](https://www.percona.com/blog/sakila-where-are-you-going/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2024-06-13T13:13:33

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

At Percona, we monitor our users’ needs and try to accommodate them. One aspect we monitor is the MySQL version distribution/utilization. Observing that, we identified a very interesting trend: the lack of migration from 5.7 to 8.x, or better yet, the need of many to remain on 5.7. That observation has triggered several actions from … Continued

## Structure detectee

- H2: The tests
- H3: Assumptions
- H3: What tests do we run?
- H2: Results
- H3: Sysbench read and write tests
- H3: TPC-C tests
- H3: What if we compare it with Percona Server for MySQL and MariaDB?
- H4: TPC-C
- H2: What are these tests saying to us?
- H3: Considerations

## Images et graphiques reperes

- featured / image: [Sakila, Where Are You Going?](https://www.percona.com/wp-content/uploads/2026/03/Sakila-MySQL.jpg)
- content / image: [mysql_versions_adoption_trend.png](https://www.percona.com/wp-content/uploads/2026/03/mysql_versions_adoption_trend.png)
- content / image: [mysql_trend_default_rw_small-1024x427.png](https://www.percona.com/wp-content/uploads/2026/03/mysql_trend_default_rw_small-1024x427.png)
- content / image: [mysql_trend_optimized_rw_small_100_range-1024x427.png](https://www.percona.com/wp-content/uploads/2026/03/mysql_trend_optimized_rw_small_100_range-1024x427.png)
- content / image: [mysql_trend_default_rw_large_100_range-1024x427.png](https://www.percona.com/wp-content/uploads/2026/03/mysql_trend_default_rw_large_100_range-1024x427.png)
- content / image: [mysql_trend_optimized_rw_large_100_range-1024x427.png](https://www.percona.com/wp-content/uploads/2026/03/mysql_trend_optimized_rw_large_100_range-1024x427.png)
- content / image: [tpcc-RepeatableRead-with-optimized_only_mysql-1024x427.png](https://www.percona.com/wp-content/uploads/2026/03/tpcc-RepeatableRead-with-optimized_only_mysql-1024x427.png)
- content / image: [tpcc-ReadCommitted-with-optimized_only_mysql-1024x427.png](https://www.percona.com/wp-content/uploads/2026/03/tpcc-ReadCommitted-with-optimized_only_mysql-1024x427.png)
- content / image: [mysql_versions_compare_optimized_rw_small_100_range-1024x427.png](https://www.percona.com/wp-content/uploads/2026/03/mysql_versions_compare_optimized_rw_small_100_range-1024x427.png)
- content / image: [mysql_versions_compare_optimized_rw_large_100_range-1024x427.png](https://www.percona.com/wp-content/uploads/2026/03/mysql_versions_compare_optimized_rw_large_100_range-1024x427.png)
- content / image: [tpcc-RepeatableRead-optimized_all-1024x427.png](https://www.percona.com/wp-content/uploads/2026/03/tpcc-RepeatableRead-optimized_all-1024x427.png)
- content / image: [tpcc-ReadCommitted-optimized_all-1024x427.png](https://www.percona.com/wp-content/uploads/2026/03/tpcc-ReadCommitted-optimized_all-1024x427.png)
- content / image: [tpcc_trx_lost_rr-1024x744.jpg](https://www.percona.com/wp-content/uploads/2026/03/tpcc_trx_lost_rr-1024x744.jpg)
- content / image: [tpcc_trx_lost_rc-1024x702.jpg](https://www.percona.com/wp-content/uploads/2026/03/tpcc_trx_lost_rc-1024x702.jpg)
- content / image: [tpcc_trx_lost_rr-1-1024x743.jpg](https://www.percona.com/wp-content/uploads/2026/03/tpcc_trx_lost_rr-1-1024x743.jpg)
- content / image: [tpcc_trx_lost_rc-1-1024x703.jpg](https://www.percona.com/wp-content/uploads/2026/03/tpcc_trx_lost_rc-1-1024x703.jpg)
- content / image: [dolphin_heatwave3-300x300.jpeg](https://www.percona.com/wp-content/uploads/2026/03/dolphin_heatwave3-300x300.jpeg)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
