---
title: 'EXPLAIN FORMAT=JSON: nested_loop makes JOIN hierarchy transparent'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/explain-format-json-nested-loop-makes-join-hierarchy-transparent/
  post_id: 10356
source_author:
  name: Sveta Smirnova
  slug: sveta-smirnova
  url: https://www.percona.com/blog/author/sveta-smirnova/
  website: http://www.percona.com/blog/
published_at: '2016-02-29T22:38:05'
published_at_gmt: '2016-02-29T22:38:05'
modified_at: '2026-04-28T22:48:53'
modified_at_gmt: '2026-04-28T22:48:53'
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
- explain
- EXPLAIN FORMAT=JSON is Cool!
- json
- MySQL Query Tuning
tag_slugs:
- explain
- explain-formatjson-is-cool
- json
- mysql-query-tuning
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/EXPLAIN-FORMATJSON.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# EXPLAIN FORMAT=JSON: nested_loop makes JOIN hierarchy transparent

Source: [Percona Blog](https://www.percona.com/blog/explain-format-json-nested-loop-makes-join-hierarchy-transparent/)

Auteur source: [Sveta Smirnova](https://www.percona.com/blog/author/sveta-smirnova/)

Publication: 2016-02-29T22:38:05

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Once again it’s time for another EXPLAIN FORMAT=JSON is cool! post. This post will discuss how EXPLAIN FORMAT=JSON allows the nested_loop command to make the JOIN operation hierarchy transparent. The regular EXPLAIN command lists each table that participates in a JOIN operation on a single row. This works perfectly for simple queries: MySQL mysql> explain select * from employees join titles join salariesG *************************** 1. row *************************** id: 1 select_type: SIMPLE table: employees partitions: NULL type: ALL possible_keys: NULL key: NULL key_len: NULL ref: NULL rows: 299379 filtered: 100.00 Extra: NULL *************************** 2. row *************************** id: 1 select_type: SIMPLE table: titles partitions: NULL type: ALL possible_keys: NULL key: NULL key_len: NULL ref: NULL rows: 442724 filtered: 100.00 Extra: Using join buffer (Block Nested Loop)...

## Images et graphiques reperes

- featured / image: [EXPLAIN FORMAT=JSON: nested_loop makes JOIN hierarchy transparent](https://www.percona.com/wp-content/uploads/2026/03/EXPLAIN-FORMATJSON.jpg)
- content / image: [EXPLAIN FORMAT=JSON](https://www.percona.com/wp-content/uploads/2026/03/EXPLAIN-FORMATJSON-300x300.jpg)

## Auteur source

Sveta joined Percona in 2015. Her main professional interests are problem solving, working with tricky issues, bugs, finding patterns that can solve typical issues quicker and teaching others how to deal with MySQL issues, bugs and gotchas effectively. Before joining Percona Sveta worked as a Support Engineer in the MySQL Bugs Analysis Support Group in MySQL AB-Sun-Oracle. She is the author of the books "MySQL Troubleshooting" and "MySQL Cookbook, 4th Edition".
