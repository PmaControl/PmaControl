---
title: Why SELECT COUNT(*) FROM TABLE Is Sometimes Very Slow in MySQL or MariaDB
source:
  name: Percona Blog
  url: https://www.percona.com/blog/why-select-count-from-table-is-sometimes-very-slow-in-mysql-or-mariadb/
  post_id: 28296
source_author:
  name: Przemysław Malkowski
  slug: przemek-malkowski
  url: https://www.percona.com/blog/author/przemek-malkowski/
  website: ''
published_at: '2024-04-15T13:02:26'
published_at_gmt: '2024-04-15T13:02:26'
modified_at: '2026-03-26T20:26:28'
modified_at_gmt: '2026-03-26T20:26:28'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MariaDB
- MySQL
matched_filters:
- category:mariadb:1281
- category:mysql:83
categories:
- Insight for DBAs
- MariaDB
- MySQL
category_slugs:
- insight-for-dbas
- mariadb
- mysql
tags:
- MariaDB
- MySQL
- mysql-and-variants
- table count
tag_slugs:
- mariadb
- mysql
- mysql-and-variants
- table-count
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/slow-SELECT-COUNT-FROM-TABLE.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Why SELECT COUNT(*) FROM TABLE Is Sometimes Very Slow in MySQL or MariaDB

Source: [Percona Blog](https://www.percona.com/blog/why-select-count-from-table-is-sometimes-very-slow-in-mysql-or-mariadb/)

Auteur source: [Przemysław Malkowski](https://www.percona.com/blog/author/przemek-malkowski/)

Publication: 2024-04-15T13:02:26

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

If you have enough experience with MySQL, it is very possible that you stumbled upon an unusually slow SELECT COUNT(*) FROM TABLE; query execution, at least occasionally. Recently, I had a chance to investigate some of these cases closer, and it stunned me what huge differences there can be depending on the circumstance given the … Continued

## Structure detectee

- H2: Well-known/obvious reasons
- H2: Less obvious reasons
- H2: MySQL ecosystem dynamics
- H2: MVCC challenge
- H2: Parallel InnoDB read threads in MySQL 8.0
- H2: New table populated with INSERTs vs. Optimized one
- H2: Other factors/bugs
- H3: Summary

## Images et graphiques reperes

- featured / image: [Why SELECT COUNT(*) FROM TABLE Is Sometimes Very Slow in MySQL or MariaDB](https://www.percona.com/wp-content/uploads/2026/03/slow-SELECT-COUNT-FROM-TABLE.jpg)

## Auteur source

Przemek joined Support Team at Percona in August 2012. Before that he spent over five years working for Wikia.com (Quantcast Top 50) as System Administrator where he was a key person responsible for seamless building up MySQL powered database infrastructure. Besides MySQL he worked on maintaining all other parts of LAMP stack, with main focus on automation, monitoring and backups.
