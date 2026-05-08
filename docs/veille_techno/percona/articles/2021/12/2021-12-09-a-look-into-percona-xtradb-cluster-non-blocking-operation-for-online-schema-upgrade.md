---
title: A Look Into Percona XtraDB Cluster Non-Blocking Operation for Online Schema Upgrade
source:
  name: Percona Blog
  url: https://www.percona.com/blog/a-look-into-percona-xtradb-cluster-non-blocking-operation-for-online-schema-upgrade/
  post_id: 25173
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2021-12-09T14:32:19'
published_at_gmt: '2021-12-09T14:32:19'
modified_at: '2026-05-05T17:36:49'
modified_at_gmt: '2026-05-05T17:36:49'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
- search:pmm
categories:
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- ddl
- galera
- High Availability
- MySQL
- mysql-and-variants
- NBO
- Percona XtraDB Cluster
- tightly coupled cluster
tag_slugs:
- ddl
- galera
- high-availability
- mysql
- mysql-and-variants
- nbo
- percona-xtradb-cluster
- tightly-coupled-cluster
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-Non-Blocking-Operation.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# A Look Into Percona XtraDB Cluster Non-Blocking Operation for Online Schema Upgrade

Source: [Percona Blog](https://www.percona.com/blog/a-look-into-percona-xtradb-cluster-non-blocking-operation-for-online-schema-upgrade/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2021-12-09T14:32:19

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona XtraDB Cluster 8.0.25 (PXC) has introduced a new option to perform online schema modifications: NBO (Non-Blocking Operation). When using PXC, the cluster relies on the wsrep_OSU_method parameter to define the Online Schema Upgrade (OSU) method the node uses to replicate DDL statements. Until now, we normally have three options: Use Total Isolation Order (TOI, … Continued

## Structure detectee

- H3: The Commands
- H3: Operations
- H3: Let’s Run It
- H2: Altering a Table with TOI
- H2: Let’s Now Try With NBO
- H2: What is Happening? What are the Differences and Why Does it Take Longer with NBO?
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [A Look Into Percona XtraDB Cluster Non-Blocking Operation for Online Schema Upgrade](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-Non-Blocking-Operation.png)
- content / image: [Percona XtraDB Cluster Non-Blocking Operation](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-Non-Blocking-Operation-300x157.png)
- content / image: [breaking_bariers.jpg](https://www.percona.com/wp-content/uploads/2026/03/breaking_bariers.jpg)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
