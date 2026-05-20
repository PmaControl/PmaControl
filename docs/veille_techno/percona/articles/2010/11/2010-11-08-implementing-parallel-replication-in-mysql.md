---
title: Implementing Parallel Replication in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/implementing-parallel-replication-in-mysql/
  post_id: 2499
source_author:
  name: Baron Schwartz
  slug: baron
  url: https://www.percona.com/blog/author/baron/
  website: http://www.percona.com/
published_at: '2010-11-08T22:38:12'
published_at_gmt: '2010-11-08T22:38:12'
modified_at: '2026-03-23T21:47:00'
modified_at_gmt: '2026-03-23T21:47:00'
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
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Implementing Parallel Replication in MySQL

Source: [Percona Blog](https://www.percona.com/blog/implementing-parallel-replication-in-mysql/)

Auteur source: [Baron Schwartz](https://www.percona.com/blog/author/baron/)

Publication: 2010-11-08T22:38:12

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Based on discussions with several clients, we are strongly considering implementing a limited form of parallel replication. Single-threaded replication is one of the most severe limitations in the MySQL server. We have a brief outline of the ideas at this wiki blueprint. So far, the “binlog order” idea is the only one that is workable. … Continued

## Auteur source

Baron is the lead author of High Performance MySQL. He is a former Percona employee.
