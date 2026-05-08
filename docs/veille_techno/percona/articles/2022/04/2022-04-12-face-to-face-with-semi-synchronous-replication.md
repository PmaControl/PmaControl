---
title: Face to Face with Semi-Synchronous Replication
source:
  name: Percona Blog
  url: https://www.percona.com/blog/face-to-face-with-semi-synchronous-replication/
  post_id: 25557
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2022-04-12T12:22:38'
published_at_gmt: '2022-04-12T12:22:38'
modified_at: '2026-03-26T20:31:59'
modified_at_gmt: '2026-03-26T20:31:59'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:percona-xtrabackup
- search:xtrabackup
categories:
- Cloud
- MySQL
- Percona Software
category_slugs:
- cloud
- mysql
- percona-software
tags:
- Kubernetes
- MySQL
- mysql-and-variants
- Operator
- Replication
tag_slugs:
- kubernetes
- mysql
- mysql-and-variants
- operator
- replication
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Semi-Synchronous-Replication.png
image_count: 15
graph_or_chart_count: 2
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Face to Face with Semi-Synchronous Replication

Source: [Percona Blog](https://www.percona.com/blog/face-to-face-with-semi-synchronous-replication/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2022-04-12T12:22:38

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Last month I performed a review of the Percona Operator for MySQL Server which is still Alpha. That operator is based on Percona Server for MySQL and uses standard asynchronous replication, with the option to activate semi-synchronous replication to gain higher levels of data consistency between nodes. The whole solution is composed as: Additionally, Orchestrator … Continued

## Structure detectee

- H2: A Look into Semi-Synchronous
- H2: Semi-Synchronous is Not Seriously Affecting the Performance
- H2: But at Least Your Data is Safe
- H2: Conclusion
- H2: References

## Images et graphiques reperes

- featured / image: [Face to Face with Semi-Synchronous Replication](https://www.percona.com/wp-content/uploads/2026/03/Semi-Synchronous-Replication.png)
- content / image: [Semi-Synchronous Replication](https://www.percona.com/wp-content/uploads/2026/03/Semi-Synchronous-Replication-300x157.png)
- content / image: [semi-synchronous replication](https://www.percona.com/wp-content/uploads/2026/03/operator.svg)
- content / graph_or_chart: [Asynchronous](https://www.percona.com/wp-content/uploads/2026/03/async-replication-diagram.png)
- content / graph_or_chart: [Semi-sync](https://www.percona.com/wp-content/uploads/2026/03/semisync-replication-diagram.png)
- content / image: [wait_whaat.jpeg](https://www.percona.com/wp-content/uploads/2026/03/wait_whaat.jpeg)
- content / image: [no semi-sync](https://www.percona.com/wp-content/uploads/2026/03/2-15-1024x456.png)
- content / image: [no semi-sync details write](https://www.percona.com/wp-content/uploads/2026/03/3-16-1024x479.png)
- content / image: [read/sec semi-sync](https://www.percona.com/wp-content/uploads/2026/03/9-5-1024x479.png)
- content / image: [4 cpu semi-sync](https://www.percona.com/wp-content/uploads/2026/03/10-7-1024x478.png)
- content / image: [MySQL Operator async replication](https://www.percona.com/wp-content/uploads/2026/03/1c-scaled.png)
- content / image: [Asynchronous replication](https://www.percona.com/wp-content/uploads/2026/03/4-16-1024x598.png)
- content / image: [Semi-synchronous](https://www.percona.com/wp-content/uploads/2026/03/8-8-1024x479.png)
- content / image: [tpcc transaction distance](https://www.percona.com/wp-content/uploads/2026/03/5-10-1024x597.png)
- content / image: [Learn More About Percona XtraDB Cluster](https://www.percona.com/wp-content/uploads/2026/03/bbb02c22-9c32-4424-ba05-d1fc6f336111.png)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
