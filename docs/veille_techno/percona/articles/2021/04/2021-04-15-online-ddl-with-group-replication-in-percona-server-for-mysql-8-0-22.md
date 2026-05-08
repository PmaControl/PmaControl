---
title: Online DDL with Group Replication In Percona Server for MySQL 8.0.22 (and MySQL 8.0.23)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/online-ddl-with-group-replication-in-percona-server-for-mysql-8-0-22/
  post_id: 24202
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2021-04-15T16:06:20'
published_at_gmt: '2021-04-15T16:06:20'
modified_at: '2026-04-27T22:26:36'
modified_at_gmt: '2026-04-27T22:26:36'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- DBA operation
- insight for DBAs
- MySQL
- mysql-and-variants
- Percona Server for MySQL
- Percona Software
tag_slugs:
- dba-operation
- insight-for-dbas
- mysql
- mysql-and-variants
- percona-server
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Online-DDL-with-Group-Replication-MySQL.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Online DDL with Group Replication In Percona Server for MySQL 8.0.22 (and MySQL 8.0.23)

Source: [Percona Blog](https://www.percona.com/blog/online-ddl-with-group-replication-in-percona-server-for-mysql-8-0-22/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2021-04-15T16:06:20

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

While I was working on my grFailOver POC, I have also done some additional parallel testing. One of them was to see how online DDL is executed inside a Group Replication cluster. The online DDL feature provides support for instant and in-place table alterations and concurrent DML. Checking the Group Replication (GR) official documentation, I … Continued

## Structure detectee

- H2: The Test
- H2: What Happens
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Online DDL with Group Replication In Percona Server for MySQL 8.0.22 (and MySQL 8.0.23)](https://www.percona.com/wp-content/uploads/2026/03/Online-DDL-with-Group-Replication-MySQL.png)
- content / image: [Online DDL with Group Replication MySQL](https://www.percona.com/wp-content/uploads/2026/03/Online-DDL-with-Group-Replication-MySQL-300x157.png)
- content / image: [group replication MySQL](https://www.percona.com/wp-content/uploads/2026/03/1-GR-DDL.png)
- content / image: [Group Replication MySQL ALTER](https://www.percona.com/wp-content/uploads/2026/03/2-GR-DDL.png)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
