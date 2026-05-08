---
title: Full Read Consistency Within Percona Operator for MySQL Based on Percona XtraDB Cluster
source:
  name: Percona Blog
  url: https://www.percona.com/blog/full-read-consistency-within-percona-kubernetes-operator-for-percona-xtradb-cluster/
  post_id: 23697
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2021-01-11T15:05:19'
published_at_gmt: '2021-01-11T15:05:19'
modified_at: '2026-05-05T16:35:58'
modified_at_gmt: '2026-05-05T16:35:58'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- ProxySQL
matched_filters:
- category:mysql:83
- category:proxysql:2261
- search:pmm
- search:proxysql
categories:
- Cloud
- MySQL
- Percona Software
- ProxySQL
category_slugs:
- cloud
- mysql
- percona-software
- proxysql
tags:
- haproxy
- Kubernetes
- mysql-and-variants
- Percona Software
- stale reads
tag_slugs:
- haproxy
- kubernetes
- mysql-and-variants
- percona-software
- stale-reads
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Full-Read-Consistency-Within-Percona-Kubernetes-Operator.png
image_count: 9
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Full Read Consistency Within Percona Operator for MySQL Based on Percona XtraDB Cluster

Source: [Percona Blog](https://www.percona.com/blog/full-read-consistency-within-percona-kubernetes-operator-for-percona-xtradb-cluster/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2021-01-11T15:05:19

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The aim of Percona Operator for MySQL based on Percona XtraDB Cluster is to be a special type of controller introduced to simplify complex deployments. The Operator extends the Kubernetes API with custom resources. The Operator solution is using Percona XtraDB Cluster (PXC) behind the hood to provide a highly available, resilient, and scalable MySQL … Continued

## Structure detectee

- H2: Stale Reads
- H2: What Is The Impact?
- H3: Results
- H3: Conclusions
- H4: References

## Images et graphiques reperes

- featured / image: [Full Read Consistency Within Percona Operator for MySQL Based on Percona XtraDB Cluster](https://www.percona.com/wp-content/uploads/2026/03/Full-Read-Consistency-Within-Percona-Kubernetes-Operator.png)
- content / image: [Full Read Consistency Within Percona Kubernetes Operator](https://www.percona.com/wp-content/uploads/2026/03/Full-Read-Consistency-Within-Percona-Kubernetes-Operator-300x168.png)
- content / image: [( https://www.slideshare.net/lefred.descamps/galera-replication-demystified-how-does-it-work ) from Fred Descamps)](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2019-10-13-at-3.27.14-PM-1024x788.png)
  Caption: ( https://www.slideshare.net/lefred.descamps/galera-replication-demystified-how-does-it-work ) from Fred Descamps)
- content / image: [haproxy.png](https://www.percona.com/wp-content/uploads/2026/03/haproxy.png)
- content / image: [proxysql-7.png](https://www.percona.com/wp-content/uploads/2026/03/proxysql-7.png)
- content / image: [redflag-2.jpg](https://www.percona.com/wp-content/uploads/2026/03/redflag-2.jpg)
- content / image: [stale_reads_moderate_load.png](https://www.percona.com/wp-content/uploads/2026/03/stale_reads_moderate_load.png)
- content / image: [performance-_loss_reads.png](https://www.percona.com/wp-content/uploads/2026/03/performance-_loss_reads.png)
- content / image: [performance-_loss_writes.png](https://www.percona.com/wp-content/uploads/2026/03/performance-_loss_writes.png)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
