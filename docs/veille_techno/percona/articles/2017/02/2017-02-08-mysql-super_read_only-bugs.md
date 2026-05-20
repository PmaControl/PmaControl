---
title: MySQL super_read_only Bugs
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-super_read_only-bugs/
  post_id: 16294
source_author:
  name: Juan Arruti
  slug: juan-arruti
  url: https://www.percona.com/blog/author/juan-arruti/
  website: ''
published_at: '2017-02-08T15:23:31'
published_at_gmt: '2017-02-08T15:23:31'
modified_at: '2026-05-05T19:43:52'
modified_at_gmt: '2026-05-05T19:43:52'
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
category_slugs:
- insight-for-dbas
- mysql
tags:
- Bugs
- MySQL Replication
- super_read_only
tag_slugs:
- bugs
- mysql-replication
- super_read_only
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MongoDB-Troubleshooting-e1480963086227.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL super_read_only Bugs

Source: [Percona Blog](https://www.percona.com/blog/mysql-super_read_only-bugs/)

Auteur source: [Juan Arruti](https://www.percona.com/blog/author/juan-arruti/)

Publication: 2017-02-08T15:23:31

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This blog we describe an issue with MySQL 5.7’s super_read_only feature when used alongside with GTID in chained slave instances. Background In MySQL 5.7.5 and onward introduced the gtid_executed table in the MySQL database to store every GTID. This allows slave instances to use the GTID feature regardless whether the binlog option is set or … Continued

## Structure detectee

- H2: Background
- H2: The Issue [1]
- H2: The Issue [2]
- H2: The Workaround
- H2: Summary

## Images et graphiques reperes

- featured / image: [MySQL super_read_only Bugs](https://www.percona.com/wp-content/uploads/2026/03/MongoDB-Troubleshooting-e1480963086227.jpg)

## Auteur source

Juan Pablo joined Percona in 2016 as a member of Technical Services Team. Before coming to Percona, he worked as DBA in several companies such as IBM, Turner and Oracle.
