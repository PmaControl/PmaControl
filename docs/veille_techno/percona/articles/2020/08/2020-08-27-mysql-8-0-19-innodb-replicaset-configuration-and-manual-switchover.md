---
title: MySQL 8.0.19 InnoDB ReplicaSet Configuration and Manual Switchover
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-8-0-19-innodb-replicaset-configuration-and-manual-switchover/
  post_id: 22999
source_author:
  name: Sri Sakthivel
  slug: sri-sakthivel
  url: https://www.percona.com/blog/author/sri-sakthivel/
  website: ''
published_at: '2020-08-27T14:00:09'
published_at_gmt: '2020-08-27T14:00:09'
modified_at: '2026-04-27T22:13:59'
modified_at_gmt: '2026-04-27T22:13:59'
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
- InnoDB
- MySQL
- mysql-and-variants
- replicaset
tag_slugs:
- innodb
- mysql
- mysql-and-variants
- replicaset
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/manualswitchover-1024x536.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL 8.0.19 InnoDB ReplicaSet Configuration and Manual Switchover

Source: [Percona Blog](https://www.percona.com/blog/mysql-8-0-19-innodb-replicaset-configuration-and-manual-switchover/)

Auteur source: [Sri Sakthivel](https://www.percona.com/blog/author/sri-sakthivel/)

Publication: 2020-08-27T14:00:09

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

InnoDB ReplicaSet was introduced from MySQL 8.0.19. It works based on the MySQL asynchronous replication. Generally, InnoDB ReplicaSet does not provide high availability on its own like InnoDB Cluster, because with InnoDB ReplicaSet we need to perform the manual failover. AdminAPI includes the support for the InnoDB ReplicaSet. We can operate the InnoDB ReplicaSet using … Continued

## Structure detectee

- H2: Why InnoDB ReplicaSet?
- H2: How to set up the InnoDB ReplicaSet in a fresh environment?
- H2: How to perform the manual switchover with ReplicaSet?

## Images et graphiques reperes

- featured / image: [MySQL 8.0.19 InnoDB ReplicaSet Configuration and Manual Switchover](https://www.percona.com/wp-content/uploads/2026/03/manualswitchover-1024x536.png)
- content / image: [Manual Switchover](https://www.percona.com/wp-content/uploads/2026/03/manualswitchover-300x157.png)

## Auteur source

Oracle certified MySQL DBA. Working on MySQL and related technologies to ensures database performance. Handling multi client projects round the clock. Currently focusing on MySQL Cluster technologies like Galera and Group replication/InnoDB cluster. Active MySQL Blogger.
