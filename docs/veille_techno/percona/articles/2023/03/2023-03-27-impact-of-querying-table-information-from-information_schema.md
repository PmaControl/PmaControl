---
title: Impact of Querying Table Information From information_schema
source:
  name: Percona Blog
  url: https://www.percona.com/blog/impact-of-querying-table-information-from-information_schema/
  post_id: 20382
source_author:
  name: Carlos Tutte
  slug: carlos-tutte
  url: https://www.percona.com/blog/author/carlos-tutte/
  website: ''
published_at: '2023-03-27T12:52:03'
published_at_gmt: '2023-03-27T12:52:03'
modified_at: '2026-03-26T20:29:57'
modified_at_gmt: '2026-03-26T20:29:57'
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
- MySQL
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/PMM_noFK.jpg
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Impact of Querying Table Information From information_schema

Source: [Percona Blog](https://www.percona.com/blog/impact-of-querying-table-information-from-information_schema/)

Auteur source: [Carlos Tutte](https://www.percona.com/blog/author/carlos-tutte/)

Publication: 2023-03-27T12:52:03

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

On MySQL and Percona Server for MySQL, there is a schema called information_schema (I_S) which provides information about database tables, views, indexes, and more. A lot of useful information can be retrieved from this schema, for example, table metadata and foreign key relations, but trying to query I_S can induce performance degradation if your server … Continued

## Structure detectee

- H2: Test
- H3: Setup
- H3: Hardware
- H3: Main Percona Server for MySQL configuration variables tuned in my.cnf:
- H3: Executed queries
- H2: Results for Percona Server for MySQL 5.7
- H2: Results for Percona Server for MySQL 8.0
- H2: Conclusion

## Images et graphiques reperes

- content / image: [MySQL table open cache status](https://www.percona.com/wp-content/uploads/2026/03/PMM_noFK.jpg)
- content / image: [PMM_withFK.jpg](https://www.percona.com/wp-content/uploads/2026/03/PMM_withFK.jpg)
- content / image: [MySQL open cache](https://www.percona.com/wp-content/uploads/2026/03/PMM_withFK2.jpg)
- content / image: [PMM_8withFK.jpg](https://www.percona.com/wp-content/uploads/2026/03/PMM_8withFK.jpg)

## Auteur source

Computer engineer from Montevideo, Uruguay, joined Percona on February 2018, first as a support engineer, then moving to the consulting team. Working in complex IT solutions for more than 10 years, Carlos now specializes in MySQL and related technologies
