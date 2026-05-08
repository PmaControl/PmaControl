---
title: Correcting MySQL Inaccurate Table Statistics for Better Execution Plan
source:
  name: Percona Blog
  url: https://www.percona.com/blog/correcting-mysql-inaccurate-table-statistics-for-better-execution-plan/
  post_id: 25575
source_author:
  name: Edwin Wang
  slug: edwin-wang
  url: https://www.percona.com/blog/author/edwin-wang/
  website: ''
published_at: '2022-04-15T11:48:19'
published_at_gmt: '2022-04-15T11:48:19'
modified_at: '2026-03-26T20:31:59'
modified_at_gmt: '2026-03-26T20:31:59'
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
category_slugs:
- insight-for-dbas
- mysql
tags:
- execution plan
- innodb_stats_persistent_sample_pages
- MySQL
- mysql-and-variants
- table statistics
tag_slugs:
- execution-plan
- innodb_stats_persistent_sample_pages
- mysql
- mysql-and-variants
- table-statistics
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Correcting-MySQL-Inaccurate-Table-Statistics.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Correcting MySQL Inaccurate Table Statistics for Better Execution Plan

Source: [Percona Blog](https://www.percona.com/blog/correcting-mysql-inaccurate-table-statistics-for-better-execution-plan/)

Auteur source: [Edwin Wang](https://www.percona.com/blog/author/edwin-wang/)

Publication: 2022-04-15T11:48:19

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Abstract: By diving into the details of our case study, we will explain how incorrect table statistics may lead the optimizer to choose a suboptimal execution plan. We will also go into how MySQL calculates the table statistics and the ways to correct the table statistics to prevent it from happening again. Case study: Incorrect table … Continued

## Structure detectee

- H3: Abstract:
- H3: Case study: Incorrect table statistics lead the optimizer to choose a poor execution plan.
- H2: How InnoDB Calculates the Table Statistics
- H3: Solution: How can we correct the table statistics and prevent it from happening again?
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Correcting MySQL Inaccurate Table Statistics for Better Execution Plan](https://www.percona.com/wp-content/uploads/2026/03/Correcting-MySQL-Inaccurate-Table-Statistics.png)
- content / image: [Correcting MySQL Inaccurate Table Statistics](https://www.percona.com/wp-content/uploads/2026/03/Correcting-MySQL-Inaccurate-Table-Statistics-300x157.png)

## Auteur source

A father with 1 wife, 2 kids, and 2 dogs. A DBA with 20 years of experience in RDBMS i.e. MySQL, Oracle Etc. Currently working at Percona as Senior Mysql Database Administrator working on different environments and scenarios, including database installation/configuration/maintenance, trouble-shooting, design, performance tuning, DB High Availability architecture, and other infrastructure-related issues, AWS cloud, ansible, GCP, etc.
