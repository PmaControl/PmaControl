---
title: Securing Dynamic Log File Locations in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/securing-dynamic-log-file-locations-in-mysql/
  post_id: 25668
source_author:
  name: Zsolt Parragi
  slug: zsolt-parragi
  url: https://www.percona.com/blog/author/zsolt-parragi/
  website: ''
published_at: '2022-05-25T12:55:49'
published_at_gmt: '2022-05-25T12:55:49'
modified_at: '2026-03-26T20:31:51'
modified_at_gmt: '2026-03-26T20:31:51'
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
- Security
category_slugs:
- insight-for-dbas
- mysql
- percona-software
- security
tags:
- MySQL
- mysql-and-variants
- security
tag_slugs:
- mysql
- mysql-and-variants
- security
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Securing-Dynamic-Log-File-Locations-in-MySQL.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Securing Dynamic Log File Locations in MySQL

Source: [Percona Blog](https://www.percona.com/blog/securing-dynamic-log-file-locations-in-mysql/)

Auteur source: [Zsolt Parragi](https://www.percona.com/blog/author/zsolt-parragi/)

Publication: 2022-05-25T12:55:49

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MySQL allows changing the location of the general log and the slow query log while the server is running by anybody having the SYSTEM_VARIABLES_ADMIN privilege to any location, including appending to existing files. In Percona Server for MySQL 8.0.28-19 we introduced a new system variable, secure-log-path, that can be used to restrict the location of … Continued

## Images et graphiques reperes

- featured / image: [Securing Dynamic Log File Locations in MySQL](https://www.percona.com/wp-content/uploads/2026/03/Securing-Dynamic-Log-File-Locations-in-MySQL.png)
- content / image: [Securing Dynamic Log File Locations in MySQL](https://www.percona.com/wp-content/uploads/2026/03/Securing-Dynamic-Log-File-Locations-in-MySQL-300x157.png)
