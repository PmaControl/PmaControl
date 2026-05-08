---
title: Using Parallel Query with Amazon Aurora for MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-parallel-query-with-amazon-aurora-for-mysql/
  post_id: 19836
source_author:
  name: Alexander Rubin
  slug: alexanderrubin
  url: https://www.percona.com/blog/author/alexanderrubin/
  website: http://www.percona.com/blog
published_at: '2019-01-17T18:31:14'
published_at_gmt: '2019-01-17T18:31:14'
modified_at: '2026-04-29T14:45:04'
modified_at_gmt: '2026-04-29T14:45:04'
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
- Cloud
- Insight for DBAs
- MySQL
category_slugs:
- cloud
- insight-for-dbas
- mysql
tags:
- parallel query
- Parallel Query Execution
- parallelization
tag_slugs:
- parallel-query
- parallel-query-execution
- parallelization
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/parallel-query-amazon-aurora-for-mysql.jpg
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using Parallel Query with Amazon Aurora for MySQL

Source: [Percona Blog](https://www.percona.com/blog/using-parallel-query-with-amazon-aurora-for-mysql/)

Auteur source: [Alexander Rubin](https://www.percona.com/blog/author/alexanderrubin/)

Publication: 2019-01-17T18:31:14

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Parallel query execution is my favorite, non-existent, feature in MySQL. In all versions of MySQL – at least at the time of writing – when you run a single query it will run in one thread, effectively utilizing one CPU core only. Multiple queries run at the same time will be using different threads and … Continued

## Structure detectee

- H2: In Short
- H2: Test data and versions
- H2: Aurora instance type and comparison
- H4: Aurora:
- H4: MySQL on ec2
- H2: Table
- H2: Working with Aurora PQ (Parallel Query)
- H2: Queries
- H2: Query 1a: simple, count(*)
- H3: Aurora, pq (parallel query) disabled:
- H2: Query 1b: simple, avg
- H2: Summary of simple query performance
- H2: Query 2: Complex filter, single table
- H4: PQ enabled:
- H2: Query 3: Complex filter, join “reference” table
- H2: Summary

## Images et graphiques reperes

- featured / image: [Using Parallel Query with Amazon Aurora for MySQL](https://www.percona.com/wp-content/uploads/2026/03/parallel-query-amazon-aurora-for-mysql.jpg)
- content / image: [parallel query amazon aurora for mysql](https://www.percona.com/wp-content/uploads/2026/03/parallel-query-amazon-aurora-for-mysql-300x187.jpg)
- content / image: [Buffer pool requests from PMM](https://www.percona.com/wp-content/uploads/2026/03/buffer_pool_requests.png)
- content / image: [InnoDB Buffer Pool Requests](https://www.percona.com/wp-content/uploads/2026/03/buffer_pool_requests2.png)

## Auteur source

Alexander joined Percona in 2013. Alexander worked with MySQL since 2000 as DBA and Application Developer. Before joining Percona he was doing MySQL consulting as a principal consultant for over 7 years (started with MySQL AB in 2006, then Sun Microsystems and then Oracle). He has helped many customers design large, scalable and highly available MySQL systems and optimize MySQL performance. Alexander has also helped customers design Big Data stores with Apache Hadoop and related technologies.
