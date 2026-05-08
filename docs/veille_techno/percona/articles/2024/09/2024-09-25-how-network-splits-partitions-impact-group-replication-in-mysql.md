---
title: How Network Splits/Partitions Impact Group Replication in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-network-splits-partitions-impact-group-replication-in-mysql/
  post_id: 28998
source_author:
  name: Anil Joshi
  slug: anil-joshi
  url: https://www.percona.com/blog/author/anil-joshi/
  website: ''
published_at: '2024-09-25T13:25:05'
published_at_gmt: '2024-09-25T13:25:05'
modified_at: '2026-03-26T20:25:55'
modified_at_gmt: '2026-03-26T20:25:55'
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
- Insight for DBAs
- MySQL
category_slugs:
- benchmarks
- insight-for-dbas
- mysql
tags:
- InnoDB
- Innodb cluster
- MySQL
- MySQL Group Replication
- MySQL Innodb Cluster
- mysql-and-variants
- MySQL8 Innodb ClusterSet
tag_slugs:
- innodb
- innodb-cluster
- mysql
- mysql-group-replication
- mysql-innodb-cluster
- mysql-and-variants
- mysql8-innodb-clusterset
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Network-SplitsPartition-on-Group-Replication.jpg
image_count: 3
graph_or_chart_count: 2
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How Network Splits/Partitions Impact Group Replication in MySQL

Source: [Percona Blog](https://www.percona.com/blog/how-network-splits-partitions-impact-group-replication-in-mysql/)

Auteur source: [Anil Joshi](https://www.percona.com/blog/author/anil-joshi/)

Publication: 2024-09-25T13:25:05

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we will explore how network partitions impact group replication and the way it detects and responds to failures. In case you haven’t checked out my previous blog post about group replication recovery strategies, please have a look at them for some insight. Topology: node1 [localhost:23637] {msandbox} ((none)) > select * from performance_schema.replication_group_members;<br>+---------------------------+--------------------------------------+-------------+-------------+--------------+-------------+----------------+----------------------------+<br>| CHANNEL_NAME | MEMBER_ID | MEMBER_HOST | MEMBER_PORT | MEMBER_STATE | MEMBER_ROLE | MEMBER_VERSION | MEMBER_COMMUNICATION_STACK |<br>+---------------------------+--------------------------------------+-------------+-------------+--------------+-------------+----------------+----------------------------+<br>| group_replicati...

## Structure detectee

- H3: Topology:
- H2: Scenario 1: One of the GR nodes [node3] faces some network interruption
- H4: Blocking communication:
- H4: Verifying the rule:
- H4: Output:
- H2: Scenario 2: Now, two of the GR nodes [node2 & node3] face some network interruption
- H4: Blocking communication:
- H4: Verifying the rule:
- H4: Output:
- H4: Workload impacted:
- H2: Final thoughts:

## Images et graphiques reperes

- featured / image: [How Network Splits/Partitions Impact Group Replication in MySQL](https://www.percona.com/wp-content/uploads/2026/03/Network-SplitsPartition-on-Group-Replication.jpg)
- content / graph_or_chart: [Group Replication network partition in case of single node failures](https://www.percona.com/wp-content/uploads/2026/03/image1-1-19-1024x548.png)
  Caption: This diagram depicts a single node down/partitioned.
- content / graph_or_chart: [Group Replication Network Partition when multiple nodes down.](https://www.percona.com/wp-content/uploads/2026/03/image2-33-1024x561.png)
  Caption: This diagram depicts multiple node failures.

## Auteur source

I am Anil Joshi, and I work for Percona as a support engineer. I've worked with some well-known Open Source database technologies (MySQL/MariaDB, MongoDB, and Redis) for almost ten years. I am keenly interested in learning new databases and writing database content.
