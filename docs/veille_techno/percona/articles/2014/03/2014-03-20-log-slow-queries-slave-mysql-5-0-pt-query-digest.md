---
title: How to log slow queries on Slave in MySQL 5.0 with pt-query-digest
source:
  name: Percona Blog
  url: https://www.percona.com/blog/log-slow-queries-slave-mysql-5-0-pt-query-digest/
  post_id: 7928
source_author:
  name: Nilnandan Joshi
  slug: nilnandan-joshi-2
  url: https://www.percona.com/blog/author/nilnandan-joshi-2/
  website: ''
published_at: '2014-03-20T07:00:41'
published_at_gmt: '2014-03-20T07:00:41'
modified_at: '2026-04-28T22:02:58'
modified_at_gmt: '2026-04-28T22:02:58'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
categories:
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- MySQL Replication
- Nilnandan Joshi
- pt-query-digest
- slave lagging
- slow queries
- Slow Query Log
tag_slugs:
- mysql-replication
- nilnandan-joshi
- pt-query-digest
- slave-lagging
- slow-queries
- slow-query-log
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to log slow queries on Slave in MySQL 5.0 with pt-query-digest

Source: [Percona Blog](https://www.percona.com/blog/log-slow-queries-slave-mysql-5-0-pt-query-digest/)

Auteur source: [Nilnandan Joshi](https://www.percona.com/blog/author/nilnandan-joshi-2/)

Publication: 2014-03-20T07:00:41

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Working as a Percona Support Engineer, every day we are seeing lots of issues related to MySQL replication. One very common issue is slave lagging. There are many reasons for slave lag but one common reason is that queries are taking more time on slave then master. How to check and log those long-running queries? … Continued

## Auteur source

Nilnandan officially started with Percona as a Support Engineer. Before joining Percona, he has worked as a MySQL Database administrator with different types of service based companies managing high-traffic websites and web applications. Nilnandan has extensive experience in database design and development, database management, client management, security/documentations/training, implementing DRM solutions, automating backups and high availability. Nilnandan is based at Pune (India). In his spare time, he likes to listen Indian classical/semi-classical music, watching tv, playing cricket/badminton and hang out with his family.
