---
title: 'Group Replication: The Sweet and the Sour'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/group-replication-sweet-sour/
  post_id: 17153
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2017-08-01T15:54:57'
published_at_gmt: '2017-08-01T15:54:57'
modified_at: '2026-05-05T18:46:38'
modified_at_gmt: '2026-05-05T18:46:38'
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
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- group replication
- High Availability
- Innodb cluster
- Percona XtraDB Cluster
tag_slugs:
- group-replication
- high-availability
- innodb-cluster
- percona-xtradb-cluster
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/group-replication-1.png
image_count: 10
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Group Replication: The Sweet and the Sour

Source: [Percona Blog](https://www.percona.com/blog/group-replication-sweet-sour/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2017-08-01T15:54:57

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog, we’ll look at group replication and how it deals with flow control (FC) and replication lag. Overview In the last few months, we had two main actors in the MySQL ecosystem: ProxySQL and Group-Replication (with the evolution to InnoDB Cluster). While I have extensively covered the first, my last serious work on … Continued

## Structure detectee

- H2: Overview
- H2: The POC
- H2: Test Definition
- H2: The Results
- H3: Efficiency on Writer by Execution Time and Rows/Sec
- H3: Entries Lag
- H3: A Graph That Tells Us a Story
- H2: Conclusions

## Images et graphiques reperes

- featured / image: [Group Replication: The Sweet and the Sour](https://www.percona.com/wp-content/uploads/2026/03/group-replication-1.png)
- content / image: [writer_efficency_bytime-1024x515.png](https://www.percona.com/wp-content/uploads/2026/03/writer_efficency_bytime-1024x515.png)
- content / image: [writer_efficency_by_rows-1024x556.png](https://www.percona.com/wp-content/uploads/2026/03/writer_efficency_by_rows-1024x556.png)
- content / image: [Writer_difference_data_time_fc_nofc-1024x446.png](https://www.percona.com/wp-content/uploads/2026/03/Writer_difference_data_time_fc_nofc-1024x446.png)
- content / image: [Writer_slave_real_lag_all_tests-1024x624.png](https://www.percona.com/wp-content/uploads/2026/03/Writer_slave_real_lag_all_tests-1024x624.png)
- content / image: [Writer_slave_real_lag_all_tests_wFC-1024x608.png](https://www.percona.com/wp-content/uploads/2026/03/Writer_slave_real_lag_all_tests_wFC-1024x608.png)
- content / image: [Writer_slave_distance_without_FC-1024x608.png](https://www.percona.com/wp-content/uploads/2026/03/Writer_slave_distance_without_FC-1024x608.png)
- content / image: [Writer_slave_distance_with_FC-1024x567.png](https://www.percona.com/wp-content/uploads/2026/03/Writer_slave_distance_with_FC-1024x567.png)
- content / image: [test8.png](https://www.percona.com/wp-content/uploads/2026/03/test8.png)
- content / image: [test4.png](https://www.percona.com/wp-content/uploads/2026/03/test4.png)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
