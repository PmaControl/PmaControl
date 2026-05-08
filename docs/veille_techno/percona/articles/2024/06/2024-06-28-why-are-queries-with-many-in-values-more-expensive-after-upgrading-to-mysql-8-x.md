---
title: Why Are Queries with Many IN Values More Expensive After Upgrading to MySQL 8.x?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/why-are-queries-with-many-in-values-more-expensive-after-upgrading-to-mysql-8-x/
  post_id: 28750
source_author:
  name: Przemysław Malkowski
  slug: przemek-malkowski
  url: https://www.percona.com/blog/author/przemek-malkowski/
  website: ''
published_at: '2024-06-28T18:08:30'
published_at_gmt: '2024-06-28T18:08:30'
modified_at: '2026-03-26T20:26:13'
modified_at_gmt: '2026-03-26T20:26:13'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- MySQL
- mysql-and-variants
- range optimization
- Upgrade
tag_slugs:
- mysql
- mysql-and-variants
- range-optimization
- upgrade
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/IN-Values-More-Expensive-After-Upgrading-to-MySQL-8.jpeg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Why Are Queries with Many IN Values More Expensive After Upgrading to MySQL 8.x?

Source: [Percona Blog](https://www.percona.com/blog/why-are-queries-with-many-in-values-more-expensive-after-upgrading-to-mysql-8-x/)

Auteur source: [Przemysław Malkowski](https://www.percona.com/blog/author/przemek-malkowski/)

Publication: 2024-06-28T18:08:30

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Some of our Percona Support customers report performance degradation after a major MySQL upgrade, and there can be many different reasons for this. These days, the most common major upgrade is from MySQL 5.7 (which recently reached EOL) to 8.0, and I am going to emphasize one important case that affects many database instances. Range … Continued

## Structure detectee

- H2: Range optimization problem
- H3: Summary

## Images et graphiques reperes

- featured / image: [Why Are Queries with Many IN Values More Expensive After Upgrading to MySQL 8.x?](https://www.percona.com/wp-content/uploads/2026/03/IN-Values-More-Expensive-After-Upgrading-to-MySQL-8.jpeg)
- content / image: [mysql-performance-tuning-1.png](https://www.percona.com/wp-content/uploads/2026/03/mysql-performance-tuning-1.png)

## Auteur source

Przemek joined Support Team at Percona in August 2012. Before that he spent over five years working for Wikia.com (Quantcast Top 50) as System Administrator where he was a key person responsible for seamless building up MySQL powered database infrastructure. Besides MySQL he worked on maintaining all other parts of LAMP stack, with main focus on automation, monitoring and backups.
