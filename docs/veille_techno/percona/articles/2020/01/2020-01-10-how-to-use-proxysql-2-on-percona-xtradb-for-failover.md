---
title: How to Use ProxySQL 2 on Percona XtraDB Cluster for Failover
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-use-proxysql-2-on-percona-xtradb-for-failover/
  post_id: 21301
source_author:
  name: Walter Garcia
  slug: walter-garcia
  url: https://www.percona.com/blog/author/walter-garcia/
  website: ''
published_at: '2020-01-10T15:18:16'
published_at_gmt: '2020-01-10T15:18:16'
modified_at: '2026-05-04T21:04:01'
modified_at_gmt: '2026-05-04T21:04:01'
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
- Percona Software
- ProxySQL
tag_slugs:
- mysql
- percona-software
- proxysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/xtradbcluster.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Use ProxySQL 2 on Percona XtraDB Cluster for Failover

Source: [Percona Blog](https://www.percona.com/blog/how-to-use-proxysql-2-on-percona-xtradb-for-failover/)

Auteur source: [Walter Garcia](https://www.percona.com/blog/author/walter-garcia/)

Publication: 2020-01-10T15:18:16

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

If you are thinking of using ProxySQL in our Percona XtraDB Cluster environment, I’ll explain how to use ProxySQL 2 for failover tasks. How to Test ProxySQL uses the “weight” column to define who is the WRITER node. For this example, I’ll use the following list of hostnames and IPs for references: +-----------+----------------+ | node_name | ip | +-----------+----------------+ | pxc1 | 192.168.88.134 | | pxc2 | 192.168.88.125 | | pxc3 | 192.168.88.132 | +-----------+----------------+ 1 2 3 4 5 6 7 + -- -- -- -- -- - + -- -- -- -- -- -- -- -- + | node_name | ip | + -- -- -- -- -- - + -- -- -- -- -- -- -- -- + | pxc1 | 192.168.88.134 | | pxc2 | 192.168.88.125 | | pxc3 | 192.168.88.132 | + -- -- -- -- -- - + -- -- -- -- -- -- -- -- + My current … Continued

## Structure detectee

- H2: How to Test
- H2: Observations
- H2: Summary

## Images et graphiques reperes

- featured / image: [How to Use ProxySQL 2 on Percona XtraDB Cluster for Failover](https://www.percona.com/wp-content/uploads/2026/03/xtradbcluster.png)
- content / image: [ProxySQL](https://www.percona.com/wp-content/uploads/2026/03/ProxySQL-2.0.5-300x168.jpg)

## Auteur source

Walter has worked as a DBA since 2010 in few companies like social gaming company in Latin America and other company in Spain. He lives in Mendoza, Argentina, He likes play football and he is learning to play guitar in his free time
