---
title: 'MySQL 8.0.22: Asynchronous Replication Automatic Connection (IO Thread) Failover'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-8-0-22-asynchronous-replication-automatic-connection-io-thread-failover/
  post_id: 23386
source_author:
  name: Sri Sakthivel
  slug: sri-sakthivel
  url: https://www.percona.com/blog/author/sri-sakthivel/
  website: ''
published_at: '2020-10-26T19:46:18'
published_at_gmt: '2020-10-26T19:46:18'
modified_at: '2026-05-05T16:34:07'
modified_at_gmt: '2026-05-05T16:34:07'
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
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- insight for DBAs
- insight for developers
- MySQL
- mysql-and-variants
tag_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.0.22-Asynchronous-Replication-Automatic-Connection.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL 8.0.22: Asynchronous Replication Automatic Connection (IO Thread) Failover

Source: [Percona Blog](https://www.percona.com/blog/mysql-8-0-22-asynchronous-replication-automatic-connection-io-thread-failover/)

Auteur source: [Sri Sakthivel](https://www.percona.com/blog/author/sri-sakthivel/)

Publication: 2020-10-26T19:46:18

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MySQL 8.0.22 was released on Oct 19, 2020, and came with nice features and a lot of bug fixes. Now, you can configure your async replica to choose the new source in case the existing source connection (IO thread) fails. In this blog, I am going to explain the entire process involved in this configuration … Continued

## Structure detectee

- H2: Overview
- H2: Requirements
- H2: Use Case
- H2: Configuration for Automatic Connection Failover
- H2: Is Failback Possible?
- H3: What happens if the primary node comes back online?
- H3: Does it perform a failback in case the server with higher weight comes back online?

## Images et graphiques reperes

- featured / image: [MySQL 8.0.22: Asynchronous Replication Automatic Connection (IO Thread) Failover](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.0.22-Asynchronous-Replication-Automatic-Connection.png)
- content / image: [MySQL 8.0.22 Asynchronous Replication Automatic Connection](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.0.22-Asynchronous-Replication-Automatic-Connection-300x157.png)

## Auteur source

Oracle certified MySQL DBA. Working on MySQL and related technologies to ensures database performance. Handling multi client projects round the clock. Currently focusing on MySQL Cluster technologies like Galera and Group replication/InnoDB cluster. Active MySQL Blogger.
