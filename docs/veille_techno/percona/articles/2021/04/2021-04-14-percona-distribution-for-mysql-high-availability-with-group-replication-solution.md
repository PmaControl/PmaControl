---
title: 'Percona Distribution for MySQL: High Availability with Group Replication Solution'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-distribution-for-mysql-high-availability-with-group-replication-solution/
  post_id: 24167
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2021-04-14T13:06:21'
published_at_gmt: '2021-04-14T13:06:21'
modified_at: '2026-04-28T01:11:23'
modified_at_gmt: '2026-04-28T01:11:23'
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
- Insight for DBAs
- MySQL
- Open Source
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- open-source
- percona-software
tags:
- business continuity
- High Availability
- MySQL
- MySQL best practices
- mysql-and-variants
- Percona Software
tag_slugs:
- business-continuity
- high-availability
- mysql
- mysql-best-practices
- mysql-and-variants
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/High-Availability-Group-Replication-MySQL.png
image_count: 8
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona Distribution for MySQL: High Availability with Group Replication Solution

Source: [Percona Blog](https://www.percona.com/blog/percona-distribution-for-mysql-high-availability-with-group-replication-solution/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2021-04-14T13:06:21

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This blog provides high availability (HA) guidelines using group replication architecture and deployment recommendations in MySQL, based on our best practices. Every architecture and deployment depends on the customer requirements and application demands for high availability and the estimated level of usage. For example, using high read or high write applications, or both, with a … Continued

## Structure detectee

- H3: Layout
- H2: Components
- H3: Connection Layer
- H3: Data Layer
- H2: High Availability
- H3: How is High Availability Achieved?
- H3: This Solution Provides:
- H2: Failovers
- H2: Maintenance Windows
- H2: Uptime
- H2: Measurement and Monitoring
- H2: How to Implement the Infrastructure
- H3: The Elements
- H3: Software Installation
- H2: Configure the Nodes
- H3: Step 1
- H3: Step 2
- H3: Step 3
- H2: Proxy Setup
- H3: Step 1
- H3: Step 2
- H3: Step 3
- H3: Step 4
- H2: Proxy HA
- H2: Disaster Recovery Implementation
- H2: Monitoring
- H3: Percona Monitoring and Management
- H3: From Command Line
- H3: Conclusions

## Images et graphiques reperes

- featured / image: [Percona Distribution for MySQL: High Availability with Group Replication Solution](https://www.percona.com/wp-content/uploads/2026/03/High-Availability-Group-Replication-MySQL.png)
- content / image: [High-Availability-Group-Replication-MySQL-300x157-1.png](https://www.percona.com/wp-content/uploads/2026/04/High-Availability-Group-Replication-MySQL-300x157-1.png)
- content / image: [MySQL High Availability with Group Replication](https://www.percona.com/wp-content/uploads/2026/04/group_replication_ha.png)
- content / image: [group replication](https://www.percona.com/wp-content/uploads/2026/04/group-replication-1.png)
- content / image: [MySQL Group Replication](https://www.percona.com/wp-content/uploads/2026/04/pmm_for_gr_overiew-1024x448-1.png)
- content / image: [pmm_for_gr-1024x451-1.png](https://www.percona.com/wp-content/uploads/2026/04/pmm_for_gr-1024x451-1.png)
- content / image: [pmm_for_gr_trx-1024x490-1.png](https://www.percona.com/wp-content/uploads/2026/04/pmm_for_gr_trx-1024x490-1.png)
- content / image: [pmm_for_gr_conflicts-1024x159-1.png](https://www.percona.com/wp-content/uploads/2026/04/pmm_for_gr_conflicts-1024x159-1.png)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
