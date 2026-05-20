---
title: MySQL Sharding with ProxySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-sharding-with-proxysql/
  post_id: 15621
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2016-08-30T19:25:49'
published_at_gmt: '2016-08-30T19:25:49'
modified_at: '2026-05-05T18:16:10'
modified_at_gmt: '2026-05-05T18:16:10'
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
- MySQL
category_slugs:
- mysql
tags:
- MySQL
- proxy
- ProxySQL
- sharding
tag_slugs:
- mysql
- proxy
- proxysql
- sharding
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-and-Ceph-1-e1472584559356.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL Sharding with ProxySQL

Source: [Percona Blog](https://www.percona.com/blog/mysql-sharding-with-proxysql/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2016-08-30T19:25:49

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This article demonstrates how MySQL sharding with ProxySQL works. Recently a colleague of mine asked me to provide a simple example on how ProxySQL performs sharding. In response, I’m writing this short tutorial in the hope it will illustrate ProxySQL’s sharding functionalities, and help people out there better understand how to use it. ProxySQL is … Continued

## Structure detectee

- H2: Shard inside the same MySQL Server using three different schemas split by continent
- H2: Sharding by host
- H3: Using hint
- H3: Using destination_hostgroup
- H2: Shard by host and by schema
- H2: Conclusion
- H3: References:
- H2: Credits

## Images et graphiques reperes

- featured / image: [MySQL Sharding with ProxySQL](https://www.percona.com/wp-content/uploads/2026/03/MySQL-and-Ceph-1-e1472584559356.jpg)
- content / image: [MySQL Sharding with ProxySQL](https://www.percona.com/wp-content/uploads/2026/03/MySQL-and-Ceph-1.jpg)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
