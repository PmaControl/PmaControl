---
title: 'MySQL 8: Load Fine Tuning With Resource Groups'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-8-load-fine-tuning-with-resource-groups/
  post_id: 19183
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2018-08-27T22:38:52'
published_at_gmt: '2018-08-27T22:38:52'
modified_at: '2026-05-05T20:20:53'
modified_at_gmt: '2026-05-05T20:20:53'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- ProxySQL
matched_filters:
- category:mysql:83
- search:proxysql
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Resource-Groups-e1535409459163.png
image_count: 21
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL 8: Load Fine Tuning With Resource Groups

Source: [Percona Blog](https://www.percona.com/blog/mysql-8-load-fine-tuning-with-resource-groups/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2018-08-27T22:38:52

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MySQL Resource Groups, introduced in MySQL 8, provide the ability to manipulate the assignment of running threads to specific resources, thereby allowing the DBA to manage application priorities. Essentially, you can assign a thread to a specific virtual CPU. In this post, I’m going to take a look at how these might work in practice. … Continued

## Structure detectee

- H2: Overview
- H3: What is the possible usage?
- H2: The Setup
- H2: Testing
- H3: Test 1
- H3: Test 2
- H3: Test 3
- H3: Test 4
- H2: Conclusion
- H3: References
- H3: You May Also Like

## Images et graphiques reperes

- featured / image: [MySQL 8: Load Fine Tuning With Resource Groups](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Resource-Groups-e1535409459163.png)
- content / image: [Test 1 master 1 current CPU core utilization](https://www.percona.com/wp-content/uploads/2026/03/master1_T1-1024x167.jpg)
- content / image: [master2_T1-1024x166.jpg](https://www.percona.com/wp-content/uploads/2026/03/master2_T1-1024x166.jpg)
- content / image: [execution_time1-1024x371.png](https://www.percona.com/wp-content/uploads/2026/03/execution_time1-1024x371.png)
- content / image: [events_by_crud1-1024x417.png](https://www.percona.com/wp-content/uploads/2026/03/events_by_crud1-1024x417.png)
- content / image: [Test 2 master 1 current CPU core utilization](https://www.percona.com/wp-content/uploads/2026/03/master1_T2app1-1024x175.jpg)
- content / image: [master1_T2app2-1024x190.jpg](https://www.percona.com/wp-content/uploads/2026/03/master1_T2app2-1024x190.jpg)
- content / image: [Test 2 slave1 current CPU core utilization](https://www.percona.com/wp-content/uploads/2026/03/master2_T2app1-1024x172.jpg)
- content / image: [Test 2 slave 2 current CPU core utilization](https://www.percona.com/wp-content/uploads/2026/03/master2_T2app2-1024x184.jpg)
- content / image: [execution_time2-1024x371.png](https://www.percona.com/wp-content/uploads/2026/03/execution_time2-1024x371.png)
- content / image: [events_by_crud2-1024x417.png](https://www.percona.com/wp-content/uploads/2026/03/events_by_crud2-1024x417.png)
- content / image: [master1_T3app2-1024x188.jpg](https://www.percona.com/wp-content/uploads/2026/03/master1_T3app2-1024x188.jpg)
- content / image: [master2_T3app2-1024x179.jpg](https://www.percona.com/wp-content/uploads/2026/03/master2_T3app2-1024x179.jpg)
- content / image: [execution_time3-1024x371.png](https://www.percona.com/wp-content/uploads/2026/03/execution_time3-1024x371.png)
- content / image: [events_by_crud3-1024x417.png](https://www.percona.com/wp-content/uploads/2026/03/events_by_crud3-1024x417.png)
- content / image: [master1_T4-1024x171.jpg](https://www.percona.com/wp-content/uploads/2026/03/master1_T4-1024x171.jpg)
- content / image: [master2_T4-1024x178.jpg](https://www.percona.com/wp-content/uploads/2026/03/master2_T4-1024x178.jpg)
- content / image: [execution_time4-1024x390.png](https://www.percona.com/wp-content/uploads/2026/03/execution_time4-1024x390.png)
- content / image: [events_by_crud4-1024x435.png](https://www.percona.com/wp-content/uploads/2026/03/events_by_crud4-1024x435.png)
- content / image: [execution_time-1024x390.png](https://www.percona.com/wp-content/uploads/2026/03/execution_time-1024x390.png)
- content / image: [events_by_crud-1024x437.png](https://www.percona.com/wp-content/uploads/2026/03/events_by_crud-1024x437.png)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
