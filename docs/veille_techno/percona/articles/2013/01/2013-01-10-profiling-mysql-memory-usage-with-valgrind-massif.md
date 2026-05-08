---
title: Profiling MySQL Memory Usage With Valgrind Massif
source:
  name: Percona Blog
  url: https://www.percona.com/blog/profiling-mysql-memory-usage-with-valgrind-massif/
  post_id: 6511
source_author:
  name: Roel Van de Paar
  slug: roel_van_de_paar
  url: https://www.percona.com/blog/author/roel_van_de_paar/
  website: http://au.linkedin.com/in/roelvandepaar
published_at: '2013-01-10T00:32:01'
published_at_gmt: '2013-01-10T00:32:01'
modified_at: '2026-05-05T22:37:36'
modified_at_gmt: '2026-05-05T22:37:36'
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
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Profiling MySQL Memory Usage With Valgrind Massif

Source: [Percona Blog](https://www.percona.com/blog/profiling-mysql-memory-usage-with-valgrind-massif/)

Auteur source: [Roel Van de Paar](https://www.percona.com/blog/author/roel_van_de_paar/)

Publication: 2013-01-10T00:32:01

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

There are times where you need to know exactly how much memory the mysqld server (or any other program) is using, where (i.e. for what function) it was allocated, how it got there (a backtrace, please!), and at what point in time the allocation happened. For example; you may have noticed a sharp memory increase … Continued

## Auteur source

Roel leads Percona's QA team. Before coming to Percona, he contributed significantly to the QA infrastructure at Oracle. Roel has a varied background in IT, backed up by many industry leading certifications. He also enjoys time with God, his wife and 5 children, or heading into nature. Roel tweets at @RoelVandePaar
