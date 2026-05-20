---
title: Comparing Graviton (ARM) Performance to Intel and AMD for MySQL (Part 2)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/comparing-graviton-arm-performance-to-intel-and-amd-for-mysql-part-2/
  post_id: 25065
source_author:
  name: Nik Krichko
  slug: mykyta-krychko
  url: https://www.percona.com/blog/author/mykyta-krychko/
  website: ''
published_at: '2021-10-25T13:25:08'
published_at_gmt: '2021-10-25T13:25:08'
modified_at: '2026-05-05T16:41:33'
modified_at_gmt: '2026-05-05T16:41:33'
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
- search:pmm
categories:
- Benchmarks
- Insight for DBAs
- MySQL
category_slugs:
- benchmarks
- insight-for-dbas
- mysql
tags:
- AWS
- cloud
- Graviton
- Intel
- MySQL
- mysql-and-variants
tag_slugs:
- aws
- cloud
- graviton
- intel
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Comparing-Graviton-Performance-to-Intel-and-AMD-for-MySQL.png
image_count: 21
graph_or_chart_count: 15
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Comparing Graviton (ARM) Performance to Intel and AMD for MySQL (Part 2)

Source: [Percona Blog](https://www.percona.com/blog/comparing-graviton-arm-performance-to-intel-and-amd-for-mysql-part-2/)

Auteur source: [Nik Krichko](https://www.percona.com/blog/author/mykyta-krychko/)

Publication: 2021-10-25T13:25:08

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently we published the first part of research comparing Graviton (ARM) with AMD and Intel CPU on AWS. In the first part, we selected general-purpose EC2 instances with the same configurations (amount of vCPU). The main goal was to see the trend and make a general comparison of CPU types on the AWS platform only … Continued

## Structure detectee

- H2: Short Conclusion:
- H3: Details and Disclaimer:
- H2: Test Case:
- H2: Results:
- H2: Result for EC2 with 2, 4, and 8 vCPU:
- H2: Result for EC2 with 16, 48 and 64 vCPU:
- H2: OVERVIEW:
- H2: Whole Result Overview:
- H2: Final Thoughts
- H3: APPENDIX:
- H3: my.cnf:

## Images et graphiques reperes

- featured / image: [Comparing Graviton (ARM) Performance to Intel and AMD for MySQL (Part 2)](https://www.percona.com/wp-content/uploads/2026/03/Comparing-Graviton-Performance-to-Intel-and-AMD-for-MySQL.png)
- content / image: [Comparing Graviton Performance to Intel and AMD for MySQL](https://www.percona.com/wp-content/uploads/2026/03/Comparing-Graviton-Performance-to-Intel-and-AMD-for-MySQL-300x168.png)
- content / image: [pic 0.1. OS monitoring during all test stages (picture is for example)](https://www.percona.com/wp-content/uploads/2026/03/PMM_SMALL-1024x1004.jpeg)
  Caption: pic 0.1. OS monitoring during all test stages (picture is for example)
- content / graph_or_chart: [plot 1.1. Throughput (queries per second) for EC2 with 2, 4 and 8 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/11_small_workload_qps-scaled.png)
  Caption: plot 1.1. Throughput (queries per second) for EC2 with 2, 4 and 8 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / image: [plot 1.2. Latencies (95 percentile) during the test for EC2 with 2, 4 and 8 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/12_small_95th_latency-scaled.png)
  Caption: plot 1.2. Latencies (95 percentile) during the test for EC2 with 2, 4 and 8 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [plot 1.3.1 Percentage comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 2, 4 and 8 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/131_IS_relative_comparison-scaled.png)
  Caption: plot 1.3.1 Percentage comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 2, 4 and 8 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [plot 1.3.2 Percentage comparison Graviton and AMD CPU in throughput (queries per second) for EC2 with 2, 4 and 8 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/132_AS_relative_comparison-scaled.png)
  Caption: plot 1.3.2 Percentage comparison Graviton and AMD CPU in throughput (queries per second) for EC2 with 2, 4 and 8 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [plot 1.4.1. Numbers comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 2, 4 and 8 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/141_abs_com_s-scaled.png)
  Caption: plot 1.4.1. Numbers comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 2, 4 and 8 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [plot 1.4.2. Numbers comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 2, 4 and 8 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/142_abs_com_s-scaled.png)
  Caption: plot 1.4.2. Numbers comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 2, 4 and 8 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [plot 2.1. Throughput (queries per second) for EC2 with 16, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/21_ML_workload_qps-scaled.png)
  Caption: plot 2.1. Throughput (queries per second) for EC2 with 16, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / image: [plot 2.2. Latencies (95 percentile) during the test for EC2 with 16, 48 and 64 vCPU for scenarios with 1,2 4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/22_ML_95th_latency-scaled.png)
  Caption: plot 2.2. Latencies (95 percentile) during the test for EC2 with 16, 48 and 64 vCPU for scenarios with 1,2 4,8,16,32,64,128 threads
- content / graph_or_chart: [plot 2.3.1 Percentage comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 16, 48 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/231_IML_relative_comparison-scaled.png)
  Caption: plot 2.3.1 Percentage comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 16, 48 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [plot 2.3.2 Percentage comparison Graviton and AMD CPU in throughput (queries per second) for EC2 with 16, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/232_AML_relative_comparison-scaled.png)
  Caption: plot 2.3.2 Percentage comparison Graviton and AMD CPU in throughput (queries per second) for EC2 with 16, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [plot 2.4.1. Numbers comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 16, 48 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/241_abs_com_ml-scaled.png)
  Caption: plot 2.4.1. Numbers comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 16, 48 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [plot 2.4.2. Numbers comparison Graviton and AMD CPU in throughput (queries per second) for EC2 with 16, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/242_abs_com_ml-scaled.png)
  Caption: plot 2.4.2. Numbers comparison Graviton and AMD CPU in throughput (queries per second) for EC2 with 16, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [plot 3.1. Throughput (queries per second) for EC2 with 2, 4, 8, 16, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/41_overview_workload_qps-scaled.png)
  Caption: plot 3.1. Throughput (queries per second) for EC2 with 2, 4, 8, 16, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / image: [plot 3.2. Latencies (95 percentile) during the test for EC2 with 2, 4, 8, 16, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/42_overview_95th_percentile-scaled.png)
  Caption: plot 3.2. Latencies (95 percentile) during the test for EC2 with 2, 4, 8, 16, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [plot 3.3.1. Percentage comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 2, 4, 8, 16 and 48 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/431_rel_com_overview_intel-scaled.png)
  Caption: plot 3.3.1. Percentage comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 2, 4, 8, 16 and 48 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [plot 3.3.2. Percentage comparison Graviton and AMD CPU in throughput (queries per second) for EC2 with 2, 4, 8, 16, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/432_rel_com_overview_AMD-scaled.png)
  Caption: plot 3.3.2. Percentage comparison Graviton and AMD CPU in throughput (queries per second) for EC2 with 2, 4, 8, 16, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [plot 3.4.1. Numbers comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 2, 4, 8, 16 AND 48 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/441_abs_com_overview_intel-scaled.png)
  Caption: plot 3.4.1. Numbers comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 2, 4, 8, 16 AND 48 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [plot 3.4.2. Numbers comparison Graviton and AMD CPU in throughput (queries per second) for EC2 with 2, 4, 8, 16, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/442_abs_com_overview_amd-scaled.png)
  Caption: plot 3.4.2. Numbers comparison Graviton and AMD CPU in throughput (queries per second) for EC2 with 2, 4, 8, 16, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads

## Auteur source

Performance engineer. More than 7 years of experience in analyzing and testing system performance. Achievements: he overloaded more than one system on the prod env., dozens on the test environment, burned several server processors during load testing, constantly clogged network traffic during load tests, blocked the work of other teams during their tests.
