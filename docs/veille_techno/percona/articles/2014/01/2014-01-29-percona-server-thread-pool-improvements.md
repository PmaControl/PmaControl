---
title: 'Percona Server: Thread Pool Improvements for Transactional Workloads'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-server-thread-pool-improvements/
  post_id: 7683
source_author:
  name: Alexey Stroganov
  slug: alexey-stroganov
  url: https://www.percona.com/blog/author/alexey-stroganov/
  website: http://www.percona.com
published_at: '2014-01-29T22:00:27'
published_at_gmt: '2014-01-29T22:00:27'
modified_at: '2026-03-25T17:17:58'
modified_at_gmt: '2026-03-25T17:17:58'
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
- Percona Software
category_slugs:
- benchmarks
- mysql
- percona-software
tags:
- Alexey Stroganov
- Percona Server for MySQL
- Thread pool
- Transactional Workloads
tag_slugs:
- alexey-stroganov
- percona-server
- thread-pool
- transactional-workloads
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/thread_pool.p2.cpu_bound.png
image_count: 5
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona Server: Thread Pool Improvements for Transactional Workloads

Source: [Percona Blog](https://www.percona.com/blog/percona-server-thread-pool-improvements/)

Auteur source: [Alexey Stroganov](https://www.percona.com/blog/author/alexey-stroganov/)

Publication: 2014-01-29T22:00:27

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In a previous thread pool post, I mentioned that in Percona Server we used an open source implementation of MariaDB’s thread pool, and enhanced/improved it further. Below I would like to describe some of these improvements for transactional workloads. When we were evaluating MariaDB’s thread pool implementation, we observed that it improves scalability for AUTOCOMMIT … Continued

## Images et graphiques reperes

- featured / image: [Percona Server: Thread Pool Improvements for Transactional Workloads](https://www.percona.com/wp-content/uploads/2026/03/thread_pool.p2.cpu_bound.png)
- content / image: [thread_pool.p2.mariadb.v3](https://www.percona.com/wp-content/uploads/2026/03/thread_pool.p2.mariadb.v3.png)
- content / image: [high_priority_diagram.v2](https://www.percona.com/wp-content/uploads/2026/03/high_priority_diagram.v2.png)
- content / image: [thread_pool.p2.mariadb.percona.v2](https://www.percona.com/wp-content/uploads/2026/03/thread_pool.p2.mariadb.percona.v2.png)
- content / image: [thread_pool.p2.io_bound](https://www.percona.com/wp-content/uploads/2026/03/thread_pool.p2.io_bound.png)

## Auteur source

Alexey Stroganov is a Performance Engineer at Percona, where he works on improvements and features that makes Percona Server even more flexible, faster and scalable. Before joining Percona he worked on the performance testings/analysis of MySQL server and it components at MySQL AB/Sun/Oracle for more than ten years. During this time he was focused on performance evaluations, benchmarks, analysis, profiling, various optimizations and tunings.
