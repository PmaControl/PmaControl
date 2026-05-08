---
title: Partially Rolling Back a Transaction in MySQL or PostgreSQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/partially-rolling-back-a-transaction-in-mysql-or-postgresql/
  post_id: 28349
source_author:
  name: Ninad Shah
  slug: ninad-shah
  url: https://www.percona.com/blog/author/ninad-shah/
  website: ''
published_at: '2024-04-12T13:36:18'
published_at_gmt: '2024-04-12T13:36:18'
modified_at: '2026-03-26T20:26:29'
modified_at_gmt: '2026-03-26T20:26:29'
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
- PostgreSQL
category_slugs:
- insight-for-dbas
- mysql
- postgresql
tags:
- MySQL
- PostgreSQL
tag_slugs:
- mysql
- postgresql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Partially-Rolling-Back-a-Transaction-in-MySQL-or-PostgreSQL.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Partially Rolling Back a Transaction in MySQL or PostgreSQL

Source: [Percona Blog](https://www.percona.com/blog/partially-rolling-back-a-transaction-in-mysql-or-postgresql/)

Auteur source: [Ninad Shah](https://www.percona.com/blog/author/ninad-shah/)

Publication: 2024-04-12T13:36:18

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This short write-up focuses on a different transaction control behavior of databases. Though this is not unusual, I decided to write an article on rolling back transactions to a particular point. I selected this topic because I found many people are not aware of this feature in databases. Description Every ACID-compliant RDBMS follows the “All … Continued

## Structure detectee

- H3: Description
- H3: The concept of savepoint
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Partially Rolling Back a Transaction in MySQL or PostgreSQL](https://www.percona.com/wp-content/uploads/2026/03/Partially-Rolling-Back-a-Transaction-in-MySQL-or-PostgreSQL.jpg)

## Auteur source

I hold 15 years of experience in the field of databases. In my career, I worked with various database technologies, such as Oracle, PostgreSQL, SQL server, MySQL, MongoDB. Out of which, I hold 8+ years of experience in PostgreSQL. At present, I work with Percona as PostgreSQL DBA I. For any queries, I am reachable at ninad.shah@percona.com
