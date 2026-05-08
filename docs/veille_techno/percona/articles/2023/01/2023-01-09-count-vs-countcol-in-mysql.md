---
title: COUNT(*) vs COUNT(col) in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/count-vs-countcol-in-mysql/
  post_id: 26477
source_author:
  name: Denis Subbota
  slug: denis-subbota
  url: https://www.percona.com/blog/author/denis-subbota/
  website: ''
published_at: '2023-01-09T13:43:10'
published_at_gmt: '2023-01-09T13:43:10'
modified_at: '2026-03-26T20:30:19'
modified_at_gmt: '2026-03-26T20:30:19'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Benchmarks
- Insight for DBAs
- MySQL
- Storage Engine
category_slugs:
- benchmarks
- insight-for-dbas
- mysql
- storage-engine
tags:
- MySQL
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/COUNT-vs-COUNTcol-in-MySQL.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# COUNT(*) vs COUNT(col) in MySQL

Source: [Percona Blog](https://www.percona.com/blog/count-vs-countcol-in-mysql/)

Auteur source: [Denis Subbota](https://www.percona.com/blog/author/denis-subbota/)

Publication: 2023-01-09T13:43:10

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Looking at how people are using COUNT(*) and COUNT(col), it looks like most of them think they are synonyms and just use what they happen to like, while there is a substantial difference in performance and even query results. Also, we find a difference in execution on InnoDB and MyISAM engines. NOTE: All tests were … Continued

## Structure detectee

- H2: Count function for Innodb engine:
- H2: Count function for MyISAM engine:

## Images et graphiques reperes

- featured / image: [COUNT(*) vs COUNT(col) in MySQL](https://www.percona.com/wp-content/uploads/2026/03/COUNT-vs-COUNTcol-in-MySQL.png)
- content / image: [COUNT(*) vs COUNT(col) in MySQL](https://www.percona.com/wp-content/uploads/2026/03/COUNT-vs-COUNTcol-in-MySQL-300x169.png)

## Auteur source

Denis Subbota is a MySQL DBA I at Percona Managed Services since March 2021, previously honed his skills as an avionics technician at Uzbekistan Airways. With a passion for IT technology, he excels in database management and optimization, bringing valuable expertise to his current role.
