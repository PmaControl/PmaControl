---
title: 'Group Replication and Percona XtraDB Cluster: Overview of Common Operations'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/group-replication-and-percona-xtradb-cluster-overview-of-common-operations/
  post_id: 22050
source_author:
  name: Tibor Korocz
  slug: tibor-koroczpercona-com
  url: https://www.percona.com/blog/author/tibor-koroczpercona-com/
  website: ''
published_at: '2020-04-28T14:01:36'
published_at_gmt: '2020-04-28T14:01:36'
modified_at: '2026-03-23T15:11:48'
modified_at_gmt: '2026-03-23T15:11:48'
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
- MySQL
- Percona Software
- ProxySQL
category_slugs:
- mysql
- percona-software
- proxysql
tags:
- MySQL
- MySQL Group Replication
- Percona Software
- Percona XtraDB Cluster
- ProxySQL
tag_slugs:
- mysql
- mysql-group-replication
- percona-software
- percona-xtradb-cluster
- proxysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/group-replication-percona-xtradb-cluster.png
image_count: 6
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Group Replication and Percona XtraDB Cluster: Overview of Common Operations

Source: [Percona Blog](https://www.percona.com/blog/group-replication-and-percona-xtradb-cluster-overview-of-common-operations/)

Auteur source: [Tibor Korocz](https://www.percona.com/blog/author/tibor-koroczpercona-com/)

Publication: 2020-04-28T14:01:36

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post I would like to give an overview of the most common failover scenarios and operations when using MySQL Group Replication 8.0.19 (aka GR) and Percona XtraDB Cluster 8 (PXC) (which is based on Galera), and explain how each technology handles each situation. I have created a three-node cluster with Group Replication … Continued

## Structure detectee

- H2: Primary Server Crashes
- H4: Group Replication – Writing
- H4: Group Replication – Reading
- H4: Percona XtraDB Cluster – Writing/Reading
- H2: Removing/Adding Node
- H4: Group Replication
- H4: Percona XtraDB Cluster
- H2: Partial Network Failure
- H4: Group Replication
- H4: Percona XtraDB Cluster
- H2: Total Network Isolation
- H4: Group Replication
- H4: Percona XtraDB Cluster
- H2: Local Applications
- H4: Group Replication
- H4: Percona XtraDB Cluster
- H2: Changing Primary
- H4: Group Replication
- H4: Percona XtraDB Cluster
- H2: Summary

## Images et graphiques reperes

- featured / image: [Group Replication and Percona XtraDB Cluster: Overview of Common Operations](https://www.percona.com/wp-content/uploads/2026/03/group-replication-percona-xtradb-cluster.png)
- content / image: [Group Replication](https://www.percona.com/wp-content/uploads/2026/03/blogpost1-1.png)
- content / image: [primary server crashes](https://www.percona.com/wp-content/uploads/2026/03/blogpost2.png)
- content / image: [Partial Network Failure](https://www.percona.com/wp-content/uploads/2026/03/blogpost3.png)
- content / image: [Total Network Isolation](https://www.percona.com/wp-content/uploads/2026/03/blogpost5.png)
- content / image: [Local Applications](https://www.percona.com/wp-content/uploads/2026/03/blogpost6.png)

## Auteur source

Tibi joined Percona in 2015 as a Consultant. Before joining Percona, among many other things, he worked at the world’s largest car hire booking service as a Senior Database Engineer. He enjoys trying and working with the latest technologies and applications which can help or work with MySQL together. In his spare time he likes to spend time with his friends, travel around the world and play ultimate frisbee.
