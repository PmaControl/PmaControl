---
title: MySQL Group Replication – How to Elect the New Primary Node
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-group-replication-how-to-elect-the-new-primary-node/
  post_id: 23735
source_author:
  name: Sri Sakthivel
  slug: sri-sakthivel
  url: https://www.percona.com/blog/author/sri-sakthivel/
  website: ''
published_at: '2021-01-11T17:31:26'
published_at_gmt: '2021-01-11T17:31:26'
modified_at: '2026-04-27T22:20:28'
modified_at_gmt: '2026-04-27T22:20:28'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
- Open Source
- Percona Software
category_slugs:
- mysql
- open-source
- percona-software
tags:
- MySQL
- mysql-and-variants
- Open Source
- Percona Software
tag_slugs:
- mysql
- mysql-and-variants
- open-source
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Group-Replication-Primary-Node.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL Group Replication – How to Elect the New Primary Node

Source: [Percona Blog](https://www.percona.com/blog/mysql-group-replication-how-to-elect-the-new-primary-node/)

Auteur source: [Sri Sakthivel](https://www.percona.com/blog/author/sri-sakthivel/)

Publication: 2021-01-11T17:31:26

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog, I am going to explain the different ways of electing the PRIMARY node in MySQL group replication. Before MySQL 8.0.2, primary election was based on the member’s UUID, with the lowest UUID elected as the new primary in the event of a failover. From MySQL 8.0.2: We can select the node to … Continued

## Structure detectee

- H3: Scenario:
- H2: Using server weight (group_replication_member_weight):
- H2: Using function “group_replication_set_as_primary”:

## Images et graphiques reperes

- featured / image: [MySQL Group Replication – How to Elect the New Primary Node](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Group-Replication-Primary-Node.png)
- content / image: [MySQL Group Replication Primary Node](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Group-Replication-Primary-Node-300x168.png)

## Auteur source

Oracle certified MySQL DBA. Working on MySQL and related technologies to ensures database performance. Handling multi client projects round the clock. Currently focusing on MySQL Cluster technologies like Galera and Group replication/InnoDB cluster. Active MySQL Blogger.
