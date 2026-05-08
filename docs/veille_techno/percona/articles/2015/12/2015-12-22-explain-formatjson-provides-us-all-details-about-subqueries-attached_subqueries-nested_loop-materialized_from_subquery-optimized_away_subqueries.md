---
title: 'EXPLAIN FORMAT=JSON: everything about attached_subqueries, optimized_away_subqueries, materialized_from_subquery'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/explain-formatjson-provides-us-all-details-about-subqueries-attached_subqueries-nested_loop-materialized_from_subquery-optimized_away_subqueries/
  post_id: 10280
source_author:
  name: Sveta Smirnova
  slug: sveta-smirnova
  url: https://www.percona.com/blog/author/sveta-smirnova/
  website: http://www.percona.com/blog/
published_at: '2015-12-22T22:41:57'
published_at_gmt: '2015-12-22T22:41:57'
modified_at: '2026-04-08T15:12:26'
modified_at_gmt: '2026-04-08T15:12:26'
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
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/shutterstock_290445947.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# EXPLAIN FORMAT=JSON: everything about attached_subqueries, optimized_away_subqueries, materialized_from_subquery

Source: [Percona Blog](https://www.percona.com/blog/explain-formatjson-provides-us-all-details-about-subqueries-attached_subqueries-nested_loop-materialized_from_subquery-optimized_away_subqueries/)

Auteur source: [Sveta Smirnova](https://www.percona.com/blog/author/sveta-smirnova/)

Publication: 2015-12-22T22:41:57

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

EXPLAIN FORMAT=JSON The regular EXPLAIN command already provides some information about subquery optimization. For example, you can find out if the subquery is dependent or not, and (since version 5.6) if it was materialized: MySQL mysql> explain select dept_name from departments where dept_no in (select dept_no from dept_manager where to_date is not null)G<br>*************************** 1. row ***************************<br> id: 1<br> select_type: SIMPLE<br> table: departments<br> partitions: NULL<br> type: index<br>possible_keys: PRIMARY<br> key: dept_name<br> key_len: 42<br> ref: NULL<br> rows: 9<br> filtered: 100.00<br> Extra: Using where; Using index<br>*************************** 2. row ***************************<br> id: 1<br> select_type: SIMPLE<br> table: <subquery2><br> partitions: NULL<br> type: eq_ref<br>possible_keys: <auto_key><br> key: <auto_key><br> key_len: 4<br> ref:...

## Structure detectee

- H2: EXPLAIN FORMAT=JSON

## Images et graphiques reperes

- featured / image: [EXPLAIN FORMAT=JSON: everything about attached_subqueries, optimized_away_subqueries, materialized_from_subquery](https://www.percona.com/wp-content/uploads/2026/03/shutterstock_290445947.jpg)
- content / image: [EXPLAIN FORMAT=JSON](https://www.percona.com/wp-content/uploads/2026/03/shutterstock_290445947-300x200.jpg)

## Auteur source

Sveta joined Percona in 2015. Her main professional interests are problem solving, working with tricky issues, bugs, finding patterns that can solve typical issues quicker and teaching others how to deal with MySQL issues, bugs and gotchas effectively. Before joining Percona Sveta worked as a Support Engineer in the MySQL Bugs Analysis Support Group in MySQL AB-Sun-Oracle. She is the author of the books "MySQL Troubleshooting" and "MySQL Cookbook, 4th Edition".
