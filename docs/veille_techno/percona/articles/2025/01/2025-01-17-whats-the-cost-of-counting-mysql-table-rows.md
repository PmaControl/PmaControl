---
title: What’s the Cost of Counting MySQL Table Rows?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/whats-the-cost-of-counting-mysql-table-rows/
  post_id: 29243
source_author:
  name: Przemysław Malkowski
  slug: przemek-malkowski
  url: https://www.percona.com/blog/author/przemek-malkowski/
  website: ''
published_at: '2025-01-17T15:19:26'
published_at_gmt: '2025-01-17T15:19:26'
modified_at: '2026-03-26T20:25:47'
modified_at_gmt: '2026-03-26T20:25:47'
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
categories:
- Insight for DBAs
- Monitoring
- MySQL
category_slugs:
- insight-for-dbas
- monitoring
- mysql
tags:
- MySQL
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Whats-the-Cost-of-Counting-MySQL-Table-Rows.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# What’s the Cost of Counting MySQL Table Rows?

Source: [Percona Blog](https://www.percona.com/blog/whats-the-cost-of-counting-mysql-table-rows/)

Auteur source: [Przemysław Malkowski](https://www.percona.com/blog/author/przemek-malkowski/)

Publication: 2025-01-17T15:19:26

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

What index will be used when you count all rows in a table? Well, the MySQL documentation provides a straightforward answer to this, quoting: InnoDB processes SELECT COUNT(*) statements by traversing the smallest available secondary index unless an index or optimizer hint directs the optimizer to use a different index. If a secondary index is … Continued

## Structure detectee

- H3: Another unexpected challenge after upgrading?
- H3: Summary

## Images et graphiques reperes

- featured / image: [What’s the Cost of Counting MySQL Table Rows?](https://www.percona.com/wp-content/uploads/2026/03/Whats-the-Cost-of-Counting-MySQL-Table-Rows.jpg)
- content / image: [mysql-performance-tuning-1.png](https://www.percona.com/wp-content/uploads/2026/03/mysql-performance-tuning-1.png)

## Auteur source

Przemek joined Support Team at Percona in August 2012. Before that he spent over five years working for Wikia.com (Quantcast Top 50) as System Administrator where he was a key person responsible for seamless building up MySQL powered database infrastructure. Besides MySQL he worked on maintaining all other parts of LAMP stack, with main focus on automation, monitoring and backups.
