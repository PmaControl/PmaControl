---
title: 'Proof of Concept: Horizontal Write Scaling for MySQL With Kubernetes Operator'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/proof-of-concept-horizontal-write-scaling-for-mysql-with-kubernetes-operator/
  post_id: 26997
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2023-05-15T11:12:34'
published_at_gmt: '2023-05-15T11:12:34'
modified_at: '2026-03-26T20:29:40'
modified_at_gmt: '2026-03-26T20:29:40'
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
- search:percona-monitoring-and-management
- search:pmm
- search:proxysql
categories:
- Cloud
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- cloud
- insight-for-dbas
- mysql
- percona-software
tags:
- containers
- Kubernetes
- MySQL
- mysql-and-variants
- Percona Server for MySQL
- Scalability
tag_slugs:
- containers
- kubernetes
- mysql
- mysql-and-variants
- percona-server
- scalability
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/my-2.jpg
image_count: 10
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Proof of Concept: Horizontal Write Scaling for MySQL With Kubernetes Operator

Source: [Percona Blog](https://www.percona.com/blog/proof-of-concept-horizontal-write-scaling-for-mysql-with-kubernetes-operator/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2023-05-15T11:12:34

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Historically MySQL is great in horizontal READ scale. The scaling, in that case, is offered by the different number of Replica nodes, no matter if using standard asynchronous replication or synchronous replication. However, those solutions do not offer the same level of scaling for writes operation. Why? Because the solutions still rely on writing in … Continued

## Structure detectee

- H2: The POC
- H4: Why this POC?
- H4: Why ProxySQL?
- H4: Why POM?
- H3: The elements used
- H2: The settings
- H3: Data layer
- H3: ProxySQL and sharding rules
- H3: Setting up the dataset
- H3: Running the application
- H3: Backup
- H3: When will this solution fit in?
- H3: When will this solution not fit in?
- H2: Conclusions
- H2: References

## Images et graphiques reperes

- featured / image: [Proof of Concept: Horizontal Write Scaling for MySQL With Kubernetes Operator](https://www.percona.com/wp-content/uploads/2026/03/my-2.jpg)
- content / image: [data model for ecommerce](https://www.percona.com/wp-content/uploads/2026/03/schema-db.gif)
- content / image: [kiss.png](https://www.percona.com/wp-content/uploads/2026/03/kiss.png)
- content / image: [kubernetes sharding](https://www.percona.com/wp-content/uploads/2026/03/horizontal_scaling.jpg)
- content / image: [ProxySQL and sharding rules](https://www.percona.com/wp-content/uploads/2026/03/query_rukes_sharding-1024x359.png)
- content / image: [MySQL](https://www.percona.com/wp-content/uploads/2026/03/pxc_ps_write_questions-1024x187.png)
- content / image: [pxc_ps_write_com-1024x179.png](https://www.percona.com/wp-content/uploads/2026/03/pxc_ps_write_com-1024x179.png)
- content / image: [number_operation_writes.png](https://www.percona.com/wp-content/uploads/2026/03/number_operation_writes.png)
- content / image: [MySQL writes](https://www.percona.com/wp-content/uploads/2026/03/writes_write-1024x898.png)
- content / graph_or_chart: [MySQL latency](https://www.percona.com/wp-content/uploads/2026/03/latency_write-1024x898.png)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
