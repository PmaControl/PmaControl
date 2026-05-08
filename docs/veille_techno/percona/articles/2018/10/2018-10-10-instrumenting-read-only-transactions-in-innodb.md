---
title: Instrumenting Read Only Transactions in InnoDB
source:
  name: Percona Blog
  url: https://www.percona.com/blog/instrumenting-read-only-transactions-in-innodb/
  post_id: 19411
source_author:
  name: Przemysław Malkowski
  slug: przemek-malkowski
  url: https://www.percona.com/blog/author/przemek-malkowski/
  website: ''
published_at: '2018-10-10T15:09:57'
published_at_gmt: '2018-10-10T15:09:57'
modified_at: '2026-05-05T19:23:17'
modified_at_gmt: '2026-05-05T19:23:17'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:mysql:83
- search:pmm
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- read only
tag_slugs:
- read-only
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Instrumenting-read-only-transactions.jpg
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Instrumenting Read Only Transactions in InnoDB

Source: [Percona Blog](https://www.percona.com/blog/instrumenting-read-only-transactions-in-innodb/)

Auteur source: [Przemysław Malkowski](https://www.percona.com/blog/author/przemek-malkowski/)

Publication: 2018-10-10T15:09:57

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Probably not well known but quite an important optimization was introduced in MySQL 5.6 – reduced overhead for “read only transactions”. While usually by a “transaction” we mean a query or a group of queries that change data, with transaction engines like InnoDB, every data read or write operation is a transaction. Now, as a … Continued

## Structure detectee

- H3: Information Schema Instrumentation
- H3: Performance Schema Problem
- H3: PMM Dashboard

## Images et graphiques reperes

- featured / image: [Instrumenting Read Only Transactions in InnoDB](https://www.percona.com/wp-content/uploads/2026/03/Instrumenting-read-only-transactions.jpg)
- content / image: [Instrumenting read only transactions MySQL](https://www.percona.com/wp-content/uploads/2026/03/Instrumenting-read-only-transactions-300x200.jpg)
- content / image: [Selection_297.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_297.png)

## Auteur source

Przemek joined Support Team at Percona in August 2012. Before that he spent over five years working for Wikia.com (Quantcast Top 50) as System Administrator where he was a key person responsible for seamless building up MySQL powered database infrastructure. Besides MySQL he worked on maintaining all other parts of LAMP stack, with main focus on automation, monitoring and backups.
