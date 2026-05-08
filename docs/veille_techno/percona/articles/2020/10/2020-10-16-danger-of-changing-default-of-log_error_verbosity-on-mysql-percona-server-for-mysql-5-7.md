---
title: Danger of Changing Default of log_error_verbosity on MySQL/Percona Server for MySQL 5.7
source:
  name: Percona Blog
  url: https://www.percona.com/blog/danger-of-changing-default-of-log_error_verbosity-on-mysql-percona-server-for-mysql-5-7/
  post_id: 23349
source_author:
  name: Carlos Tutte
  slug: carlos-tutte
  url: https://www.percona.com/blog/author/carlos-tutte/
  website: ''
published_at: '2020-10-16T18:29:10'
published_at_gmt: '2020-10-16T18:29:10'
modified_at: '2026-04-27T22:16:01'
modified_at_gmt: '2026-04-27T22:16:01'
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
- insight for DBAs
- MySQL
- mysql-and-variants
- Percona Server for MySQL
- Percona Software
tag_slugs:
- insight-for-dbas
- mysql
- mysql-and-variants
- percona-server
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Changing-Default-of-log_error_verbosity.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Danger of Changing Default of log_error_verbosity on MySQL/Percona Server for MySQL 5.7

Source: [Percona Blog](https://www.percona.com/blog/danger-of-changing-default-of-log_error_verbosity-on-mysql-percona-server-for-mysql-5-7/)

Auteur source: [Carlos Tutte](https://www.percona.com/blog/author/carlos-tutte/)

Publication: 2020-10-16T18:29:10

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Changing the default value (3) of log_error_verbosity in MySQL/Percona Server for MySQL 5.7 can have a hidden unintended effect! What does log_error_verbosity do exactly? As per the documentation: “The log_error_verbosity system variable specifies the verbosity for handling events intended for the error log.” Basically a value of 1 logs only [Errors]; 2 is 1)+[Warnings]; and … Continued

## Images et graphiques reperes

- featured / image: [Danger of Changing Default of log_error_verbosity on MySQL/Percona Server for MySQL 5.7](https://www.percona.com/wp-content/uploads/2026/03/Changing-Default-of-log_error_verbosity.png)
- content / image: [Changing Default of log_error_verbosity mysql](https://www.percona.com/wp-content/uploads/2026/03/Changing-Default-of-log_error_verbosity-300x168.png)

## Auteur source

Computer engineer from Montevideo, Uruguay, joined Percona on February 2018, first as a support engineer, then moving to the consulting team. Working in complex IT solutions for more than 10 years, Carlos now specializes in MySQL and related technologies
