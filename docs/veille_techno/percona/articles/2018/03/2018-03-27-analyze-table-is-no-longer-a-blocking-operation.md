---
title: ANALYZE TABLE Is No Longer a Blocking Operation
source:
  name: Percona Blog
  url: https://www.percona.com/blog/analyze-table-is-no-longer-a-blocking-operation/
  post_id: 18224
source_author:
  name: Sveta Smirnova
  slug: sveta-smirnova
  url: https://www.percona.com/blog/author/sveta-smirnova/
  website: http://www.percona.com/blog/
published_at: '2018-03-27T19:35:56'
published_at_gmt: '2018-03-27T19:35:56'
modified_at: '2026-03-20T21:44:00'
modified_at_gmt: '2026-03-20T21:44:00'
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
- Storage Engine
category_slugs:
- insight-for-dbas
- mysql
- storage-engine
tags:
- ANALYZE TABLE
- InnoDB
- MySQL
- Optimizer
tag_slugs:
- analyze-table
- innodb
- mysql
- optimizer
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/analyze-table-e1522177142832.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# ANALYZE TABLE Is No Longer a Blocking Operation

Source: [Percona Blog](https://www.percona.com/blog/analyze-table-is-no-longer-a-blocking-operation/)

Auteur source: [Sveta Smirnova](https://www.percona.com/blog/author/sveta-smirnova/)

Publication: 2018-03-27T19:35:56

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this post, I’ll discuss the fix for lp:1704195 (migrated to PS-2503), which prevents ANALYZE TABLE from blocking all subsequent queries on the same table. In November 2017, Percona released a fix for lp:1704195 (migrated to PS-2503), created by Laurynas Biveinis. The fix, included with Percona Server for MySQL since versions 5.6.38-83.0 and 5.7.20-18, stops ANALYZE TABLE from invalidating query and … Continued

## Structure detectee

- H4: Why is this important?
- H4: Why do we need to run ANALYZE TABLE?

## Images et graphiques reperes

- featured / image: [ANALYZE TABLE Is No Longer a Blocking Operation](https://www.percona.com/wp-content/uploads/2026/03/analyze-table-e1522177142832.jpg)
- content / image: [analyze table](https://www.percona.com/wp-content/uploads/2026/03/analyze-table-300x200.jpg)

## Auteur source

Sveta joined Percona in 2015. Her main professional interests are problem solving, working with tricky issues, bugs, finding patterns that can solve typical issues quicker and teaching others how to deal with MySQL issues, bugs and gotchas effectively. Before joining Percona Sveta worked as a Support Engineer in the MySQL Bugs Analysis Support Group in MySQL AB-Sun-Oracle. She is the author of the books "MySQL Troubleshooting" and "MySQL Cookbook, 4th Edition".
