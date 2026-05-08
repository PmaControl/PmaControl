---
title: 'Better high availability: MySQL and Percona XtraDB Cluster with good application design'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-and-percona-xtradb-cluster-even-higher-availability-with-correct-application-design/
  post_id: 10304
source_author:
  name: Przemysław Malkowski
  slug: przemek-malkowski
  url: https://www.percona.com/blog/author/przemek-malkowski/
  website: ''
published_at: '2015-12-21T21:19:00'
published_at_gmt: '2015-12-21T21:19:00'
modified_at: '2026-05-05T22:33:28'
modified_at_gmt: '2026-05-05T22:33:28'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
categories:
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- datacenter
- MySQL
- nodes
- Percona XtraDB Cluster
- Przemek Malkowski
- pxc
- Quorum
- read only
tag_slugs:
- datacenter
- mysql
- nodes
- percona-xtradb-cluster
- przemek-malkowski
- pxc
- quorum
- read-only
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/ha.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Better high availability: MySQL and Percona XtraDB Cluster with good application design

Source: [Percona Blog](https://www.percona.com/blog/mysql-and-percona-xtradb-cluster-even-higher-availability-with-correct-application-design/)

Auteur source: [Przemysław Malkowski](https://www.percona.com/blog/author/przemek-malkowski/)

Publication: 2015-12-21T21:19:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

High Availability Have you ever wondered if your application should be able to work in read-only mode? How important is that question? MySQL seems to be the most popular database solution for web-based products. Most typical Internet application workloads consist of many reads, with usually few writes. There are exceptions of course – MMO games … Continued

## Structure detectee

- H2: High Availability
- H2: PXC
- H3: Focus on data consistency
- H3: Anti-split-brain
- H3: Dirty reads from Galera cluster
- H2: MySQL
- H3: Useful links

## Images et graphiques reperes

- featured / image: [Better high availability: MySQL and Percona XtraDB Cluster with good application design](https://www.percona.com/wp-content/uploads/2026/03/ha.png)
- content / image: [high availability](https://www.percona.com/wp-content/uploads/2026/03/ha-1.png)
- content / image: [WAN_PXC](https://www.percona.com/wp-content/uploads/2026/03/WAN_PXC.png)

## Auteur source

Przemek joined Support Team at Percona in August 2012. Before that he spent over five years working for Wikia.com (Quantcast Top 50) as System Administrator where he was a key person responsible for seamless building up MySQL powered database infrastructure. Besides MySQL he worked on maintaining all other parts of LAMP stack, with main focus on automation, monitoring and backups.
