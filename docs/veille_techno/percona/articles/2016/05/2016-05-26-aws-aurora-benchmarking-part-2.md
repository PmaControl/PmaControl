---
title: AWS Aurora Benchmarking part 2
source:
  name: Percona Blog
  url: https://www.percona.com/blog/aws-aurora-benchmarking-part-2/
  post_id: 15113
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2016-05-26T12:45:10'
published_at_gmt: '2016-05-26T12:45:10'
modified_at: '2026-05-05T20:11:45'
modified_at_gmt: '2026-05-05T20:11:45'
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
- Aurora
- AWS
- Benchmarking
- pxc
tag_slugs:
- aurora
- aws
- benchmarking
- pxc
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/AWS-Aurora-Benchmarking.jpg
image_count: 45
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# AWS Aurora Benchmarking part 2

Source: [Percona Blog](https://www.percona.com/blog/aws-aurora-benchmarking-part-2/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2016-05-26T12:45:10

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Some time ago, I published the article on AWS Aurora Benchmarking (AWS Aurora Benchmarking – Blast or Splash?), in which I analyzed the behavior of different solutions using synchronous replication in AWS environment. This blog follows up with some of the comments and suggestions I received regarding that post from the community and Amazon engineers. … Continued

## Structure detectee

- H2: Why new tests?
- H2: Why so many (different) tests?
- H2: About the tests
- H2: Tests run
- H3: Performance and load stress
- H2: The machines
- H2: A note
- H2: The layout
- H2: Two words about Aurora first
- H2: The IO activity
- H2: Thread pooling
- H2: The results
- H2: First Test: IIBench
- H2: Second Test: Application Ingest
- H2: Third Test: OLTP Application
- H2: Fourth Test: TPCC-mysql
- H2: Fifth Test: Sysbench
- H2: HA availability
- H2: Fail-over time
- H2: Execution latency
- H2: Conclusions
- H2: High Availability
- H2: Performance
- H2: General Comments on Aurora
- H2: About Cost

## Images et graphiques reperes

- featured / image: [AWS Aurora Benchmarking part 2](https://www.percona.com/wp-content/uploads/2026/03/AWS-Aurora-Benchmarking.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-certification.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/hootsuite_MySQL_HA_failover-POC-architecture--scaled.png)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2016-05-12-at-9.39.17-AM.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2016-05-12-at-1.34.10-PM.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2016-05-12-at-9.41.55-AM.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2016-05-12-at-9.42.13-AM.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/iibench_exectime_old.png)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/iibench_exectime_new.png)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/iibench_aurora_handlers.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/iibench_pxc_handlers.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/iibench_aurora_page.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/iibench_pxc_page.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/app_ingest_exec_time_old.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/app_ingest_exec_time_new.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/app_ingest_rows_inserted_old.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/app_ingest_rows_inserted_new.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/app_ingest_pxc_handlers.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/app_ingest_aurora_handlers.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/app_ingest_pxc_connections.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/app_ingest_aurora_connections.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/app_ingest_aurora_rows.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/app_ingest_pxc_rows.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/app_oltp_exec_time_old.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/app_oltp_exec_time_new.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/commands_OLTP.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/commit_OLTP.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/tpcc_old-1024x671.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/tpcc_new-scaled.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/tppc_aurora_com-1024x512.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/tppc_pxc_com-1024x512.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/tppc_aurora_files-1024x512.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/tppc_pxc_files-1024x512.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/tppc_aurora_handlers.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/tppc_pxc_handlers.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/tppc_aurora_locks.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/tppc_pxc_locks.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/tppc_response_time.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/sys_bench_high_threads_trnsactions.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/sys_bench_high_threads_latency.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/sys_bench_high_threads_Handlers.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/sys_bench_high_threads_Handlers_read.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/ha_time.png)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/ha_latency_old.jpg)
- content / image: [AWS Aurora Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/ha_latency_new.jpg)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
