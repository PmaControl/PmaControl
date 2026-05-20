---
title: 2026 – MySQL Ecosystem Performance Benchmark Report
source:
  name: Percona Blog
  url: https://www.percona.com/blog/2026-mysql-ecosystem-performance-benchmark-report/
  post_id: 43316
source_author:
  name: Bogdan Degtyariov
  slug: bogdan-degtyariov
  url: https://www.percona.com/blog/author/bogdan-degtyariov/
  website: ''
published_at: '2026-03-26T21:28:55'
published_at_gmt: '2026-03-26T21:28:55'
modified_at: '2026-04-27T18:58:11'
modified_at_gmt: '2026-04-27T18:58:11'
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
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/04/img_7_1_1.png
image_count: 29
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# 2026 – MySQL Ecosystem Performance Benchmark Report

Source: [Percona Blog](https://www.percona.com/blog/2026-mysql-ecosystem-performance-benchmark-report/)

Auteur source: [Bogdan Degtyariov](https://www.percona.com/blog/author/bogdan-degtyariov/)

Publication: 2026-03-26T21:28:55

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MySQL Ecosystem Performance Benchmark Report 2026 Comparative Analysis of InnoDB-Compatible Engines — Percona Lab Results Repository: github.com/Percona-Lab-results/2026-interactive-metrics Interactive graphs available: Explore the full dataset dynamically — click any graph below to open the interactive version. OLTP Read-Write Local — interactive benchmark graph OLTP Read-Write Network — interactive benchmark graph OLTP Read-Only Local — interactive benchmark … Continued

## Structure detectee

- H1: MySQL Ecosystem Performance Benchmark Report 2026
- H4: Table of Contents
- H2: Executive Summary
- H2: 1. Benchmark Overview
- H3: 1.1 Purpose and Scope
- H3: 1.2 Engines Under Test
- H2: 2. Key Findings Summary
- H2: 3. Test Environment & Infrastructure
- H3: 3.1 Hardware & Host Configuration
- H3: 3.2 Containerization Strategy
- H3: 3.3 Buffer Pool Tiers
- H2: 4. Database Configuration
- H3: 4.1 Shared Base Configuration
- H3: 4.2 Version-Specific Configuration
- H2: 5. Benchmark Workload
- H3: 5.1 Warmup Protocol
- H3: 5.2 Measurement Run
- H2: 6. Metrics & Reporting
- H3: 6.1 Primary Metrics
- H3: 6.2 Derived Metrics
- H3: 6.3 Output Files
- H2: 7. Results:
- H3: 7.1 Read-Write Local
- H3: Table 7.1.1
- H3: Table 7.1.2
- H3: Table 7.1.3
- H3: 7.2. Results: Read-Only Local
- H3: Table 7.2.1
- H3: Table 7.2.2
- H3: Table 7.2.3
- H3: 7.3. Results: Read-Write Network
- H3: Table 7.3.1
- H3: Table 7.3.2
- H3: Percona 5.7 leads at 1,417 TPS (512 threads). MariaDB engines peak at 128 threads (~1,240 TPS). Percona 8.0 reaches only 797 TPS.
- H3: Table 7.3.3
- H2: 8. Version Progression
- H3: 8.1. Read-Write Local
- H3: 8.2. Read-Only Local
- H3: 8.3. Read-Write Network
- H2: 9. Methodology Notes & Caveats
- H3: 9.1 Limitations
- H3: 9.2 Reproducibility
- H3: 9.3 Configuration Philosophy
- H2: Side Note:

## Images et graphiques reperes

- content / image: [img_7_1_1.png](https://www.percona.com/wp-content/uploads/2026/04/img_7_1_1.png)
- content / image: [img_7_1_2.png](https://www.percona.com/wp-content/uploads/2026/04/img_7_1_2.png)
- content / image: [img_7_1_3.png](https://www.percona.com/wp-content/uploads/2026/04/img_7_1_3.png)
- content / image: [img_7_2_1.png](https://www.percona.com/wp-content/uploads/2026/04/img_7_2_1.png)
- content / image: [img_7_2_2.png](https://www.percona.com/wp-content/uploads/2026/04/img_7_2_2.png)
- content / image: [img_7_2_3.png](https://www.percona.com/wp-content/uploads/2026/04/img_7_2_3.png)
- content / image: [img_7_3_1.png](https://www.percona.com/wp-content/uploads/2026/04/img_7_3_1.png)
- content / image: [img_7_3_2.png](https://www.percona.com/wp-content/uploads/2026/04/img_7_3_2.png)
- content / image: [img_7_3_3.png](https://www.percona.com/wp-content/uploads/2026/04/img_7_3_3.png)
- content / image: [cmp_maria_114_vs_1011-e1775484951244.png](https://www.percona.com/wp-content/uploads/2026/04/cmp_maria_114_vs_1011-e1775484951244.png)
- content / image: [cmp_maria_121_vs_1011-e1775484997848.png](https://www.percona.com/wp-content/uploads/2026/04/cmp_maria_121_vs_1011-e1775484997848.png)
- content / image: [cmp_mysql_80_vs_57-e1775485083840.png](https://www.percona.com/wp-content/uploads/2026/04/cmp_mysql_80_vs_57-e1775485083840.png)
- content / image: [cmp_mysql_84_vs_57-e1775485142484.png](https://www.percona.com/wp-content/uploads/2026/04/cmp_mysql_84_vs_57-e1775485142484.png)
- content / image: [cmp_mysql_96_vs_57-e1775485203518.png](https://www.percona.com/wp-content/uploads/2026/04/cmp_mysql_96_vs_57-e1775485203518.png)
- content / image: [cmp_percona_80_vs_57-e1775485295865.png](https://www.percona.com/wp-content/uploads/2026/04/cmp_percona_80_vs_57-e1775485295865.png)
- content / image: [cmp_percona_84_vs_57-e1775485323923.png](https://www.percona.com/wp-content/uploads/2026/04/cmp_percona_84_vs_57-e1775485323923.png)
- content / image: [cmp_ro_maria_114_vs_1011-e1775485410729.png](https://www.percona.com/wp-content/uploads/2026/04/cmp_ro_maria_114_vs_1011-e1775485410729.png)
- content / image: [cmp_ro_maria_121_vs_1011-e1775485459463.png](https://www.percona.com/wp-content/uploads/2026/04/cmp_ro_maria_121_vs_1011-e1775485459463.png)
- content / image: [cmp_ro_mysql_80_vs_57-e1775485500459.png](https://www.percona.com/wp-content/uploads/2026/04/cmp_ro_mysql_80_vs_57-e1775485500459.png)
- content / image: [cmp_ro_mysql_84_vs_57-e1775485529599.png](https://www.percona.com/wp-content/uploads/2026/04/cmp_ro_mysql_84_vs_57-e1775485529599.png)
- content / image: [cmp_ro_mysql_96_vs_57-e1775485607749.png](https://www.percona.com/wp-content/uploads/2026/04/cmp_ro_mysql_96_vs_57-e1775485607749.png)
- content / image: [cmp_ro_percona_80_vs_57-e1775485634812.png](https://www.percona.com/wp-content/uploads/2026/04/cmp_ro_percona_80_vs_57-e1775485634812.png)
- content / image: [cmp_ro_percona_84_vs_57-e1775485661800.png](https://www.percona.com/wp-content/uploads/2026/04/cmp_ro_percona_84_vs_57-e1775485661800.png)
- content / image: [cmp_rwn_maria_114_vs_1011-e1775485685501.png](https://www.percona.com/wp-content/uploads/2026/04/cmp_rwn_maria_114_vs_1011-e1775485685501.png)
- content / image: [cmp_rwn_maria_121_vs_1011-e1775485809181.png](https://www.percona.com/wp-content/uploads/2026/04/cmp_rwn_maria_121_vs_1011-e1775485809181.png)
- content / image: [cmp_rwn_mysql_80_vs_57-e1775485836375.png](https://www.percona.com/wp-content/uploads/2026/04/cmp_rwn_mysql_80_vs_57-e1775485836375.png)
- content / image: [cmp_rwn_mysql_84_vs_57-e1775485862747.png](https://www.percona.com/wp-content/uploads/2026/04/cmp_rwn_mysql_84_vs_57-e1775485862747.png)
- content / image: [cmp_rwn_mysql_96_vs_57-e1775485886516.png](https://www.percona.com/wp-content/uploads/2026/04/cmp_rwn_mysql_96_vs_57-e1775485886516.png)
- content / image: [cmp_rwn_percona_84_vs_57-e1775485949901.png](https://www.percona.com/wp-content/uploads/2026/04/cmp_rwn_percona_84_vs_57-e1775485949901.png)
