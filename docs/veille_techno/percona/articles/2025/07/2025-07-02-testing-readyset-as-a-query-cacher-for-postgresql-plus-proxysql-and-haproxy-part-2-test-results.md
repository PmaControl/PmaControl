---
title: 'Testing ReadySet as a Query Cacher for PostgreSQL (Plus ProxySQL and HAproxy) Part 2: Test Results'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/testing-readyset-as-a-query-cacher-for-postgresql-plus-proxysql-and-haproxy-part-2-test-results/
  post_id: 35021
source_author:
  name: Fernando Laudares Camargos
  slug: fernando-laudares
  url: https://www.percona.com/blog/author/fernando-laudares/
  website: ''
published_at: '2025-07-02T12:38:57'
published_at_gmt: '2025-07-02T12:38:57'
modified_at: '2026-05-05T17:14:32'
modified_at_gmt: '2026-05-05T17:14:32'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
- ProxySQL
matched_filters:
- category:proxysql:2261
- search:pmm
- search:proxysql
categories:
- Benchmarks
- PostgreSQL
- ProxySQL
category_slugs:
- benchmarks
- postgresql
- proxysql
tags:
- Fernando PP
- PostgreSQL
- ProxySQL
tag_slugs:
- fernando-planetpostgresql
- postgresql
- proxysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Testing-ReadySet-as-a-Query-Cacher-for-PostgreSQL-test-results.jpg
image_count: 32
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Testing ReadySet as a Query Cacher for PostgreSQL (Plus ProxySQL and HAproxy) Part 2: Test Results

Source: [Percona Blog](https://www.percona.com/blog/testing-readyset-as-a-query-cacher-for-postgresql-plus-proxysql-and-haproxy-part-2-test-results/)

Auteur source: [Fernando Laudares Camargos](https://www.percona.com/blog/author/fernando-laudares/)

Publication: 2025-07-02T12:38:57

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In the first post of this series (Testing ReadySet as a Query Cacher for PostgreSQL (Plus ProxySQL and HAproxy) Part 1: How-To), I presented my test environment and methodology and explained how to install ReadySet, ProxySQL, and HAproxy and configure them to work with PostgreSQL. In this final part, I present the different test scenarios … Continued

## Structure detectee

- H2: Quick recap
- H2: Test scenarios
- H2: Results
- H2: Query processing
- H2: ReadySet metrics
- H2: Performance metrics
- H3: Disk performance
- H3: Active connections and throughput
- H3: Replication lag

## Images et graphiques reperes

- featured / image: [Testing ReadySet as a Query Cacher for PostgreSQL (Plus ProxySQL and HAproxy) Part 2: Test Results](https://www.percona.com/wp-content/uploads/2026/03/Testing-ReadySet-as-a-Query-Cacher-for-PostgreSQL-test-results.jpg)
- content / image: [read-write test](https://www.percona.com/wp-content/uploads/2026/03/Sysbench-OLTP-Read-Write-64-threads-limits.png)
- content / image: [read-only test](https://www.percona.com/wp-content/uploads/2026/03/Sysbench-OLTP-Read-Only-64-threads-limits.png)
- content / image: [primary-rw-CORRECT.png](https://www.percona.com/wp-content/uploads/2026/03/primary-rw-CORRECT.png)
- content / image: [readyset-rw.png](https://www.percona.com/wp-content/uploads/2026/03/readyset-rw.png)
- content / image: [proxysql-rw-pg1.png](https://www.percona.com/wp-content/uploads/2026/03/proxysql-rw-pg1.png)
- content / image: [proxysql-rw-pg2.png](https://www.percona.com/wp-content/uploads/2026/03/proxysql-rw-pg2.png)
- content / image: [Selection_4973-scaled-1.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_4973-scaled-1.png)
- content / image: [readyset-ro.png](https://www.percona.com/wp-content/uploads/2026/03/readyset-ro.png)
- content / image: [proxysql-ro-pg1.png](https://www.percona.com/wp-content/uploads/2026/03/proxysql-ro-pg1.png)
- content / image: [proxysql-ro-pg2.png](https://www.percona.com/wp-content/uploads/2026/03/proxysql-ro-pg2.png)
- content / image: [haproxy-ro-pg1.png](https://www.percona.com/wp-content/uploads/2026/03/haproxy-ro-pg1.png)
- content / image: [haproxy-ro-pg2.png](https://www.percona.com/wp-content/uploads/2026/03/haproxy-ro-pg2.png)
- content / image: [Selection_4943.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_4943.png)
- content / image: [Selection_4946.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_4946.png)
- content / image: [Selection_4912-Copy.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_4912-Copy.png)
- content / image: [Selection_4925.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_4925.png)
- content / image: [Selection_4935.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_4935.png)
- content / image: [Selection_4917.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_4917.png)
- content / image: [Selection_4930.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_4930.png)
- content / image: [Selection_4940.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_4940.png)
- content / image: [Selection_4918.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_4918.png)
- content / image: [Selection_4931.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_4931.png)
- content / image: [Selection_4941.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_4941.png)
- content / image: [Selection_4919.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_4919.png)
- content / image: [Selection_4932.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_4932.png)
- content / image: [Selection_4942.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_4942.png)
- content / image: [Selection_4906-Copy.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_4906-Copy.png)
- content / image: [Selection_4910-Copy.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_4910-Copy.png)
- content / image: [Selection_4907-Copy.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_4907-Copy.png)
- content / image: [rep_lag-proxysql-rw-1024x392.png](https://www.percona.com/wp-content/uploads/2026/03/rep_lag-proxysql-rw-1024x392.png)
- content / image: [Get-Enterprise-Postgres-1.png](https://www.percona.com/wp-content/uploads/2026/03/Get-Enterprise-Postgres-1.png)

## Auteur source

Fernando Laudares Camargos joined Percona in early 2013 after working 8 years for a Canadian company specialized in offering services based in open source technologies. Fernando's work experience includes the architecture, deployment and maintenance of IT infrastructures based on Linux, open source software and a layer of server virtualization. From the basic services such as DHCP & DNS to identity management systems, but also including backup routines, configuration management tools and thin-clients. He's now focusing on the universe of MySQL, MongoDB and PostgreSQL with a particular interest in understanding the intricacies of database systems and contributes regularly to this blog. You can read his other articles here.
