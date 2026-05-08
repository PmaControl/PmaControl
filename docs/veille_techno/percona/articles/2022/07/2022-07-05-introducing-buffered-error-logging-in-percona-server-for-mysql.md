---
title: Introducing Buffered Error Logging in Percona Server for MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/introducing-buffered-error-logging-in-percona-server-for-mysql/
  post_id: 25744
source_author:
  name: Zsolt Parragi
  slug: zsolt-parragi
  url: https://www.percona.com/blog/author/zsolt-parragi/
  website: ''
published_at: '2022-07-05T13:07:53'
published_at_gmt: '2022-07-05T13:07:53'
modified_at: '2026-03-26T20:31:43'
modified_at_gmt: '2026-03-26T20:31:43'
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
- Percona Server for MySQL
tag_slugs:
- mysql
- mysql-and-variants
- percona-server
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Introducing-Buffered-Error-Logging-MySQL.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Introducing Buffered Error Logging in Percona Server for MySQL

Source: [Percona Blog](https://www.percona.com/blog/introducing-buffered-error-logging-in-percona-server-for-mysql/)

Auteur source: [Zsolt Parragi](https://www.percona.com/blog/author/zsolt-parragi/)

Publication: 2022-07-05T13:07:53

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The MySQL error log is usually used to store and later analyze error and warning messages, but in some cases, it is also used for high throughput debug messages for analyzing complex issues. These messages take up more space, slow down the server, and also make the error log harder to use for other issues. … Continued

## Structure detectee

- H2: Motivation
- H2: Our implementation
- H2: Configuring buffered logging

## Images et graphiques reperes

- featured / image: [Introducing Buffered Error Logging in Percona Server for MySQL](https://www.percona.com/wp-content/uploads/2026/03/Introducing-Buffered-Error-Logging-MySQL.png)
- content / image: [Introducing Buffered Error Logging MySQL](https://www.percona.com/wp-content/uploads/2026/03/Introducing-Buffered-Error-Logging-MySQL-300x157.png)
