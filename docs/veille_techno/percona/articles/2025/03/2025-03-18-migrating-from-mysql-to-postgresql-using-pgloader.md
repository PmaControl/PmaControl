---
title: 'Migrating from MySQL to PostgreSQL Using pgloader: A Practical Guide'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/migrating-from-mysql-to-postgresql-using-pgloader/
  post_id: 27771
source_author:
  name: Robert Bernier
  slug: robert-bernier
  url: https://www.percona.com/blog/author/robert-bernier/
  website: ''
published_at: '2025-03-18T16:05:29'
published_at_gmt: '2025-03-18T16:05:29'
modified_at: '2026-05-05T17:19:55'
modified_at_gmt: '2026-05-05T17:19:55'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- search:percona-monitoring-and-management
- search:pmm
categories:
- Insight for DBAs
- Open Source
- Percona Software
- PostgreSQL
category_slugs:
- insight-for-dbas
- open-source
- percona-software
- postgresql
tags:
- PostgreSQL
tag_slugs:
- postgresql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Migrating-from-MySQL-to-PostgreSQL-Using-pgloader.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Migrating from MySQL to PostgreSQL Using pgloader: A Practical Guide

Source: [Percona Blog](https://www.percona.com/blog/migrating-from-mysql-to-postgresql-using-pgloader/)

Auteur source: [Robert Bernier](https://www.percona.com/blog/author/robert-bernier/)

Publication: 2025-03-18T16:05:29

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This post was originally published in December 2023 and was updated in March 2025. These days, there’s significant industry interest in moving database systems to PostgreSQL. Often, these are greenfield projects focusing on design and architecture. However, decisions are increasingly being made to move from existing platforms, like MySQL, to PostgreSQL for compelling business and … Continued

## Structure detectee

- H2: About pgLoader: A powerful migration tool
- H2: Database migration POC: MySQL to PostgreSQL with pgloader
- H3: Objective
- H3: Migration environment setup
- H3: Migration method: Preparing the source
- H2: Step-by-step migration process using pgloader
- H3: 1. PostgreSQL host preparation
- H3: MySQL host
- H3: pgLoader configuration file
- H3: pgLoader invocation
- H2: Pre-migration: MySQL database updates
- H3: Issue #1: Incompatible values/data types, MySQL (datetime) -> Postgres (timestamp)
- H3: Issue #2: Incompatible value/datatype, MySQL (time) -> Postgres (timestamp)
- H3: Issue #3: MySQL table names are too long
- H3: Issue #4: MySQL index names are too long
- H3: Issue #5: MySQL index names duplicated
- H3: Issue #6: Missing data detected in tables resulting in Foreign Key Constraint failures
- H2: Final pgloader configuration for data migration
- H2: Conclusion
- H3: References
- H3: About pgloader
- H3: About Percona Monitoring and Management
- H3: About MySQL
- H2: FAQ: Migrating from MySQL to PostgreSQL using pgloader

## Images et graphiques reperes

- featured / image: [Migrating from MySQL to PostgreSQL Using pgloader: A Practical Guide](https://www.percona.com/wp-content/uploads/2026/03/Migrating-from-MySQL-to-PostgreSQL-Using-pgloader.jpg)
- content / image: [PostgreSQL Enterprise](https://www.percona.com/wp-content/uploads/2026/03/Postgres-Enterprise-Real-Cost-DIY.png)

## Auteur source

Robert's first working computer was the very user-friendly IBM 360 with an awesome 4MB RAM. After a round of much needed therapy overcoming the trauma of programming with punch cards he discovered the IBM-XT and the miracle of DOS 2.0. Years later, Robert became enamored with Linux and the opensource world and after meeting one of the members of CORE his primary focus had become all things PostgreSQL. Robert has since then worked in mom and pop companies, fortune 50 corporations and a number of very cool environments including the famed Los Alamos National Laboratory in New Mexico, birthplace of the atomic age. Although reluctant to leave the enjoyable experience of California's Silicon Valley commuter life, he returned to the Pacific Northwest and once again experienced real weather. These days, he serves as the PostgreSQL Consultant here at Percona.
