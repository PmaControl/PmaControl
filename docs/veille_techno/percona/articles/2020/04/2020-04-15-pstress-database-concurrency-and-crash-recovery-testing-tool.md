---
title: 'Pstress: Database Concurrency and Crash Recovery Testing Tool'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/pstress-database-concurrency-and-crash-recovery-testing-tool/
  post_id: 22040
source_author:
  name: Dev Montiontactic
  slug: mt_admin
  url: https://www.percona.com/blog/author/mt_admin/
  website: ''
published_at: '2020-04-15T13:01:53'
published_at_gmt: '2020-04-15T13:01:53'
modified_at: '2026-03-23T15:11:30'
modified_at_gmt: '2026-03-23T15:11:30'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-xtrabackup
- search:xtrabackup
categories:
- MySQL
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- MySQL
- Percona Software
- Pstress
tag_slugs:
- mysql
- percona-software
- pstress
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/pstress-Percona.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Pstress: Database Concurrency and Crash Recovery Testing Tool

Source: [Percona Blog](https://www.percona.com/blog/pstress-database-concurrency-and-crash-recovery-testing-tool/)

Auteur source: [Dev Montiontactic](https://www.percona.com/blog/author/mt_admin/)

Publication: 2020-04-15T13:01:53

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Databases are complicated software made to handle the concurrent load while making specific guarantees about data consistency and availability. There are many scenarios which should be tested that can only happen under concurrent conditions. Pstress is a probability-based open-source database testing tool designed to run in concurrency and to test if the database can recover when … Continued

## Structure detectee

- H2: Key Features of Pstress
- H2: How it Works
- H3: Why We Have Multiple Steps
- H2: Different Use Cases for Pstress
- H3: Regression Testing
- H3: Feature Testing
- H3: Crash Recovery Testing
- H2: Modules in Pstress
- H3: Driver Script
- H3: Workload
- H2: Types of Transactions
- H2: Design
- H2: Success Stories with MySQL
- H2: Comparison with Existing Tools
- H2: Pquery and Pstress
- H2: Limitations of Pstress
- H2: Features in the Development Stage

## Images et graphiques reperes

- featured / image: [Pstress: Database Concurrency and Crash Recovery Testing Tool](https://www.percona.com/wp-content/uploads/2026/03/pstress-Percona.png)
- content / image: [Pstress Percona](https://www.percona.com/wp-content/uploads/2026/03/pstress-Percona-300x168.png)
