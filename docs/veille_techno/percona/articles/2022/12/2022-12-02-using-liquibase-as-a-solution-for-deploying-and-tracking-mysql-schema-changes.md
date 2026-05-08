---
title: Using Liquibase as a Solution for Deploying and Tracking MySQL Schema Changes
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-liquibase-as-a-solution-for-deploying-and-tracking-mysql-schema-changes/
  post_id: 26334
source_author:
  name: Anil Joshi
  slug: anil-joshi
  url: https://www.percona.com/blog/author/anil-joshi/
  website: ''
published_at: '2022-12-02T14:58:00'
published_at_gmt: '2022-12-02T14:58:00'
modified_at: '2026-03-26T20:30:31'
modified_at_gmt: '2026-03-26T20:30:31'
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
- Database Trends
- Insight for Developers
- MySQL
- Open Source
category_slugs:
- database-trends
- insight-for-developers
- mysql
- open-source
tags:
- Liquibase
- MySQL
- mysql-and-variants
tag_slugs:
- liquibase
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Liquibase-mysql-schema-changes.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using Liquibase as a Solution for Deploying and Tracking MySQL Schema Changes

Source: [Percona Blog](https://www.percona.com/blog/using-liquibase-as-a-solution-for-deploying-and-tracking-mysql-schema-changes/)

Auteur source: [Anil Joshi](https://www.percona.com/blog/author/anil-joshi/)

Publication: 2022-12-02T14:58:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Database-as-code service is a new concept and gaining some popularity in recent years. As we already know, we have deployment solutions for application code. Managing and tracking application changes are quite easy with tools like Git and Jenkins. Now this concept is applied in the database domain as well, assuming SQL as a code to … Continued

## Structure detectee

- H2: What is Liquibase?
- H2: Installing Liquibase
- H2: How to use Liquibase with MySQL
- H4: Output:
- H4: a) Table: DATABASECHANGELOG
- H4: b) Table: DATABASECHANGELOGLOCK
- H3: Let’s see the steps to perform the rollback operations
- H4: Output:
- H2: Liquibase integration with Percona Toolkit (pt-osc)
- H3: Let’s see the steps to use pt-osc with Liquibase extension
- H4: Output
- H2: Summary
- H3: Further reading

## Images et graphiques reperes

- featured / image: [Using Liquibase as a Solution for Deploying and Tracking MySQL Schema Changes](https://www.percona.com/wp-content/uploads/2026/03/Liquibase-mysql-schema-changes.png)
- content / image: [Liquibase mysql schema changes](https://www.percona.com/wp-content/uploads/2026/03/Liquibase-mysql-schema-changes-300x157.png)

## Auteur source

I am Anil Joshi, and I work for Percona as a support engineer. I've worked with some well-known Open Source database technologies (MySQL/MariaDB, MongoDB, and Redis) for almost ten years. I am keenly interested in learning new databases and writing database content.
