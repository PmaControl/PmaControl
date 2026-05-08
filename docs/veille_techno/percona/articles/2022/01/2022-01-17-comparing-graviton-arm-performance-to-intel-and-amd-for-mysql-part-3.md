---
title: Comparing Graviton (ARM) Performance to Intel and AMD for MySQL (Part 3)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/comparing-graviton-arm-performance-to-intel-and-amd-for-mysql-part-3/
  post_id: 25294
source_author:
  name: Nik Krichko
  slug: mykyta-krychko
  url: https://www.percona.com/blog/author/mykyta-krychko/
  website: ''
published_at: '2022-01-17T14:30:58'
published_at_gmt: '2022-01-17T14:30:58'
modified_at: '2026-05-05T16:42:02'
modified_at_gmt: '2026-05-05T16:42:02'
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
- Cloud
- MySQL
category_slugs:
- benchmarks
- cloud
- mysql
tags:
- ARM
- AWS
- Graviton
- MySQL
- mysql-and-variants
- sysbench
tag_slugs:
- arm
- aws
- graviton
- mysql
- mysql-and-variants
- sysbench
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Comparing-Graviton-ARM-Performance-to-Intel-and-AMD-for-MySQL-1.png
image_count: 31
graph_or_chart_count: 23
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Comparing Graviton (ARM) Performance to Intel and AMD for MySQL (Part 3)

Source: [Percona Blog](https://www.percona.com/blog/comparing-graviton-arm-performance-to-intel-and-amd-for-mysql-part-3/)

Auteur source: [Nik Krichko](https://www.percona.com/blog/author/mykyta-krychko/)

Publication: 2022-01-17T14:30:58

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently we published the first part (m5, m5a, m6g) and the second part (C5, C5a, C6g) of research regarding comparing Graviton ARM with AMD and Intel CPU on AWS. We selected general-purpose EC2 instances with the same configurations (amount of vCPU in the first part). In the second part, we compared compute-optimized EC2 instances with … Continued

## Structure detectee

- H2: Short Conclusion:
- H2: Details, or How We Got Our Short Conclusion:
- H2: Test Case:
- H2: Results:
- H2: Result for EC2 with 2, 4, and 8 vCPU:
- H2: Result for EC2 with 16 and 32 vCPU:
- H2: Result for EC2 with 48 and 64 vCPU:
- H2: Full Result Overview:
- H3: Final Thoughts
- H3: List of EC2 used in research:
- H3: my.cnf:

## Images et graphiques reperes

- featured / image: [Comparing Graviton (ARM) Performance to Intel and AMD for MySQL (Part 3)](https://www.percona.com/wp-content/uploads/2026/03/Comparing-Graviton-ARM-Performance-to-Intel-and-AMD-for-MySQL-1.png)
- content / image: [Comparing Graviton (ARM) Performance to Intel and AMD for MySQL](https://www.percona.com/wp-content/uploads/2026/03/Comparing-Graviton-ARM-Performance-to-Intel-and-AMD-for-MySQL-1-300x168.png)
- content / image: [perona monitoring and management](https://www.percona.com/wp-content/uploads/2026/03/PMM_SMALL-1024x1004.jpeg)
  Caption: pic 0.1. OS monitoring during all test stages
- content / graph_or_chart: [Result for EC2 with 2, 4, and 8 vCPU](https://www.percona.com/wp-content/uploads/2026/03/011_workload_qps_small-scaled.png)
  Caption: Plot 1.1. Throughput (queries per second) for EC2 with 2, 4 and 8 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / image: [Plot 1.2. Latencies (95 percentile) during the test for EC2 with 2, 4 and 8 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/012_95th_latency_small-scaled.png)
  Caption: Plot 1.2. Latencies (95 percentile) during the test for EC2 with 2, 4 and 8 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [Plot 1.3.1 Percentage comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 2, 4 and 8 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/013_relative_comparison_intel_small-scaled.png)
  Caption: Plot 1.3.1 Percentage comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 2, 4 and 8 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [Plot 1.3.2 Percentage comparison Graviton and AMD CPU in throughput (queries per second) for EC2 with 2, 4 and 8 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/014_relative_comparison_AMD_small-scaled.png)
  Caption: Plot 1.3.2 Percentage comparison Graviton and AMD CPU in throughput (queries per second) for EC2 with 2, 4 and 8 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [Plot 1.4.1. Numbers comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 2, 4 and 8 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/016_absolute_comparison_intel_small-scaled.png)
  Caption: Plot 1.4.1. Numbers comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 2, 4 and 8 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [Plot 1.4.2. Numbers comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 2, 4 and 8 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/017_absolute_comparison_AMD_small-scaled.png)
  Caption: Plot 1.4.2. Numbers comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 2, 4 and 8 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [Result for EC2 with 16 and 32 vCPU](https://www.percona.com/wp-content/uploads/2026/03/021_workload_qps_medium-scaled.png)
  Caption: Plot 2.1. Throughput (queries per second) for EC2 with 16 and 32 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / image: [Plot 2.2. Latencies (95 percentile) during the test for EC2 with 16 and 32 vCPU for scenarios with 1,2 4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/022_95th_latency_medium-scaled.png)
  Caption: Plot 2.2. Latencies (95 percentile) during the test for EC2 with 16 and 32 vCPU for scenarios with 1,2 4,8,16,32,64,128 threads
- content / graph_or_chart: [Plot 2.3.1 Percentage comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 16 and 32 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/023_relative_comparison_intel_medium-scaled.png)
  Caption: Plot 2.3.1 Percentage comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 16 and 32 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [Plot 2.3.2 Percentage comparison Graviton and AMD CPU in throughput (queries per second) for EC2 with 16 and 32 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/024_relative_comparison_AMD_medium-scaled.png)
  Caption: Plot 2.3.2 Percentage comparison Graviton and AMD CPU in throughput (queries per second) for EC2 with 16 and 32 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [Plot 2.4.1. Numbers comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 16 and 32 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/026_absolute_comparison_intel_medium-scaled.png)
  Caption: Plot 2.4.1. Numbers comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 16 and 32 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [Plot 2.4.2. Numbers comparison Graviton and AMD CPU in throughput (queries per second) for EC2 with 16 and 32 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/027_absolute_comparison_AMD_medium-scaled.png)
  Caption: Plot 2.4.2. Numbers comparison Graviton and AMD CPU in throughput (queries per second) for EC2 with 16 and 32 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [Result for EC2 with 48 and 64 vCPU](https://www.percona.com/wp-content/uploads/2026/03/031_workload_qps-scaled.png)
  Caption: Plot 3.1. Throughput (queries per second) for EC2 with 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / image: [Plot 3.2. Latencies (95 percentile) during the test for EC2 with 48 and 64 vCPU for scenarios with 1,2 4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/032_95th_latency-scaled.png)
  Caption: Plot 3.2. Latencies (95 percentile) during the test for EC2 with 48 and 64 vCPU for scenarios with 1,2 4,8,16,32,64,128 threads
- content / graph_or_chart: [Plot 3.3.1 Percentage comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/033_relative_comparison_intel_large-scaled.png)
  Caption: Plot 3.3.1 Percentage comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [Plot 3.3.2 Percentage comparison Graviton and AMD CPU in throughput (queries per second) for EC2 with 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/034_relative_comparison_AMD_large-scaled.png)
  Caption: Plot 3.3.2 Percentage comparison Graviton and AMD CPU in throughput (queries per second) for EC2 with 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [Plot 3.4.1. Numbers comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/036_absolute_comparison_intel_large-scaled.png)
  Caption: Plot 3.4.1. Numbers comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [Plot 3.4.2. Numbers comparison Graviton and AMD CPU in throughput (queries per second) for EC2 with 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/037_absolute_comparison_AMD_large-scaled.png)
  Caption: Plot 3.4.2. Numbers comparison Graviton and AMD CPU in throughput (queries per second) for EC2 with 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [Plot 4.1.1. Throughput (queries per second) – bar plot for EC2 with 2, 4, 8, 16, 32, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/061_workload_qps_overview-scaled.png)
  Caption: Plot 4.1.1. Throughput (queries per second) – bar plot for EC2 with 2, 4, 8, 16, 32, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [Plot 4.1.2. Throughput (queries per second) – line plot for EC2 with 2, 4, 8, 16, 32, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/071_throughput_overview_line.png-scaled.jpg)
  Caption: Plot 4.1.2. Throughput (queries per second) – line plot for EC2 with 2, 4, 8, 16, 32, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / image: [Plot 4.2.1. Latencies (95 percentile) during the test – bar plot for EC2 with 2, 4, 8, 16, 32, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/062_95th_percentile_overview-scaled.png)
  Caption: Plot 4.2.1. Latencies (95 percentile) during the test – bar plot for EC2 with 2, 4, 8, 16, 32, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / image: [Plot 4.2.2. Latencies (95 percentile) during the test – line plot for EC2 with 2, 4, 8, 16, 32, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/072_latency_p95_overview_line-scaled.png)
  Caption: Plot 4.2.2. Latencies (95 percentile) during the test – line plot for EC2 with 2, 4, 8, 16, 32, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [Plot 4.3.1 Percentage comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 2, 4, 8, 16, 32, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/065_relative_comparison_overview_intel-scaled.png)
  Caption: Plot 4.3.1 Percentage comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 2, 4, 8, 16, 32, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [Plot 4.3.2 Percentage comparison Graviton and AMD CPU in throughput (queries per second) for EC2 with 2, 4, 8, 16, 32, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/066_relative_comparison_overview_AMD-scaled.png)
  Caption: Plot 4.3.2 Percentage comparison Graviton and AMD CPU in throughput (queries per second) for EC2 with 2, 4, 8, 16, 32, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [Plot 4.4.1. Numbers comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 2, 4, 8, 16, 32, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/067_absolute_comparison_overview_intel-scaled.png)
  Caption: Plot 4.4.1. Numbers comparison Graviton and Intel CPU in throughput (queries per second) for EC2 with 2, 4, 8, 16, 32, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [Plot 4.4.2. Numbers comparison Graviton and AMD CPU in throughput (queries per second) for EC2 with 2, 4, 8, 16, 32, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/068_absolute_comparison_overview_amd-scaled.png)
  Caption: Plot 4.4.2. Numbers comparison Graviton and AMD CPU in throughput (queries per second) for EC2 with 2, 4, 8, 16, 32, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [Plot 4.5.1. Percentage comparison INTEL and AMD CPU in throughput (queries per second) for EC2 with 2, 4, 8, 16, 32, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/0691_relative_comparison_overview_intel_amd-scaled.png)
  Caption: Plot 4.5.1. Percentage comparison INTEL and AMD CPU in throughput (queries per second) for EC2 with 2, 4, 8, 16, 32, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads
- content / graph_or_chart: [Plot 4.5.2. Numbers comparison INTEL and AMD CPU in throughput (queries per second) for EC2 with 2, 4, 8, 16, 32, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads](https://www.percona.com/wp-content/uploads/2026/03/0692_absolute_comparison_overview_intel_amd-scaled.png)
  Caption: Plot 4.5.2. Numbers comparison INTEL and AMD CPU in throughput (queries per second) for EC2 with 2, 4, 8, 16, 32, 48 and 64 vCPU for scenarios with 1,2,4,8,16,32,64,128 threads

## Auteur source

Performance engineer. More than 7 years of experience in analyzing and testing system performance. Achievements: he overloaded more than one system on the prod env., dozens on the test environment, burned several server processors during load testing, constantly clogged network traffic during load tests, blocked the work of other teams during their tests.
