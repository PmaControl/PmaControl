---
title: Prometheus 2 Times Series Storage Performance Analyses
source:
  name: Percona Blog
  url: https://www.percona.com/blog/prometheus-2-times-series-storage-performance-analyses/
  post_id: 19341
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2018-09-20T16:07:20'
published_at_gmt: '2018-09-20T16:07:20'
modified_at: '2026-05-05T19:22:12'
modified_at_gmt: '2026-05-05T19:22:12'
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
- MySQL
- Percona Software
category_slugs:
- benchmarks
- mysql
- percona-software
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/cpu-saturation-and-max-core-usage.png
image_count: 14
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Prometheus 2 Times Series Storage Performance Analyses

Source: [Percona Blog](https://www.percona.com/blog/prometheus-2-times-series-storage-performance-analyses/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2018-09-20T16:07:20

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Prometheus 2 time series database (TSDB) is an amazing piece of engineering, offering a dramatic improvement compared to “v2” storage in Prometheus 1 in terms of ingest performance, query performance and resource use efficiency. As we’ve been adopting Prometheus 2 in Percona Monitoring and Management (PMM), I had a chance to look into the performance … Continued

## Structure detectee

- H3: Understanding the typical Prometheus workload
- H3: The Benchmark
- H3: Design Observations
- H3: Compactions
- H3: Crash Recovery
- H3: Warmup
- H3: CPU Usage Spikes
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Prometheus 2 Times Series Storage Performance Analyses](https://www.percona.com/wp-content/uploads/2026/03/cpu-saturation-and-max-core-usage.png)
- content / image: [head block Prometheus 2](https://www.percona.com/wp-content/uploads/2026/03/head-block-Prometheus-2.png)
- content / image: [Prometheus process memory usage](https://www.percona.com/wp-content/uploads/2026/03/Prometheus-process-memory-usage.png)
- content / image: [active data blocks](https://www.percona.com/wp-content/uploads/2026/03/active-data-blocks.png)
- content / image: [Prometheus 2 compactions](https://www.percona.com/wp-content/uploads/2026/03/Prometheus-2-compactions.png)
- content / image: [spike in io activity for compactions](https://www.percona.com/wp-content/uploads/2026/03/spike-in-io-activity-for-compactions.png)
- content / image: [spike in CPU usage during compactions](https://www.percona.com/wp-content/uploads/2026/03/spike-in-CPU-usage-during-compactions.png)
- content / image: [Memory utilization during compaction process](https://www.percona.com/wp-content/uploads/2026/03/Memory-utilization-during-compaction-process.png)
- content / image: [cpu usage during warmup](https://www.percona.com/wp-content/uploads/2026/03/cpu-usage-during-warmup.png)
- content / image: [memory usage during warmup](https://www.percona.com/wp-content/uploads/2026/03/memory-usage-during-warmup.png)
- content / image: [cpu usage spikes maybe during Go Garbage collection](https://www.percona.com/wp-content/uploads/2026/03/cpu-usage-spikes-maybe-during-Go-Garbage-collection.png)
- content / image: [Prometheus 2 process memory usage](https://www.percona.com/wp-content/uploads/2026/03/Prometheus-2-process-memory-usage.png)
- content / image: [scrape time by job](https://www.percona.com/wp-content/uploads/2026/03/scrape-time-by-job.png)
- content / image: [garbage collection in Prometheus processing](https://www.percona.com/wp-content/uploads/2026/03/garbage-collection-in-Prometheus-processing.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
