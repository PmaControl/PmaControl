---
title: Beware of Unstable Query Digests in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/beware-of-unstable-query-digests-in-mysql/
  post_id: 21508
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2020-01-20T18:58:13'
published_at_gmt: '2020-01-20T18:58:13'
modified_at: '2026-05-05T16:23:59'
modified_at_gmt: '2026-05-05T16:23:59'
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
- search:pmm
- tag:pmm:2167
categories:
- Monitoring
- MySQL
- Percona Software
category_slugs:
- monitoring
- mysql
- percona-software
tags:
- Monitoring
- MySQL
- Percona Software
- PMM
tag_slugs:
- monitoring
- mysql
- percona-software
- pmm
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/unstable-query-digests-mysql.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Beware of Unstable Query Digests in MySQL

Source: [Percona Blog](https://www.percona.com/blog/beware-of-unstable-query-digests-in-mysql/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2020-01-20T18:58:13

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

If you’re using MySQL’s Performance Schema you may use “query digests” as IDs to identify specific query patterns in the events_statements_summary_by_digest Performance Schema Table. You might assume these hashes are stable between different versions, so, for example, when upgrading from MySQL 5.7 to MySQL 8, you can compare the query response time and other execution … Continued

## Structure detectee

- H2: MySQL 5.6
- H2: MySQL 5.7
- H2: MySQL 8.0

## Images et graphiques reperes

- featured / image: [Beware of Unstable Query Digests in MySQL](https://www.percona.com/wp-content/uploads/2026/03/unstable-query-digests-mysql.png)
- content / image: [Unstable Query Digests in MySQL](https://www.percona.com/wp-content/uploads/2026/03/unstable-query-digests-mysql-300x168.png)
- content / image: [Unstable Query Digests in MySQL](https://www.percona.com/wp-content/uploads/2026/03/Unstable-Query-Digests-in-MySQL-1024x669.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
