---
title: Economical Comparison of AWS CPUs for MySQL (ARM vs Intel vs AMD)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/economical-comparison-of-aws-cpus-for-mysql-arm-vs-intel-vs-amd/
  post_id: 25414
source_author:
  name: Nik Krichko
  slug: mykyta-krychko
  url: https://www.percona.com/blog/author/mykyta-krychko/
  website: ''
published_at: '2022-02-16T13:45:49'
published_at_gmt: '2022-02-16T13:45:49'
modified_at: '2026-05-05T21:21:23'
modified_at_gmt: '2026-05-05T21:21:23'
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
- Cloud
- Insight for DBAs
- Insight for Developers
- MySQL
category_slugs:
- cloud
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- cloud
- MySQL
- mysql-and-variants
tag_slugs:
- cloud
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Economical-Comparison-of-AWS-CPUs-for-MySQL.png
image_count: 19
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Economical Comparison of AWS CPUs for MySQL (ARM vs Intel vs AMD)

Source: [Percona Blog](https://www.percona.com/blog/economical-comparison-of-aws-cpus-for-mysql-arm-vs-intel-vs-amd/)

Auteur source: [Nik Krichko](https://www.percona.com/blog/author/mykyta-krychko/)

Publication: 2022-02-16T13:45:49

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

It is always hard to select a CPU for your own purpose. You could waste hours reviewing different benchmarks, reviews, and bloggers, and in the end, we would limit all our requirements to performance and price. For performance measuring we already have some specific metrics (e.g. in MHz to some specific tool), however, for economic … Continued

## Structure detectee

- H2: Result Overview
- H2: Request Per Hour vs. Price For Equal Load
- H3: Plot 1.1. Number of requests per hour compared to EC2 instance price
- H2: Request Per Dollar vs. Price
- H3: Plot 2.1. Number of requests per one USD comparing with instance price for equal load
- H2: RATING
- H3: Plot 3.1. All EC2 sorted by approximate amount of transactions they could generate during one hour.
- H3: Plot 3.2. All EC2 sorted by approximate amount of transaction they could generate for one USD
- H2: How I Would Select a CPU For The Next Project
- H3: Plot 4.1. Cheapest EC2 instances that can handle 500 million transactions per hour
- H3: Plot 4.2. Cheapest EC2 instances that can handle 10,000 transactions per second
- H3: Plot 4.3. The cheapest EC2 instances for a particular load in transaction per hour depends on the number of vCPU
- H3: Plot 4.4. The cheapest EC2 instances for a particular load in transaction per second depends on the number of vCPU
- H3: Plot 4.4.1. Cheapest EC2 instance for required load with a load that was equal to the number of vCPU on an instance
- H2: Important Exceptions
- H3: Plot 5.1. Graviton behavior on higher load
- H3: Plot 5.1.1 Performance comparison of high-performance EC2 instances with an equal and double load
- H3: Plot 5.1.2 Advantage of high concurrency instances with double load over equal load in percents
- H3: Plot 5.2. Economical efficiency of 8 and 16 cores EC2
- H3: Plot 5.4. Economical efficiency of 12 core Intel vs 16 core Graviton and AMD
- H3: Final Thoughts

## Images et graphiques reperes

- featured / image: [Economical Comparison of AWS CPUs for MySQL (ARM vs Intel vs AMD)](https://www.percona.com/wp-content/uploads/2026/03/Economical-Comparison-of-AWS-CPUs-for-MySQL.png)
- content / image: [Economical Comparison of AWS CPUs for MySQL](https://www.percona.com/wp-content/uploads/2026/03/Economical-Comparison-of-AWS-CPUs-for-MySQL-300x157.png)
- content / image: [Number of requests per hour compared to EC2 instance price](https://www.percona.com/wp-content/uploads/2026/03/011_cpu_efficiency_per_1_usd-scaled.png)
- content / image: [012_cpu_efficiency_per_1_usd-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/012_cpu_efficiency_per_1_usd-scaled.png)
- content / image: [Number of requests per one USD comparing with instance price for equal load](https://www.percona.com/wp-content/uploads/2026/03/021_cpu_efficiency_per_1_usd-scaled.png)
- content / image: [Number of requests per one USD comparing with instance price for equal load with ec2 instance labels](https://www.percona.com/wp-content/uploads/2026/03/022_cpu_efficiency_per_1_usd_with_labels-scaled.png)
- content / image: [efficiency of CPUS MySQL AWS](https://www.percona.com/wp-content/uploads/2026/03/076_all_scenarios_simplify_perf_e-1024x768.png)
- content / image: [MYSQL costs](https://www.percona.com/wp-content/uploads/2026/03/075_all_scenarios_simplify_econ_e-scaled.png)
- content / image: [Cheapest EC2 instances that can handle 500 million transactions per hour](https://www.percona.com/wp-content/uploads/2026/03/911_hourly_load_500m-scaled.png)
- content / image: [Cheapest EC2 instances that can handle 10,000 transactions per second](https://www.percona.com/wp-content/uploads/2026/03/921_rps_load_10k-scaled.png)
- content / image: [cheapest EC2 instances for a particular load in transaction per hour depends on the number of vCPU](https://www.percona.com/wp-content/uploads/2026/03/931_horly_heatmap-scaled.png)
- content / image: [932_secondly_heatmap-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/932_secondly_heatmap-scaled.png)
- content / image: [Cheapest EC2 instance for required load with a load that was equal to the number of vCPU on an instance](https://www.percona.com/wp-content/uploads/2026/03/0441_all_scenarios_simplify_cheapest_rating-scaled.png)
- content / image: [0442_all_scenarios_simplify_HL_cheapest_rating-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/0442_all_scenarios_simplify_HL_cheapest_rating-scaled.png)
- content / image: [812_m5_scenarios_perf_e-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/812_m5_scenarios_perf_e-scaled.png)
- content / image: [high-performance EC2 instances with an equal and double load](https://www.percona.com/wp-content/uploads/2026/03/0512_HL_advantage_absolute_bar_plot-scaled.png)
- content / image: [0511_HL_advantage_relative_bar_plot-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/0511_HL_advantage_relative_bar_plot-scaled.png)
- content / image: [Economical efficiency of 8 and 16 cores EC2](https://www.percona.com/wp-content/uploads/2026/03/077_all_scenarios_simplify_HL_econ_e-1024x768.png)
- content / image: [Economical efficiency of 12 core intel vs 16 cores Graviton and AMD](https://www.percona.com/wp-content/uploads/2026/03/915_hourly_load_2200m-scaled.png)

## Auteur source

Performance engineer. More than 7 years of experience in analyzing and testing system performance. Achievements: he overloaded more than one system on the prod env., dozens on the test environment, burned several server processors during load testing, constantly clogged network traffic during load tests, blocked the work of other teams during their tests.
