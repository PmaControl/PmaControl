---
title: 'Percona Monitoring and Management (PMM) Graphs Explained: MongoDB with RocksDB'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-monitoring-and-management-pmm-graphs-explained-mongodb-with-rocksdb/
  post_id: 16369
source_author:
  name: Tim Vaillancourt
  slug: tim-vaillancourt
  url: https://www.percona.com/blog/author/tim-vaillancourt/
  website: ''
published_at: '2017-02-22T20:36:25'
published_at_gmt: '2017-02-22T20:36:25'
modified_at: '2026-03-26T20:21:41'
modified_at_gmt: '2026-03-26T20:21:41'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- search:percona-monitoring-and-management
- search:pmm
- tag:percona-monitoring-and-management:2166
- tag:pmm:2167
categories:
- MongoDB
- Percona Software
category_slugs:
- mongodb
- percona-software
tags:
- graphs
- Metrics
- MongoDB
- MySQL
- Percona Monitoring and Management
- PMM
- RocksDB
tag_slugs:
- graphs
- metrics
- mongodb
- mysql
- percona-monitoring-and-management
- pmm
- rocksdb
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-Monitoring-and-Management-e1502904228646.png
image_count: 18
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona Monitoring and Management (PMM) Graphs Explained: MongoDB with RocksDB

Source: [Percona Blog](https://www.percona.com/blog/percona-monitoring-and-management-pmm-graphs-explained-mongodb-with-rocksdb/)

Auteur source: [Tim Vaillancourt](https://www.percona.com/blog/author/tim-vaillancourt/)

Publication: 2017-02-22T20:36:25

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This blog post is another in the series on the Percona Server for MongoDB 3.4 bundle release. In mid-2016, Percona Monitoring and Management (PMM) added support for RocksDB with MongoDB, also known as “MongoRocks.” In this blog, we will go over the Percona Monitoring and Management (PMM) 1.1.0 version of the MongoDB RocksDB dashboard, how PMM is … Continued

## Images et graphiques reperes

- featured / image: [Percona Monitoring and Management (PMM) Graphs Explained: MongoDB with RocksDB](https://www.percona.com/wp-content/uploads/2026/03/Percona-Monitoring-and-Management-e1502904228646.png)
- content / image: [Percona Monitoring and Management](https://www.percona.com/wp-content/uploads/2026/03/Percona-Monitoring-and-Management-e1487293756967.png)
- content / image: [RocksDB](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2017-02-16-at-14.45.34.png)
- content / image: [rocksdb_memtable_percent-300x85.png](https://www.percona.com/wp-content/uploads/2026/03/rocksdb_memtable_percent-300x85.png)
- content / image: [RocksDB](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2017-02-15-at-22.50.31.png)
- content / image: [Screen-Shot-2017-02-16-at-14.22.15.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2017-02-16-at-14.22.15.png)
- content / image: [Screen-Shot-2017-02-16-at-14.41.26.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2017-02-16-at-14.41.26.png)
- content / image: [Screen-Shot-2017-02-16-at-15.42.29.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2017-02-16-at-15.42.29.png)
- content / image: [Screen-Shot-2017-02-15-at-20.06.36.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2017-02-15-at-20.06.36.png)
- content / image: [Screen-Shot-2017-02-15-at-20.06.17.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2017-02-15-at-20.06.17.png)
- content / image: [Screen-Shot-2017-02-15-at-20.07.17.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2017-02-15-at-20.07.17.png)
- content / image: [Screen-Shot-2017-02-16-at-16.33.49.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2017-02-16-at-16.33.49.png)
- content / image: [Screen-Shot-2017-02-15-at-20.04.18-1024x217.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2017-02-15-at-20.04.18-1024x217.png)
- content / image: [Screen-Shot-2017-02-15-at-20.03.57-1024x217.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2017-02-15-at-20.03.57-1024x217.png)
- content / image: [Screen-Shot-2017-02-16-at-17.22.40.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2017-02-16-at-17.22.40.png)
- content / image: [Screen-Shot-2017-02-16-at-16.50.07.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2017-02-16-at-16.50.07.png)
- content / image: [Screen-Shot-2017-02-16-at-17.02.17.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2017-02-16-at-17.02.17.png)
- content / image: [Screen-Shot-2017-02-16-at-17.10.38.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2017-02-16-at-17.10.38.png)
