---
title: ProxySQL and Percona XtraDB Cluster (Galera) Integration
source:
  name: Percona Blog
  url: https://www.percona.com/blog/proxysql-percona-cluster-galera-integration/
  post_id: 15659
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2016-09-15T22:37:20'
published_at_gmt: '2016-09-15T22:37:20'
modified_at: '2026-04-28T00:28:53'
modified_at_gmt: '2026-04-28T00:28:53'
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
- Insight for DBAs
- MySQL
- Percona Software
- ProxySQL
category_slugs:
- insight-for-dbas
- mysql
- percona-software
- proxysql
tags:
- High Availability
- Percona XtraDB Cluster with ProxySQL
- ProxySQL
tag_slugs:
- high-availability
- percona-xtradb-cluster-with-proxysql
- proxysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/galera_proxy.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# ProxySQL and Percona XtraDB Cluster (Galera) Integration

Source: [Percona Blog](https://www.percona.com/blog/proxysql-percona-cluster-galera-integration/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2016-09-15T22:37:20

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this post, we’ll discuss how an integrated ProxySQL and Percona XtraDB Cluster (Galera) helps manage node states and failovers. ProxySQL is designed to not perform any specialized operation in relation to the servers with which it communicates. Instead, it uses an event scheduler to extend functionalities and cover any special needs. Given that specialized products … Continued

## Structure detectee

- H2: Brief digression
- H2: Percona XtraDB Cluster/Galera Integration
- H3: Multi-writer mode
- H3: ProxySQL and PXC using Replication HostGroup
- H4: Let see the manual procedure first:
- H4: Let see the automatic procedure now:
- H2: Conclusions

## Images et graphiques reperes

- featured / image: [ProxySQL and Percona XtraDB Cluster (Galera) Integration](https://www.percona.com/wp-content/uploads/2026/03/galera_proxy.png)
- content / image: [ProxySQL and Percona XtraDB Cluster](https://www.percona.com/wp-content/uploads/2026/04/galera_proxy-300x300-1.png)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
