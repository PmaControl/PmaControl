---
title: Achieving Consistent Read and High Availability with Percona XtraDB Cluster 8.0
source:
  name: Percona Blog
  url: https://www.percona.com/blog/achieving-consistent-read-and-high-availability-with-percona-xtradb-cluster-8-0/
  post_id: 22666
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2020-07-01T13:35:00'
published_at_gmt: '2020-07-01T13:35:00'
modified_at: '2026-05-05T16:30:26'
modified_at_gmt: '2026-05-05T16:30:26'
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
- Galera 4
- MySQL
- mysql-and-variants
- Percona Software
- Percona XtraDB Cluster
- ProxySQL
- PXC8
- stale reads
tag_slugs:
- galera-4
- mysql
- mysql-and-variants
- percona-software
- percona-xtradb-cluster
- proxysql
- pxc8
- stale-reads
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/High-Availability-with-Percona-XtraDB-Cluster-8.0.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Achieving Consistent Read and High Availability with Percona XtraDB Cluster 8.0

Source: [Percona Blog](https://www.percona.com/blog/achieving-consistent-read-and-high-availability-with-percona-xtradb-cluster-8-0/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2020-07-01T13:35:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In real life, there are frequent cases where getting a running application to work correctly is strongly dependent on consistent write/read operations. This is no issue when using a single data node as a provider, but it becomes more concerning and challenging when adding additional nodes for high availability and/or read scaling. In the MySQL … Continued

## Structure detectee

- H2: The Architecture
- H2: Installation
- H2: Covering Stale Reads
- H2: ProxySQL Requirements
- H2: Setting All Blocks

## Images et graphiques reperes

- featured / image: [Achieving Consistent Read and High Availability with Percona XtraDB Cluster 8.0](https://www.percona.com/wp-content/uploads/2026/03/High-Availability-with-Percona-XtraDB-Cluster-8.0.png)
- content / image: [High Availability with Percona XtraDB Cluster 8.0](https://www.percona.com/wp-content/uploads/2026/03/High-Availability-with-Percona-XtraDB-Cluster-8.0-300x168.png)
- content / image: [High Availability with Percona XtraDB Cluster](https://www.percona.com/wp-content/uploads/2026/03/architecture.png)
- content / image: [redflag-1.jpg](https://www.percona.com/wp-content/uploads/2026/03/redflag-1.jpg)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
