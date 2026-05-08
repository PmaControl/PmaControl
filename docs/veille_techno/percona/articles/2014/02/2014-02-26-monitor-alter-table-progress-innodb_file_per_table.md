---
title: How to monitor ALTER TABLE progress in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/monitor-alter-table-progress-innodb_file_per_table/
  post_id: 7801
source_author:
  name: Nilnandan Joshi
  slug: nilnandan-joshi-2
  url: https://www.percona.com/blog/author/nilnandan-joshi-2/
  website: ''
published_at: '2014-02-26T08:00:28'
published_at_gmt: '2014-02-26T08:00:28'
modified_at: '2026-04-28T22:01:24'
modified_at_gmt: '2026-04-28T22:01:24'
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
- tag:percona-toolkit:378
categories:
- MySQL
- Percona Services
- Percona Software
category_slugs:
- mysql
- percona-services
- percona-software
tags:
- alter table
- INSERT/UPDATE/DELETE
- Percona Toolkit
- pt-online-schema-change
tag_slugs:
- alter-table
- insert-update-delete
- percona-toolkit
- pt-online-schema-change
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.0.17-Clone-Plugin.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to monitor ALTER TABLE progress in MySQL

Source: [Percona Blog](https://www.percona.com/blog/monitor-alter-table-progress-innodb_file_per_table/)

Auteur source: [Nilnandan Joshi](https://www.percona.com/blog/author/nilnandan-joshi-2/)

Publication: 2014-02-26T08:00:28

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

While working on a recent support issue as a Percona Support Engineer, I got one question from a customer asking how to monitor ALTER TABLE progress. Actually, for MySQL 5.5 and prior versions, it’s quite difficult to ALTER the table in a running production environment especially for large tables (with millions of records). Because it … Continued

## Images et graphiques reperes

- featured / image: [How to monitor ALTER TABLE progress in MySQL](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.0.17-Clone-Plugin.jpg)

## Auteur source

Nilnandan officially started with Percona as a Support Engineer. Before joining Percona, he has worked as a MySQL Database administrator with different types of service based companies managing high-traffic websites and web applications. Nilnandan has extensive experience in database design and development, database management, client management, security/documentations/training, implementing DRM solutions, automating backups and high availability. Nilnandan is based at Pune (India). In his spare time, he likes to listen Indian classical/semi-classical music, watching tv, playing cricket/badminton and hang out with his family.
