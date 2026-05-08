---
title: Quickly Troubleshoot Metadata Locks in MySQL 5.7
source:
  name: Percona Blog
  url: https://www.percona.com/blog/quickly-troubleshooting-metadata-locks-mysql-5-7/
  post_id: 16031
source_author:
  name: Jaime Sicam
  slug: jaimesicam
  url: https://www.percona.com/blog/author/jaimesicam/
  website: ''
published_at: '2016-12-28T19:52:57'
published_at_gmt: '2016-12-28T19:52:57'
modified_at: '2026-05-05T18:25:10'
modified_at_gmt: '2026-05-05T18:25:10'
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
- '5.7'
- metadata lock
- metadata_locks
- PERFORMANCE_SCHEMA
tag_slugs:
- 5-7
- metadata-lock
- metadata_locks
- performance_schema
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/metadata-locks-e1482954449614.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Quickly Troubleshoot Metadata Locks in MySQL 5.7

Source: [Percona Blog](https://www.percona.com/blog/quickly-troubleshooting-metadata-locks-mysql-5-7/)

Auteur source: [Jaime Sicam](https://www.percona.com/blog/author/jaimesicam/)

Publication: 2016-12-28T19:52:57

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In a previous article, Ovais demonstrated how a DDL can render a table blocked from new queries. In another article, Valerii introduced performance_schema.metadata_locks, which is available in MySQL 5.7 and exposes metadata lock details. Given this information, here’s a quick way to troubleshoot metadata locks by creating a stored procedure that can: Find out which … Continued

## Structure detectee

- H3: Setting up instrumentation
- H3: Testing

## Images et graphiques reperes

- featured / image: [Quickly Troubleshoot Metadata Locks in MySQL 5.7](https://www.percona.com/wp-content/uploads/2026/03/metadata-locks-e1482954449614.png)

## Auteur source

Jaime is a Senior Support Engineer at Percona. Prior to joining Percona, Jaime worked as a remote system administrator managing high-traffic websites and consultant for several local companies. He also conducted Linux trainings in several schools. Jaime is based in the Philippines. He enjoys road trips and photography in his spare time.
