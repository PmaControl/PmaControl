---
title: 'ProxySQL 2.3.0: Enhanced Support for MySQL Group Replication'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/proxysql-2-3-0-enhanced-support-for-mysql-group-replication/
  post_id: 24993
source_author:
  name: Sri Sakthivel
  slug: sri-sakthivel
  url: https://www.percona.com/blog/author/sri-sakthivel/
  website: ''
published_at: '2021-10-12T12:12:37'
published_at_gmt: '2021-10-12T12:12:37'
modified_at: '2026-04-28T15:02:06'
modified_at_gmt: '2026-04-28T15:02:06'
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
- MySQL
- ProxySQL
category_slugs:
- mysql
- proxysql
tags:
- MySQL
- mysql-and-variants
- ProxySQL
tag_slugs:
- mysql
- mysql-and-variants
- proxysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/ProxySQL-2.3-MySQL-Group-Replication.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# ProxySQL 2.3.0: Enhanced Support for MySQL Group Replication

Source: [Percona Blog](https://www.percona.com/blog/proxysql-2-3-0-enhanced-support-for-mysql-group-replication/)

Auteur source: [Sri Sakthivel](https://www.percona.com/blog/author/sri-sakthivel/)

Publication: 2021-10-12T12:12:37

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

ProxySQL 2.3.0 was recently released and when I was reading the release notes, I was really impressed with the Group Replication enhancements and features. I thought of experimenting with those things and was interested to write a blog about them. Here, I have focused on the following two topics: When the replication lag threshold … Continued

## Structure detectee

- H2: Test Environment
- H3: Scenario 1: When the replication lag threshold is reached, ProxySQL will move the server to SHUNNED state, instead of moving them to OFFLINE host group.
- H3: Scenario 2: The servers can be taken to maintenance through ProxySQL using “OFFLINE_SOFT”.

## Images et graphiques reperes

- featured / image: [ProxySQL 2.3.0: Enhanced Support for MySQL Group Replication](https://www.percona.com/wp-content/uploads/2026/03/ProxySQL-2.3-MySQL-Group-Replication.png)
- content / image: [ProxySQL 2.3 MySQL Group Replication](https://www.percona.com/wp-content/uploads/2026/03/ProxySQL-2.3-MySQL-Group-Replication-300x157.png)

## Auteur source

Oracle certified MySQL DBA. Working on MySQL and related technologies to ensures database performance. Handling multi client projects round the clock. Currently focusing on MySQL Cluster technologies like Galera and Group replication/InnoDB cluster. Active MySQL Blogger.
