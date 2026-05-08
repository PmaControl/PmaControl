---
title: Is Thread Pool Plugin the Right Choice for Your Workload?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/is-thread-pool-plugin-the-right-choice-for-your-workload/
  post_id: 27906
source_author:
  name: Francisco Bordenave
  slug: francisco-bordenave
  url: https://www.percona.com/blog/author/francisco-bordenave/
  website: ''
published_at: '2024-01-02T17:01:39'
published_at_gmt: '2024-01-02T17:01:39'
modified_at: '2026-03-26T20:26:48'
modified_at_gmt: '2026-03-26T20:26:48'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- ProxySQL
matched_filters:
- category:mysql:83
- search:pmm
- search:proxysql
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- MySQL
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/thread-pool-plugin-mysql.jpg
image_count: 13
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Is Thread Pool Plugin the Right Choice for Your Workload?

Source: [Percona Blog](https://www.percona.com/blog/is-thread-pool-plugin-the-right-choice-for-your-workload/)

Auteur source: [Francisco Bordenave](https://www.percona.com/blog/author/francisco-bordenave/)

Publication: 2024-01-02T17:01:39

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

TL&DR: Depending on the workload, the thread pool plugin can cause serious performance drops. This post was motivated by two recent cases I’ve worked with. Two setups running in cluster mode: one in MariaDB+Galera and one with Percona XtraDB Cluster (Percona Server+Galera). In both cases, the clusters had the thread pool plugin enabled and were … Continued

## Structure detectee

- H3: The method:
- H2: Baseline numbers
- H3: Default configuration (innodb_thread_concurrency=0)
- H3: innodb_thread_concurrency=8
- H3: thread pool enabled
- H2: Metrics with long-running queries
- H3: Default configuration (innodb_thread_concurrency=0)
- H3: innodb_thread_concurrency=8
- H3: thread pool enabled

## Images et graphiques reperes

- featured / image: [Is Thread Pool Plugin the Right Choice for Your Workload?](https://www.percona.com/wp-content/uploads/2026/03/thread-pool-plugin-mysql.jpg)
- content / image: [Thread Pool Plugin](https://www.percona.com/wp-content/uploads/2026/03/qps_default-1-scaled.png)
- content / image: [cpu_default-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/cpu_default-scaled.png)
- content / image: [qps_inndodb_thread_concurrency-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/qps_inndodb_thread_concurrency-scaled.png)
- content / image: [cpu_innodb_thread_concurrency_long_queries-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/cpu_innodb_thread_concurrency_long_queries-scaled.png)
- content / image: [qps_thread_pool-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/qps_thread_pool-scaled.png)
- content / image: [cpu_thread_pool-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/cpu_thread_pool-scaled.png)
- content / image: [qps_long_running_queries-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/qps_long_running_queries-scaled.png)
- content / image: [cpu_long_running_queries-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/cpu_long_running_queries-scaled.png)
- content / image: [qps_innodb_thread_concurrency_long_queries-1-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/qps_innodb_thread_concurrency_long_queries-1-scaled.png)
- content / image: [cpu_innodb_thread_concurrency_long_queries-1-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/cpu_innodb_thread_concurrency_long_queries-1-scaled.png)
- content / image: [thread pool enabled](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-from-2023-12-13-11-50-52-scaled.png)
- content / image: [Screenshot-from-2023-12-13-11-50-39-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-from-2023-12-13-11-50-39-scaled.png)

## Auteur source

Francisco has been working in MySQL since 2006, he has worked for several companies which includes Health Care industry to Gaming. Over the last 6 years he has been working as a Remote DBA and Database Consultant which help him to acquire a lot of technical and multi-cultural skills. He lives in La Plata, Argentina and during his free time he likes to play football, spent time with family and friends and cook.
