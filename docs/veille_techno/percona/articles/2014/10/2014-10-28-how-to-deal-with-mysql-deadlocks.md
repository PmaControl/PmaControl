---
title: How to deal with MySQL deadlocks
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-deal-with-mysql-deadlocks/
  post_id: 8668
source_author:
  name: Peiran Song
  slug: peiran-song
  url: https://www.percona.com/blog/author/peiran-song/
  website: http://www.percona.com/forums/
published_at: '2014-10-28T07:00:21'
published_at_gmt: '2014-10-28T07:00:21'
modified_at: '2026-04-28T22:12:46'
modified_at_gmt: '2026-04-28T22:12:46'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:percona-toolkit
categories:
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- InnoDB
- MySQL 5.6
- MySQL deadlocks
- Peiran Song
- Primary
- pt-deadlock-logger
tag_slugs:
- innodb
- mysql-5-6
- mysql-deadlocks
- peiran-song
- primary
- pt-deadlock-logger
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-deadlocks.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to deal with MySQL deadlocks

Source: [Percona Blog](https://www.percona.com/blog/how-to-deal-with-mysql-deadlocks/)

Auteur source: [Peiran Song](https://www.percona.com/blog/author/peiran-song/)

Publication: 2014-10-28T07:00:21

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A deadlock in MySQL happens when two or more transactions mutually hold and request for locks, creating a cycle of dependencies. In a transaction system, deadlocks are a fact of life and not completely avoidable. InnoDB automatically detects transaction deadlocks, rollbacks a transaction immediately and returns an error. It uses a metric to pick the … Continued

## Structure detectee

- H2: How to diagnose a MySQL deadlock
- H2: How to avoid a MySQL deadlock

## Images et graphiques reperes

- featured / image: [How to deal with MySQL deadlocks](https://www.percona.com/wp-content/uploads/2026/03/MySQL-deadlocks.jpg)
- content / image: [Find and fix MySQL issues faster with Percona Monitoring and Management](https://www.percona.com/wp-content/uploads/2026/03/4e347054-beae-4f4e-95fd-5bc84de30078.png)

## Auteur source

Peiran joined Percona as a Support Engineer in March 2014. Prior to that, she had served as database architect and database administrator at companies in the fields of SAAS, mobile and social games and web services.
