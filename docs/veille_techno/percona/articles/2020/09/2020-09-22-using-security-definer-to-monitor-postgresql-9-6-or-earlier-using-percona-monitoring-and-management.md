---
title: Using Security Definer to Monitor PostgreSQL 9.6 or Earlier Using Percona Monitoring and Management
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-security-definer-to-monitor-postgresql-9-6-or-earlier-using-percona-monitoring-and-management/
  post_id: 21478
source_author:
  name: Avinash Vallarapu
  slug: avi-vallarapu
  url: https://www.percona.com/blog/author/avi-vallarapu/
  website: ''
published_at: '2020-09-22T13:45:02'
published_at_gmt: '2020-09-22T13:45:02'
modified_at: '2026-03-26T20:09:39'
modified_at_gmt: '2026-03-26T20:09:39'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- category:monitoring:2104
- search:percona-monitoring-and-management
- search:pmm
- tag:percona-monitoring-and-management:2166
categories:
- Monitoring
- Percona Software
- PostgreSQL
- Security
category_slugs:
- monitoring
- percona-software
- postgresql
- security
tags:
- Monitoring
- Percona Monitoring and Management
- Percona Software
- PostgreSQL
- security
tag_slugs:
- monitoring
- percona-monitoring-and-management
- percona-software
- postgresql
- security
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/security-definer-postgresql.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using Security Definer to Monitor PostgreSQL 9.6 or Earlier Using Percona Monitoring and Management

Source: [Percona Blog](https://www.percona.com/blog/using-security-definer-to-monitor-postgresql-9-6-or-earlier-using-percona-monitoring-and-management/)

Auteur source: [Avinash Vallarapu](https://www.percona.com/blog/author/avi-vallarapu/)

Publication: 2020-09-22T13:45:02

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I have previously written a blog post on the detailed steps involved in enabling PostgreSQL monitoring using PMM. In that post, you could see me talking about the role: pg_monitor that can be granted to monitoring users. The pg_monitor role restricts a monitoring user from accessing user data but only grants access to statistic views … Continued

## Structure detectee

- H2: Security Invoker vs Security Definer in PostgreSQL
- H3: Security Invoker
- H3: Security Definer
- H4: Statistic views accessed by PMM that need access using a security definer:

## Images et graphiques reperes

- featured / image: [Using Security Definer to Monitor PostgreSQL 9.6 or Earlier Using Percona Monitoring and Management](https://www.percona.com/wp-content/uploads/2026/03/security-definer-postgresql.png)
- content / image: [security definer postgresql](https://www.percona.com/wp-content/uploads/2026/03/security-definer-postgresql-300x168.png)

## Auteur source

Avinash Vallarapu joined Percona in the month of May 2018. Before joining Percona, Avi worked as a Database Architect at OpenSCG for 2 Years and as a DBA Lead at Dell for 10 Years in Database technologies such as PostgreSQL, Oracle, MySQL and MongoDB. He has given several talks and trainings on PostgreSQL. He has good experience in performing Architectural Health Checks and Migrations to PostgreSQL Environments.
