---
title: 'Percona Server: Improve Scalability with Percona Thread Pool'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-server-improve-scalability-percona-thread-pool/
  post_id: 7674
source_author:
  name: Alexey Stroganov
  slug: alexey-stroganov
  url: https://www.percona.com/blog/author/alexey-stroganov/
  website: http://www.percona.com
published_at: '2014-01-23T22:28:32'
published_at_gmt: '2014-01-23T22:28:32'
modified_at: '2026-03-25T17:17:49'
modified_at_gmt: '2026-03-25T17:17:49'
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
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/thread_pool.p1.cpu_bound.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona Server: Improve Scalability with Percona Thread Pool

Source: [Percona Blog](https://www.percona.com/blog/percona-server-improve-scalability-percona-thread-pool/)

Auteur source: [Alexey Stroganov](https://www.percona.com/blog/author/alexey-stroganov/)

Publication: 2014-01-23T22:28:32

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

By default, for every client connection the MySQL server spawns a separate thread which will process all statements for this connection. This is the ‘one-thread-per-connection’ model. It’s simple and efficient until some number of connections N is reached. After this point performance of the MySQL server will degrade, mostly due to various contentions caused by … Continued

## Images et graphiques reperes

- featured / image: [Percona Server: Improve Scalability with Percona Thread Pool](https://www.percona.com/wp-content/uploads/2026/03/thread_pool.p1.cpu_bound.png)
- content / image: [thread_pool.p1.io_bound](https://www.percona.com/wp-content/uploads/2026/03/thread_pool.p1.io_bound.png)
- content / image: [thread_pool.p1.io_bound.updated.v2](https://www.percona.com/wp-content/uploads/2026/03/thread_pool.p1.io_bound.updated.v2.png)

## Auteur source

Alexey Stroganov is a Performance Engineer at Percona, where he works on improvements and features that makes Percona Server even more flexible, faster and scalable. Before joining Percona he worked on the performance testings/analysis of MySQL server and it components at MySQL AB/Sun/Oracle for more than ten years. During this time he was focused on performance evaluations, benchmarks, analysis, profiling, various optimizations and tunings.
