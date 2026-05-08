---
title: Using Slow Query Log to Find High Load Spots in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/identifying-high-load-mysql-slow-query-log-pt-query-digest/
  post_id: 19472
source_author:
  name: Uday Varagani
  slug: uday-varagani
  url: https://www.percona.com/blog/author/uday-varagani/
  website: ''
published_at: '2023-03-03T14:24:39'
published_at_gmt: '2023-03-03T14:24:39'
modified_at: '2026-03-26T20:30:06'
modified_at_gmt: '2026-03-26T20:30:06'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:percona-toolkit
- search:pmm
categories:
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- High Load
- MySQL
- mysql-and-variants
- pt-query-digest
- pt-toolkit
- query auditing
- Slow Query Log
tag_slugs:
- high-load
- mysql
- mysql-and-variants
- pt-query-digest
- pt-toolkit
- query-auditing
- slow-query-log
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_a_computer_server_texture_triangles_Azure_Radiance_57e22ffe-3e3b-45d5-a75d-123cc4d69e15.png
image_count: 2
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using Slow Query Log to Find High Load Spots in MySQL

Source: [Percona Blog](https://www.percona.com/blog/identifying-high-load-mysql-slow-query-log-pt-query-digest/)

Auteur source: [Uday Varagani](https://www.percona.com/blog/author/uday-varagani/)

Publication: 2023-03-03T14:24:39

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This post was originally published in October 2018 and was updated in March 2023. pt-query-digest is one of the most commonly used tools for query auditing in MySQL. By default, pt-query-digest reports the top ten queries consuming the most amount of time inside MySQL. A query that takes more time than the set threshold for … Continued

## Structure detectee

- H2: Overview of the MySQL Slow Query Log
- H2: How to enable the slow query log in MySQL
- H3: Slow query log parameters
- H3: Slow query log contents
- H2: How to read the MySQL slow query log
- H2: Some sample data
- H2: Analyzing MySQL Slow Query Log from pt-query-digest
- H2: Using PMM to monitor queries
- H2: FAQs
- H3: What is the default location of MySQL Slow Query Log?
- H3: How can I exclude certain queries from the slow query log?
- H3: Can MySQL Slow Query Log affect database performance?
- H3: Can I use third-party tools to analyze MySQL Slow Query Log?
- H3: How do I optimize MySQL performance for specific queries?

## Images et graphiques reperes

- featured / image: [Using Slow Query Log to Find High Load Spots in MySQL](https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_a_computer_server_texture_triangles_Azure_Radiance_57e22ffe-3e3b-45d5-a75d-123cc4d69e15.png)
- content / graph_or_chart: [Slow query log from PMM dashboard](https://www.percona.com/wp-content/uploads/2026/03/pmm-queries-slowest-query-1024x345.png)
