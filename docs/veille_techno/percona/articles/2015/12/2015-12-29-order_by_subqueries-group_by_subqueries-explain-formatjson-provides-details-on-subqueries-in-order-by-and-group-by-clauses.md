---
title: 'EXPLAIN FORMAT=JSON: order_by_subqueries, group_by_subqueries details on subqueries in ORDER BY and GROUP BY'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/order_by_subqueries-group_by_subqueries-explain-formatjson-provides-details-on-subqueries-in-order-by-and-group-by-clauses/
  post_id: 10288
source_author:
  name: Sveta Smirnova
  slug: sveta-smirnova
  url: https://www.percona.com/blog/author/sveta-smirnova/
  website: http://www.percona.com/blog/
published_at: '2015-12-29T20:39:32'
published_at_gmt: '2015-12-29T20:39:32'
modified_at: '2026-04-28T22:47:20'
modified_at_gmt: '2026-04-28T22:47:20'
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
- EXPLAIN FORMAT=JSON
- EXPLAIN FORMAT=JSON is Cool!
- json
- MySQL Query Tuning
tag_slugs:
- explain
- explain-formatjson
- explain-formatjson-is-cool
- json
- mysql-query-tuning
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/shutterstock_286203344.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# EXPLAIN FORMAT=JSON: order_by_subqueries, group_by_subqueries details on subqueries in ORDER BY and GROUP BY

Source: [Percona Blog](https://www.percona.com/blog/order_by_subqueries-group_by_subqueries-explain-formatjson-provides-details-on-subqueries-in-order-by-and-group-by-clauses/)

Auteur source: [Sveta Smirnova](https://www.percona.com/blog/author/sveta-smirnova/)

Publication: 2015-12-29T20:39:32

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Another post in the EXPLAIN FORMAT=JSON is Cool! series! In this post, we’ll discuss how the EXPLAIN FORMAT=JSON provides optimization details for ORDER BY and GROUP BY operations in conjunction with order_by_subqueries and group_by_subqueries . EXPLAIN FORMAT = JSON can print details on how a subquery in ORDER BY is optimized: MySQL mysql> explain format=json select emp_no, concat(first_name, ' ', last_name) f2 from employees order by (select emp_no limit 1)G *************************** 1. row *************************** EXPLAIN: { "query_block": { "select_id": 1, "cost_info": { "query_cost": "60833.60" }, "ordering_operation": { "using_filesort": true, "table": { "table_name": "employees", "access_type": "ALL", "rows_examined_per_scan": 299843, "rows_produced_per_join": 299843, "filtered": "100.00", "cost_info": { "read_cost": "865.00", "eval_cost": "59968.60", "prefix_cost": "60833...

## Images et graphiques reperes

- featured / image: [EXPLAIN FORMAT=JSON: order_by_subqueries, group_by_subqueries details on subqueries in ORDER BY and GROUP BY](https://www.percona.com/wp-content/uploads/2026/03/shutterstock_286203344.jpg)
- content / image: [EXPLAIN FORMAT](https://www.percona.com/wp-content/uploads/2026/03/shutterstock_286203344-300x225.jpg)

## Auteur source

Sveta joined Percona in 2015. Her main professional interests are problem solving, working with tricky issues, bugs, finding patterns that can solve typical issues quicker and teaching others how to deal with MySQL issues, bugs and gotchas effectively. Before joining Percona Sveta worked as a Support Engineer in the MySQL Bugs Analysis Support Group in MySQL AB-Sun-Oracle. She is the author of the books "MySQL Troubleshooting" and "MySQL Cookbook, 4th Edition".
