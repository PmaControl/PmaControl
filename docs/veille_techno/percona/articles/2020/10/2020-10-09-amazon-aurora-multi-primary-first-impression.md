---
title: Amazon Aurora Multi-Primary First Impression
source:
  name: Percona Blog
  url: https://www.percona.com/blog/amazon-aurora-multi-primary-first-impression/
  post_id: 23279
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2020-10-09T14:53:45'
published_at_gmt: '2020-10-09T14:53:45'
modified_at: '2026-04-27T22:15:41'
modified_at_gmt: '2026-04-27T22:15:41'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- ProxySQL
matched_filters:
- category:mysql:83
- category:proxysql:2261
- search:proxysql
categories:
- Benchmarks
- Cloud
- MySQL
- ProxySQL
category_slugs:
- benchmarks
- cloud
- mysql
- proxysql
tags:
- Amazon Aurora
- cloud
- High Availability
- MySQL
- mysql-and-variants
- ProxySQL
- Scalability
tag_slugs:
- amazon-aurora
- cloud
- high-availability
- mysql
- mysql-and-variants
- proxysql
- scalability
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Amazon-Aurora-Multi-Primary-First-Impression.png
image_count: 34
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Amazon Aurora Multi-Primary First Impression

Source: [Percona Blog](https://www.percona.com/blog/amazon-aurora-multi-primary-first-impression/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2020-10-09T14:53:45

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

For what reason should I use a real multi-primary setup? To be clear, not a multi-writer solution where any node can become the active writer in case of needs, as for Percona XtraDB Cluster (PXC) or Percona Server for MySQL using Group_replication. No, we are talking about a multi-primary setup where I can write at … Continued

## Structure detectee

- H2: Test Results
- H3: Connection Speed
- H3: Stale Reads
- H2: Writing Tests
- H3: Write Single Node (Baseline)
- H3: Write on Both Nodes, Different Schemas
- H3: Overview
- H3: Reads
- H3: Writes
- H3: Write on Both Nodes, Same Schema
- H3: Overview
- H3: Reads
- H3: Writes
- H2: Recovery From Crashed Node
- H3: Conclusions
- H4: References

## Images et graphiques reperes

- featured / image: [Amazon Aurora Multi-Primary First Impression](https://www.percona.com/wp-content/uploads/2026/03/Amazon-Aurora-Multi-Primary-First-Impression.png)
- content / image: [Amazon Aurora Multi-Primary First Impression](https://www.percona.com/wp-content/uploads/2026/03/Amazon-Aurora-Multi-Primary-First-Impression-300x168.png)
- content / image: [instances.png](https://www.percona.com/wp-content/uploads/2026/03/instances.png)
- content / image: [Amazon Aurora Multi-Primary](https://www.percona.com/wp-content/uploads/2026/03/a1.png)
- content / image: [a3.png](https://www.percona.com/wp-content/uploads/2026/03/a3.png)
- content / image: [a2.png](https://www.percona.com/wp-content/uploads/2026/03/a2.png)
- content / image: [a4.png](https://www.percona.com/wp-content/uploads/2026/03/a4.png)
- content / image: [aurora_multi_master_sharing_BP.png](https://www.percona.com/wp-content/uploads/2026/03/aurora_multi_master_sharing_BP.png)
- content / image: [Aurora consistency model](https://www.percona.com/wp-content/uploads/2026/03/Consistency_model.png)
- content / image: [Screen-Shot-2020-10-09-at-10.49.53-AM-1024x271.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-10-09-at-10.49.53-AM-1024x271.png)
- content / image: [a7.png](https://www.percona.com/wp-content/uploads/2026/03/a7.png)
- content / image: [lag time in nanoseconds](https://www.percona.com/wp-content/uploads/2026/03/a8.png)
- content / image: [baseline reads/writes](https://www.percona.com/wp-content/uploads/2026/03/Picture-1-2.png)
- content / image: [t1-2-1024x447.png](https://www.percona.com/wp-content/uploads/2026/03/t1-2-1024x447.png)
- content / image: [t2-1-1024x447.png](https://www.percona.com/wp-content/uploads/2026/03/t2-1-1024x447.png)
- content / image: [aurora scalability](https://www.percona.com/wp-content/uploads/2026/03/scalability.png)
- content / image: [expected scalability](https://www.percona.com/wp-content/uploads/2026/03/Picture-2-2.png)
- content / image: [split_traffic_by_db_table_partition_to_avoid_conflicts.png](https://www.percona.com/wp-content/uploads/2026/03/split_traffic_by_db_table_partition_to_avoid_conflicts.png)
- content / image: [Picture-3-2.png](https://www.percona.com/wp-content/uploads/2026/03/Picture-3-2.png)
- content / image: [Schema read writes Aurora](https://www.percona.com/wp-content/uploads/2026/03/Picture-5-1.png)
- content / image: [Picture-6.png](https://www.percona.com/wp-content/uploads/2026/03/Picture-6.png)
- content / image: [t4-1024x447.png](https://www.percona.com/wp-content/uploads/2026/03/t4-1024x447.png)
- content / image: [t7-1024x447.png](https://www.percona.com/wp-content/uploads/2026/03/t7-1024x447.png)
- content / image: [t5-1024x447.png](https://www.percona.com/wp-content/uploads/2026/03/t5-1024x447.png)
- content / image: [t8-1024x447.png](https://www.percona.com/wp-content/uploads/2026/03/t8-1024x447.png)
- content / image: [Picture-4-1-1.png](https://www.percona.com/wp-content/uploads/2026/03/Picture-4-1-1.png)
- content / image: [Write on Both Nodes, Same Schema](https://www.percona.com/wp-content/uploads/2026/03/Picture-7-1.png)
- content / image: [Picture-8.png](https://www.percona.com/wp-content/uploads/2026/03/Picture-8.png)
- content / image: [t10-1024x447.png](https://www.percona.com/wp-content/uploads/2026/03/t10-1024x447.png)
- content / image: [t13-1024x447.png](https://www.percona.com/wp-content/uploads/2026/03/t13-1024x447.png)
- content / image: [t11-1024x447.png](https://www.percona.com/wp-content/uploads/2026/03/t11-1024x447.png)
- content / image: [t14-1024x447.png](https://www.percona.com/wp-content/uploads/2026/03/t14-1024x447.png)
- content / image: [Recovery From Crashed Node](https://www.percona.com/wp-content/uploads/2026/03/fail-over_times_using_mariadb_driver.png)
- content / image: [a10.png](https://www.percona.com/wp-content/uploads/2026/03/a10.png)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
