---
title: Leveraging ProxySQL with AWS Aurora to Improve Performance
source:
  name: Percona Blog
  url: https://www.percona.com/blog/leveraging-proxysql-with-aws-aurora-for-performance/
  post_id: 18442
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2018-04-03T18:51:00'
published_at_gmt: '2018-04-03T18:51:00'
modified_at: '2026-03-20T21:47:53'
modified_at_gmt: '2026-03-20T21:47:53'
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
- Cloud
- MySQL
- ProxySQL
category_slugs:
- cloud
- mysql
- proxysql
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/ProxySQL-with-AWS-Aurora.png
image_count: 22
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Leveraging ProxySQL with AWS Aurora to Improve Performance

Source: [Percona Blog](https://www.percona.com/blog/leveraging-proxysql-with-aws-aurora-for-performance/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2018-04-03T18:51:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, I’ll look at how you can use ProxySQL with AWS Aurora to further leverage database performance. My previous article described how easy is to replace the native Aurora connector with ProxySQL. In this article, you will see WHY you should do that. It is important to understand that aside from the basic optimization … Continued

## Structure detectee

- H2: The tests
- H2: The Results
- H2: Sysbench
- H3: Read Only
- H3: Write Only
- H3: Read and Write
- H2: Java Application Tests
- H2: But Why?
- H2: Conclusions
- H2: You May Also Like

## Images et graphiques reperes

- featured / image: [Leveraging ProxySQL with AWS Aurora to Improve Performance](https://www.percona.com/wp-content/uploads/2026/03/ProxySQL-with-AWS-Aurora.png)
- content / image: [summary_sysbench.png](https://www.percona.com/wp-content/uploads/2026/03/summary_sysbench.png)
- content / image: [summary_for_java_app-300x86.png](https://www.percona.com/wp-content/uploads/2026/03/summary_for_java_app-300x86.png)
- content / image: [read_events-300x135.png](https://www.percona.com/wp-content/uploads/2026/03/read_events-300x135.png)
- content / image: [reads_latency-300x132.png](https://www.percona.com/wp-content/uploads/2026/03/reads_latency-300x132.png)
- content / image: [Screen-Shot-2018-03-26-at-7.17.04-PM.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2018-03-26-at-7.17.04-PM.png)
- content / image: [Screen-Shot-2018-03-26-at-7.17.20-PM.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2018-03-26-at-7.17.20-PM.png)
- content / image: [reads_reads-300x135.png](https://www.percona.com/wp-content/uploads/2026/03/reads_reads-300x135.png)
- content / image: [reads_sysb_queries-300x206.png](https://www.percona.com/wp-content/uploads/2026/03/reads_sysb_queries-300x206.png)
- content / image: [write_events_sysb-300x135.png](https://www.percona.com/wp-content/uploads/2026/03/write_events_sysb-300x135.png)
- content / image: [write_latency_sysb-300x132.png](https://www.percona.com/wp-content/uploads/2026/03/write_latency_sysb-300x132.png)
- content / image: [rw_writes_sysb-300x134.png](https://www.percona.com/wp-content/uploads/2026/03/rw_writes_sysb-300x134.png)
- content / image: [write_sysb_queries-300x233.png](https://www.percona.com/wp-content/uploads/2026/03/write_sysb_queries-300x233.png)
- content / image: [rw_events_sysb-300x135.png](https://www.percona.com/wp-content/uploads/2026/03/rw_events_sysb-300x135.png)
- content / image: [rw_latency_sysb-300x133.png](https://www.percona.com/wp-content/uploads/2026/03/rw_latency_sysb-300x133.png)
- content / image: [rw_queies_sysb-300x216.png](https://www.percona.com/wp-content/uploads/2026/03/rw_queies_sysb-300x216.png)
- content / image: [app_con_latency_summary-300x182.png](https://www.percona.com/wp-content/uploads/2026/03/app_con_latency_summary-300x182.png)
- content / image: [app_crud_summary-300x225.png](https://www.percona.com/wp-content/uploads/2026/03/app_crud_summary-300x225.png)
- content / image: [app_evnts_summary-300x186.png](https://www.percona.com/wp-content/uploads/2026/03/app_evnts_summary-300x186.png)
- content / image: [app_exectime_summary-300x240.png](https://www.percona.com/wp-content/uploads/2026/03/app_exectime_summary-300x240.png)
- content / image: [cross-server-graphs-300x150.png](https://www.percona.com/wp-content/uploads/2026/03/cross-server-graphs-300x150.png)
- content / image: [cpu_utilization-300x183.png](https://www.percona.com/wp-content/uploads/2026/03/cpu_utilization-300x183.png)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
