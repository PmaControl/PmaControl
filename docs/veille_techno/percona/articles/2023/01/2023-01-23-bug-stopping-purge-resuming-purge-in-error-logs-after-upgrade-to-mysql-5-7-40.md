---
title: '[BUG] Stopping Purge/Resuming Purge in Error Logs After Upgrade to MySQL 5.7.40'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/bug-stopping-purge-resuming-purge-in-error-logs-after-upgrade-to-mysql-5-7-40/
  post_id: 26541
source_author:
  name: Yunus Shaikh
  slug: yunus-shaikh
  url: https://www.percona.com/blog/author/yunus-shaikh/
  website: ''
published_at: '2023-01-23T12:55:28'
published_at_gmt: '2023-01-23T12:55:28'
modified_at: '2026-03-26T20:30:16'
modified_at_gmt: '2026-03-26T20:30:16'
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
- MySQL
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Resuming-Purge-in-Error-Logs-After-Upgrade-to-MySQL-5.7.40.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# [BUG] Stopping Purge/Resuming Purge in Error Logs After Upgrade to MySQL 5.7.40

Source: [Percona Blog](https://www.percona.com/blog/bug-stopping-purge-resuming-purge-in-error-logs-after-upgrade-to-mysql-5-7-40/)

Auteur source: [Yunus Shaikh](https://www.percona.com/blog/author/yunus-shaikh/)

Publication: 2023-01-23T12:55:28

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

We had a couple of cases where clients reported that the MySQL error log was flooded with the below note: Shell 2023-01-18T13:07:56.946323Z 2 [Note] InnoDB: Stopping purge<br>2023-01-18T13:07:56.948621Z 2 [Note] InnoDB: Resuming purge<br>2023-01-18T13:08:27.229703Z 2 [Note] InnoDB: Stopping purge<br>2023-01-18T13:08:27.231552Z 2 [Note] InnoDB: Resuming purge<br>2023-01-18T13:08:28.581674Z 2 [Note] InnoDB: Stopping purge 1 2023 - 01 - 18T13 : 07 : 56.946323Z 2 [ Note ] InnoDB : Stopping purge < br > 2023 - 01 - 18T13 : 07 : 56.948621Z 2 [ Note ] InnoDB : Resuming purge < br > 2023 - 01 - 18T13 : 08 : 27.229703Z 2 [ Note ] InnoDB : Stopping purge < br > 2023 - 01 - 18T13 : 08 : 27.231552Z 2 [ Note ] InnoDB : Resuming purge < br > 2023 - 01 - 18T13 : 08 : 28.581674Z 2 [ Note ] InnoDB : Stopping purge One of my colleagues Sami Ahlroos found that whenever we trigger a truncate on any table...

## Images et graphiques reperes

- featured / image: [[BUG] Stopping Purge/Resuming Purge in Error Logs After Upgrade to MySQL 5.7.40](https://www.percona.com/wp-content/uploads/2026/03/Resuming-Purge-in-Error-Logs-After-Upgrade-to-MySQL-5.7.40.jpg)

## Auteur source

Yunus have joined Percona since 2018 as a Database Administrator. He also has spent working as System administrator earlier in his career having good experience in open source tools.
