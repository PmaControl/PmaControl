---
title: Support for Percona XtraDB Cluster in ProxySQL (Part One)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/support-for-percona-xtradb-cluster-in-proxysql-part-one/
  post_id: 23482
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2020-11-30T15:13:46'
published_at_gmt: '2020-11-30T15:13:46'
modified_at: '2026-04-27T22:18:03'
modified_at_gmt: '2026-04-27T22:18:03'
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
- Open Source
- Percona Software
- ProxySQL
category_slugs:
- mysql
- open-source
- percona-software
- proxysql
tags:
- business continuity
- High Availability
- mysql-and-variants
- Percona Software
- ProxySQL
- testing
tag_slugs:
- business-continuity
- high-availability
- mysql-and-variants
- percona-software
- proxysql
- testing
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Support-for-Percona-XtraDB-Cluster-in-ProxySQL-One.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Support for Percona XtraDB Cluster in ProxySQL (Part One)

Source: [Percona Blog](https://www.percona.com/blog/support-for-percona-xtradb-cluster-in-proxysql-part-one/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2020-11-30T15:13:46

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

How native ProxySQL stands in failover support (both v2.0.15 and v2.1.0) In recent times I have been designing several solutions focused on High Availability and Disaster Recovery. Some of them using Percona Server for MySQL with group replication, some using Percona XtraDB Cluster (PXC). What many of them had in common was the use of … Continued

## Structure detectee

- H2: What I Have Tested
- H2: PXC- Failover Scenario
- H2: The Tests
- H3: First Test
- H3: Second Test
- H3: Third Test
- H3: Fourth Test
- H4: Failover Because of a Crash
- H3: A Problem Along the Road… (only with v2.0.15)
- H3: Conclusions
- H3: References

## Images et graphiques reperes

- featured / image: [Support for Percona XtraDB Cluster in ProxySQL (Part One)](https://www.percona.com/wp-content/uploads/2026/03/Support-for-Percona-XtraDB-Cluster-in-ProxySQL-One.png)
- content / image: [Support for Percona XtraDB Cluster in ProxySQL](https://www.percona.com/wp-content/uploads/2026/03/Support-for-Percona-XtraDB-Cluster-in-ProxySQL-One-300x168.png)
- content / image: [tightly-coupled-274x300.png](https://www.percona.com/wp-content/uploads/2026/03/tightly-coupled-274x300.png)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
