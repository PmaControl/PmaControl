---
title: Deploying External Read Replica in MySQL InnoDB Cluster
source:
  name: Percona Blog
  url: https://www.percona.com/blog/deploying-external-read-replica-in-mysql-innodb-cluster/
  post_id: 34881
source_author:
  name: Anil Joshi
  slug: anil-joshi
  url: https://www.percona.com/blog/author/anil-joshi/
  website: ''
published_at: '2025-05-08T12:52:57'
published_at_gmt: '2025-05-08T12:52:57'
modified_at: '2026-03-26T20:25:34'
modified_at_gmt: '2026-03-26T20:25:34'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- asynchronous MySQL replication
- Innodb cluster
- MySQL
- MySQL Innodb Cluster
- mysql-and-variants
- MySQL8
tag_slugs:
- asynchronous-mysql-replication
- innodb-cluster
- mysql
- mysql-innodb-cluster
- mysql-and-variants
- mysql8
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Deploying-External-Read-Replica-in-MySQL-InnoDB-Cluster.jpg
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Deploying External Read Replica in MySQL InnoDB Cluster

Source: [Percona Blog](https://www.percona.com/blog/deploying-external-read-replica-in-mysql-innodb-cluster/)

Auteur source: [Anil Joshi](https://www.percona.com/blog/author/anil-joshi/)

Publication: 2025-05-08T12:52:57

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Innodb Cluster or ClusterSet topologies already have secondary instances that can act as a failover for primary or also offload read requests. However, with MySQL 8.4, we now have the feasibility of adding a separate async replica to the cluster for serving various special/ad-hoc queries or some reporting purposes. This will also help offload read traffic away … Continued

## Structure detectee

- H2: Topology
- H4: InnoDB cluster nodes
- H4: Async replica
- H2: Quickly deploying InnoDB Cluster
- H2: Bootstrapping the first node
- H2: Adding other nodes
- H2: Verifying cluster status
- H2: Adding async replica
- H4: Prerequisites:
- H4: Performing manual switchover over node (“127.0.0.1:3308”)
- H3: Monitoring
- H2: Traffic routing
- H3: Recovery of the async replica
- H2: Final thought

## Images et graphiques reperes

- featured / image: [Deploying External Read Replica in MySQL InnoDB Cluster](https://www.percona.com/wp-content/uploads/2026/03/Deploying-External-Read-Replica-in-MySQL-InnoDB-Cluster.jpg)
- content / image: [Innodb Cluster Read Replica's](https://www.percona.com/wp-content/uploads/2026/03/Innodb-cluster-read-replica-2025.png)
  Caption: Async read replica auto source failover
- content / image: [mysql performance tuning](https://www.percona.com/wp-content/uploads/2026/03/mysql-performance-tuning-8.png)

## Auteur source

I am Anil Joshi, and I work for Percona as a support engineer. I've worked with some well-known Open Source database technologies (MySQL/MariaDB, MongoDB, and Redis) for almost ten years. I am keenly interested in learning new databases and writing database content.
