---
title: 'Reducing High CPU on MySQL: a Case Study'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/reducing-high-cpu-on-mysql-a-case-study/
  post_id: 20069
source_author:
  name: Marcelo Altmann
  slug: marcelo-altmann
  url: https://www.percona.com/blog/author/marcelo-altmann/
  website: https://blog.marceloaltmann.com
published_at: '2019-03-07T15:17:35'
published_at_gmt: '2019-03-07T15:17:35'
modified_at: '2026-04-27T21:12:14'
modified_at_gmt: '2026-04-27T21:12:14'
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
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- MySQL indexing
- MySQL Query Performance
- MySQL Query Tuning
tag_slugs:
- mysql-indexing
- mysql-query-performance
- mysql-query-tuning
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2019-02-22-at-16.45.34.png
image_count: 13
graph_or_chart_count: 3
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Reducing High CPU on MySQL: a Case Study

Source: [Percona Blog](https://www.percona.com/blog/reducing-high-cpu-on-mysql-a-case-study/)

Auteur source: [Marcelo Altmann](https://www.percona.com/blog/author/marcelo-altmann/)

Publication: 2019-03-07T15:17:35

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, I want to share a case we worked on a few days ago. I’ll show you how we approached the resolution of a MySQL performance issue and used Percona Monitoring and Management PMM to support troubleshooting. The customer had noticed a linear high CPU usage in one of their MySQL instances … Continued

## Structure detectee

- H2: CPU
- H2: MySQL
- H2: Results
- H2: Summary:

## Images et graphiques reperes

- featured / image: [Reducing High CPU on MySQL: a Case Study](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2019-02-22-at-16.45.34.png)
- content / image: [The original issue - CPU usage at almost 100% during application use](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2019-02-22-at-15.52.58-1024x403.png)
- content / graph_or_chart: [Thread activity graph in PMM for MySQL](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2019-02-22-at-16.03.33.png)
- content / image: [Queries per second](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2019-02-22-at-16.03.47.png)
- content / graph_or_chart: [All the commands are of a SELECT type, in red in this graph](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2019-02-22-at-16.14.29-1024x261.png)
- content / image: [Showing that the query was a full table scan](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2019-02-22-at-16.17.57-1024x231.png)
- content / image: [identifying the long running query in QAN](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2019-02-22-at-15.25.35-scaled.png)
- content / image: [The initial query load](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2019-02-22-at-16.31.57-1024x72.png)
- content / image: [Fingerprint of query](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2019-02-22-at-16.34.35-1024x89.png)
- content / image: [Indexes on table did not include a key column](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2019-02-22-at-16.35.13-1024x77.png)
- content / image: [MySQL Handlers after query tuning](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2019-02-22-at-16.46.36.png)
- content / graph_or_chart: [MySQL query throughput after query tuning](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2019-02-22-at-16.46.10.png)
- content / image: [Query analysis by EXPLAIN in PMM after tuning](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2019-02-23-at-10.11.31-1024x73.png)

## Auteur source

Marcelo Altmann is a C++ Software Engineer working on MySQL related products. At Percona he has also worked as a Senior Support Engineer and a Tech Lead of the Support Team. Prior to joining Percona , he worked as a MySQL DBA at Ireland's CCTLD, and worked as a DBA/PHP developer in Brazil. He also blogs about other MySQL related stuff at his personal blog.
