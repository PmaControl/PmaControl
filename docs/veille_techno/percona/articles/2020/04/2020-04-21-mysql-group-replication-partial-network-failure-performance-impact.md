---
title: MySQL Group Replication – Partial Network Failure Performance Impact
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-group-replication-partial-network-failure-performance-impact/
  post_id: 22031
source_author:
  name: Tibor Korocz
  slug: tibor-koroczpercona-com
  url: https://www.percona.com/blog/author/tibor-koroczpercona-com/
  website: ''
published_at: '2020-04-21T17:10:56'
published_at_gmt: '2020-04-21T17:10:56'
modified_at: '2026-04-27T21:34:50'
modified_at_gmt: '2026-04-27T21:34:50'
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
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- MySQL
- MySQL Group Replication
- Percona Software
- Percona XtraDB Cluster
- pxc
tag_slugs:
- mysql
- mysql-group-replication
- percona-software
- percona-xtradb-cluster
- pxc
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/mysql-group-replication.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL Group Replication – Partial Network Failure Performance Impact

Source: [Percona Blog](https://www.percona.com/blog/mysql-group-replication-partial-network-failure-performance-impact/)

Auteur source: [Tibor Korocz](https://www.percona.com/blog/author/tibor-koroczpercona-com/)

Publication: 2020-04-21T17:10:56

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this two-part blog series, I wanted to cover some failover scenarios with Group Replication. In part one, I will discuss an interesting behavior and performance degradation I discovered while writing these posts. In part two, I will show several failover scenarios and demonstrate how Group Replication handles each situation. The test environment is very … Continued

## Structure detectee

- H3: But What Does This Mean?
- H3: Serious Performance Degradation
- H3: How Does This Work with Percona XtraDB Cluster?
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [MySQL Group Replication – Partial Network Failure Performance Impact](https://www.percona.com/wp-content/uploads/2026/03/mysql-group-replication.png)
- content / image: [GR1.png](https://www.percona.com/wp-content/uploads/2026/03/GR1.png)
- content / image: [GR2.png](https://www.percona.com/wp-content/uploads/2026/03/GR2.png)
- content / image: [UghjSBIJoL.gif](https://www.percona.com/wp-content/uploads/2026/03/UghjSBIJoL.gif)

## Auteur source

Tibi joined Percona in 2015 as a Consultant. Before joining Percona, among many other things, he worked at the world’s largest car hire booking service as a Senior Database Engineer. He enjoys trying and working with the latest technologies and applications which can help or work with MySQL together. In his spare time he likes to spend time with his friends, travel around the world and play ultimate frisbee.
