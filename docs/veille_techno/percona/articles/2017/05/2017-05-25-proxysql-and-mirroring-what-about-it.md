---
title: What About ProxySQL and Mirroring?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/proxysql-and-mirroring-what-about-it/
  post_id: 16936
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2017-05-25T22:45:15'
published_at_gmt: '2017-05-25T22:45:15'
modified_at: '2026-05-05T18:41:56'
modified_at_gmt: '2026-05-05T18:41:56'
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
- category:proxysql:2261
- search:percona-monitoring-and-management
- search:pmm
- search:proxysql
- tag:percona-monitoring-and-management:2166
- tag:pmm:2167
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
- Data Mirroring
- load test
- MySQL
- Percona Monitoring and Management
- PMM
- ProxySQL
tag_slugs:
- data-mirroring
- load-test
- mysql
- percona-monitoring-and-management
- pmm
- proxysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/ProxySQL-and-Mirroring-e1495740928798.jpg
image_count: 24
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# What About ProxySQL and Mirroring?

Source: [Percona Blog](https://www.percona.com/blog/proxysql-and-mirroring-what-about-it/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2017-05-25T22:45:15

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll look at how ProxySQL and mirroring go together. Overview Let me be clear: I love ProxySQL, and I think it is a great component for expanding architecture flexibility and high availability. But not all that shines is gold! In this post, I want to correctly set some expectations, and avoid … Continued

## Structure detectee

- H2: Overview
- H3: Test 1
- H3: Test 2
- H3: Test 4 (two app node writing)
- H3: Test 7 (CRUD)
- H2: Conclusions
- H2: Acknowledgments

## Images et graphiques reperes

- featured / image: [What About ProxySQL and Mirroring?](https://www.percona.com/wp-content/uploads/2026/03/ProxySQL-and-Mirroring-e1495740928798.jpg)
- content / image: [ProxySQL and Mirroring](https://www.percona.com/wp-content/uploads/2026/03/cpu_proxy-1024x413.jpg)
- content / image: [ProxySQL and Mirroring](https://www.percona.com/wp-content/uploads/2026/03/question_galera-1024x323.jpg)
- content / image: [ProxySQL and Mirroring](https://www.percona.com/wp-content/uploads/2026/03/question_mysql-1024x442.jpg)
- content / image: [ProxySQL and Mirroring](https://www.percona.com/wp-content/uploads/2026/03/queue_proxy-1024x437.jpg)
- content / image: [ProxySQL and Mirroring](https://www.percona.com/wp-content/uploads/2026/03/test2.jpg)
- content / image: [ProxySQL and Mirroring](https://www.percona.com/wp-content/uploads/2026/03/queue_proxy-1-1024x438.jpg)
- content / image: [ProxySQL and Mirroring](https://www.percona.com/wp-content/uploads/2026/03/cpu_proxy-1-1024x436.jpg)
- content / image: [ProxySQL and Mirroring](https://www.percona.com/wp-content/uploads/2026/03/commands_galera-1024x466.jpg)
- content / image: [ProxySQL and Mirroring](https://www.percona.com/wp-content/uploads/2026/03/commands_mysql-1024x466.jpg)
- content / image: [ProxySQL and Mirroring](https://www.percona.com/wp-content/uploads/2026/03/test4.jpg)
- content / image: [command_galera-1024x461.jpg](https://www.percona.com/wp-content/uploads/2026/03/command_galera-1024x461.jpg)
- content / image: [command_mysql-1024x462.jpg](https://www.percona.com/wp-content/uploads/2026/03/command_mysql-1024x462.jpg)
- content / image: [cpu_galera.jpg](https://www.percona.com/wp-content/uploads/2026/03/cpu_galera.jpg)
- content / image: [cpu_mysql.jpg](https://www.percona.com/wp-content/uploads/2026/03/cpu_mysql.jpg)
- content / image: [cpu_proxy-2-1024x437.jpg](https://www.percona.com/wp-content/uploads/2026/03/cpu_proxy-2-1024x437.jpg)
- content / image: [commands_galera-1-1024x493.jpg](https://www.percona.com/wp-content/uploads/2026/03/commands_galera-1-1024x493.jpg)
- content / image: [commands_mysql-1-1024x457.jpg](https://www.percona.com/wp-content/uploads/2026/03/commands_mysql-1-1024x457.jpg)
- content / image: [threads_galera-1024x470.jpg](https://www.percona.com/wp-content/uploads/2026/03/threads_galera-1024x470.jpg)
- content / image: [threads_mysql-1024x443.jpg](https://www.percona.com/wp-content/uploads/2026/03/threads_mysql-1024x443.jpg)
- content / image: [questions_galera-1024x473.jpg](https://www.percona.com/wp-content/uploads/2026/03/questions_galera-1024x473.jpg)
- content / image: [questions_mysql-1024x439.jpg](https://www.percona.com/wp-content/uploads/2026/03/questions_mysql-1024x439.jpg)
- content / image: [cpu_galera-1.jpg](https://www.percona.com/wp-content/uploads/2026/03/cpu_galera-1.jpg)
- content / image: [cpu_mysql-1.jpg](https://www.percona.com/wp-content/uploads/2026/03/cpu_mysql-1.jpg)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
