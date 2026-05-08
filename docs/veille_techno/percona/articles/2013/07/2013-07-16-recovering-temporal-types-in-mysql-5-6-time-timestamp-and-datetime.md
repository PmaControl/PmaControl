---
title: 'Recovering temporal types in MySQL 5.6: TIME, TIMESTAMP and DATETIME'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/recovering-temporal-types-in-mysql-5-6-time-timestamp-and-datetime/
  post_id: 7171
source_author:
  name: Aleksandr Kuzminsky
  slug: akuzminsky
  url: https://www.percona.com/blog/author/akuzminsky/
  website: ''
published_at: '2013-07-16T13:43:52'
published_at_gmt: '2013-07-16T13:43:52'
modified_at: '2026-05-04T22:06:52'
modified_at_gmt: '2026-05-04T22:06:52'
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
category_slugs:
- insight-for-dbas
- mysql
tags:
- Aleksandr Kuzminsky
- datetime
- innodb recovery
- microseconds resolution
- MySQL 5.6
- time
- timestamp
tag_slugs:
- aleksandr-kuzminsky
- datetime
- innodb-recovery
- microseconds-resolution
- mysql-5-6
- time
- timestamp
featured_image_url: http://www.veryicon.com/icon/png/System/NX10/Time.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Recovering temporal types in MySQL 5.6: TIME, TIMESTAMP and DATETIME

Source: [Percona Blog](https://www.percona.com/blog/recovering-temporal-types-in-mysql-5-6-time-timestamp-and-datetime/)

Auteur source: [Aleksandr Kuzminsky](https://www.percona.com/blog/author/akuzminsky/)

Publication: 2013-07-16T13:43:52

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MySQL 5.6 introduces a new feature – microseconds resolution in some temporal types. As of 5.6.4 TIME, TIMESTAMP and DATETIME can have a fractional part. To create a field with subseconds you can specify precision in brackets: TIME(3), DATETIME(6) etc. Obviously, the new feature requires the format change. All three types may now have a … Continued

## Images et graphiques reperes

- featured / image: [Recovering temporal types in MySQL 5.6: TIME, TIMESTAMP and DATETIME](http://www.veryicon.com/icon/png/System/NX10/Time.png)

## Auteur source

Aleksandr is a consultant and data recovery specialist. He is a former Percona employee.
