---
title: Group Replication in Percona Server for MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/group-replication-in-percona-server-for-mysql/
  post_id: 21493
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2020-01-17T19:01:15'
published_at_gmt: '2020-01-17T19:01:15'
modified_at: '2026-04-27T21:29:44'
modified_at_gmt: '2026-04-27T21:29:44'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- MySQL
- Percona Server for MySQL
- Percona Software
tag_slugs:
- mysql
- percona-server
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/group-replication-percona-server-mysql.png
image_count: 11
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Group Replication in Percona Server for MySQL

Source: [Percona Blog](https://www.percona.com/blog/group-replication-in-percona-server-for-mysql/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2020-01-17T19:01:15

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona Server for MySQL 8.0.18 ships all functionality to run Group Replication and InnoDB Cluster setups, so I decided to evaluate how it works and how it compares with Percona XtraDB Cluster in some situations. For this I planned to use three bare metal nodes, SSD drives, and a 10Gb network available for in-between nodes … Continued

## Structure detectee

- H2: Load Data
- H3: Group Replication, Load Time
- H3: PXC 5.7.28, Load Time
- H3: PXC 8.0.15 Experimental, Load Time
- H3: One Node PXC 5.7.28 Load Time
- H2: Node Joining
- H3: Incremental
- H3: Incremental State Transfer in Percona XtraDB Cluster
- H3: Clone
- H3: SST in PXC 5.7.28
- H3: Clone and SST on NVMe Storage with 2x10Gb Network
- H2: Conclusions

## Images et graphiques reperes

- featured / image: [Group Replication in Percona Server for MySQL](https://www.percona.com/wp-content/uploads/2026/03/group-replication-percona-server-mysql.png)
- content / image: [group replication percona server mysql](https://www.percona.com/wp-content/uploads/2026/03/group-replication-percona-server-mysql-300x168.png)
- content / image: [one-1024x443.png](https://www.percona.com/wp-content/uploads/2026/03/one-1024x443.png)
- content / image: [two-1024x444.png](https://www.percona.com/wp-content/uploads/2026/03/two-1024x444.png)
- content / image: [three-1024x443.png](https://www.percona.com/wp-content/uploads/2026/03/three-1024x443.png)
- content / image: [four-1024x440.png](https://www.percona.com/wp-content/uploads/2026/03/four-1024x440.png)
- content / image: [five-1024x443.png](https://www.percona.com/wp-content/uploads/2026/03/five-1024x443.png)
- content / image: [six-1024x439.png](https://www.percona.com/wp-content/uploads/2026/03/six-1024x439.png)
- content / image: [seven-1024x436.png](https://www.percona.com/wp-content/uploads/2026/03/seven-1024x436.png)
- content / image: [eight-1024x441.png](https://www.percona.com/wp-content/uploads/2026/03/eight-1024x441.png)
- content / image: [nine-1024x446.png](https://www.percona.com/wp-content/uploads/2026/03/nine-1024x446.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
