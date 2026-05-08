---
title: Tuning Autovacuum in PostgreSQL and Autovacuum Internals
source:
  name: Percona Blog
  url: https://www.percona.com/blog/tuning-autovacuum-in-postgresql-and-autovacuum-internals/
  post_id: 19078
source_author:
  name: Avinash Vallarapu
  slug: avi-vallarapu
  url: https://www.percona.com/blog/author/avi-vallarapu/
  website: ''
published_at: '2018-08-10T17:44:18'
published_at_gmt: '2018-08-10T17:44:18'
modified_at: '2026-03-26T20:11:11'
modified_at_gmt: '2026-03-26T20:11:11'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- ProxySQL
matched_filters:
- search:proxysql
categories:
- Insight for DBAs
- PostgreSQL
category_slugs:
- insight-for-dbas
- postgresql
tags:
- performance tuning
- PostgreSQL Performance Tuning
tag_slugs:
- performance-tuning
- postgresql-performance-tuning
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Tuning-Autovacuum-in-PostgreSQL.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Tuning Autovacuum in PostgreSQL and Autovacuum Internals

Source: [Percona Blog](https://www.percona.com/blog/tuning-autovacuum-in-postgresql-and-autovacuum-internals/)

Auteur source: [Avinash Vallarapu](https://www.percona.com/blog/author/avi-vallarapu/)

Publication: 2018-08-10T17:44:18

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The performance of a PostgreSQL database can be compromised by dead tuples since they continue to occupy space and can lead to bloat. We provided an introduction to VACUUM and bloat in an earlier blog post. Now, though, it’s time to look at autovacuum for postgres, and the internals you to know to maintain a … Continued

## Structure detectee

- H3: What is autovacuum?
- H3: Why is autovacuum needed?
- H3: Logging autovacuum
- H3: When does PostgreSQL run autovacuum on a table?
- H2: Tuning Autovacuum in PostgreSQL
- H4: Is this a problem?
- H3: How do we identify the tables that need their autovacuum settings tuned?
- H3: How many autovacuum processes can run at a time?
- H3: Is VACUUM IO intensive?
- H3: You May Also Like

## Images et graphiques reperes

- featured / image: [Tuning Autovacuum in PostgreSQL and Autovacuum Internals](https://www.percona.com/wp-content/uploads/2026/03/Tuning-Autovacuum-in-PostgreSQL.jpg)
- content / image: [Watch Free Webinar: Using Vacuum to Clean Up PostgreSQL for Performance](https://www.percona.com/wp-content/uploads/2026/03/e47b76b2-719b-47d2-bf80-dc84f05a7cca.png)

## Auteur source

Avinash Vallarapu joined Percona in the month of May 2018. Before joining Percona, Avi worked as a Database Architect at OpenSCG for 2 Years and as a DBA Lead at Dell for 10 Years in Database technologies such as PostgreSQL, Oracle, MySQL and MongoDB. He has given several talks and trainings on PostgreSQL. He has good experience in performing Architectural Health Checks and Migrations to PostgreSQL Environments.
