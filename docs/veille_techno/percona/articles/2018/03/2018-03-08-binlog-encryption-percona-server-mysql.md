---
title: Binlog Encryption with Percona Server for MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/binlog-encryption-percona-server-mysql/
  post_id: 18034
source_author:
  name: Robert Golebiowski
  slug: robert-golebiowski
  url: https://www.percona.com/blog/author/robert-golebiowski/
  website: ''
published_at: '2018-03-08T22:11:42'
published_at_gmt: '2018-03-08T22:11:42'
modified_at: '2026-05-05T19:31:18'
modified_at_gmt: '2026-05-05T19:31:18'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:monitoring:2104
- category:mysql:83
- search:percona-monitoring-and-management
categories:
- Insight for DBAs
- Monitoring
- MySQL
- Percona Software
- Security
category_slugs:
- insight-for-dbas
- monitoring
- mysql
- percona-software
- security
tags:
- binlog
- binlog encryption
- database
- MySQL
- Percona Server for MySQL
- Replication
- security
tag_slugs:
- binlog
- binlog-encryption
- database
- mysql
- percona-server
- replication
- security
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/binlog-encryption-e1520540151167.jpg
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Binlog Encryption with Percona Server for MySQL

Source: [Percona Blog](https://www.percona.com/blog/binlog-encryption-percona-server-mysql/)

Auteur source: [Robert Golebiowski](https://www.percona.com/blog/author/robert-golebiowski/)

Publication: 2018-03-08T22:11:42

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll look at how to turn on binlog encryption in Percona Server for MySQL. Why do I need this? As you probably know, Percona Server for MySQL’s binlog contains sensitive information. Replication uses the binlog to copy events between servers. They contain all the information from one server so that it … Continued

## Structure detectee

- H3: Why do I need this?
- H3: How do you turn it on?
- H3: How does this work in the big picture?
- H3: Digging deeper with mysqlbinlog

## Images et graphiques reperes

- featured / image: [Binlog Encryption with Percona Server for MySQL](https://www.percona.com/wp-content/uploads/2026/03/binlog-encryption-e1520540151167.jpg)
- content / image: [binlog_local_without_force.jpg](https://www.percona.com/wp-content/uploads/2026/03/binlog_local_without_force.jpg)
- content / image: [binlog_local_with_force.jpg](https://www.percona.com/wp-content/uploads/2026/03/binlog_local_with_force.jpg)
- content / image: [binlog_remote.jpg](https://www.percona.com/wp-content/uploads/2026/03/binlog_remote.jpg)

## Auteur source

Passionate software developer. Working in MySQL ecosystem since 2014 - now with Percona, previously with Oracle. Working mainly on security features.
