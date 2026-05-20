---
title: 'grouping_operation, duplicates_removal: EXPLAIN FORMAT=JSON has all details about GROUP BY'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/grouping_operation-duplicates_removal-explain-formatjson-has-all-details-about-group-by/
  post_id: 10296
source_author:
  name: Sveta Smirnova
  slug: sveta-smirnova
  url: https://www.percona.com/blog/author/sveta-smirnova/
  website: http://www.percona.com/blog/
published_at: '2016-01-05T01:45:17'
published_at_gmt: '2016-01-05T01:45:17'
modified_at: '2026-04-28T22:47:37'
modified_at_gmt: '2026-04-28T22:47:37'
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
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/shutterstock_280632665.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# grouping_operation, duplicates_removal: EXPLAIN FORMAT=JSON has all details about GROUP BY

Source: [Percona Blog](https://www.percona.com/blog/grouping_operation-duplicates_removal-explain-formatjson-has-all-details-about-group-by/)

Auteur source: [Sveta Smirnova](https://www.percona.com/blog/author/sveta-smirnova/)

Publication: 2016-01-05T01:45:17

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In the previous EXPLAIN FORMAT=JSON is Cool! series blog post, we discussed the group_by_subqueries member (which is child of grouping_operation ). Let’s now focus on the grouping_operation and other details of GROUP BY processing. grouping_operation simply shows the details of what happens when the GROUP BY clause is run: MySQL mysql> explain format=json select dept_no from dept_emp group by dept_noG *************************** 1. row *************************** EXPLAIN: { "query_block": { "select_id": 1, "cost_info": { "query_cost": "14.40" }, "grouping_operation": { "using_filesort": false, "table": { "table_name": "dept_emp", "access_type": "range", "possible_keys": [ "PRIMARY", "emp_no", "dept_no" ], "key": "dept_no", "used_key_parts": [ "dept_no" ], "key_length": "4", "rows_examined_per_scan": 9, "rows_produced_per_join": 9, "filtered": "100.00", "using_index_for_group_by": true...

## Images et graphiques reperes

- featured / image: [grouping_operation, duplicates_removal: EXPLAIN FORMAT=JSON has all details about GROUP BY](https://www.percona.com/wp-content/uploads/2026/03/shutterstock_280632665.jpg)
- content / image: [EXPLAIN FORMAT=JSON](https://www.percona.com/wp-content/uploads/2026/03/shutterstock_280632665-300x200.jpg)

## Auteur source

Sveta joined Percona in 2015. Her main professional interests are problem solving, working with tricky issues, bugs, finding patterns that can solve typical issues quicker and teaching others how to deal with MySQL issues, bugs and gotchas effectively. Before joining Percona Sveta worked as a Support Engineer in the MySQL Bugs Analysis Support Group in MySQL AB-Sun-Oracle. She is the author of the books "MySQL Troubleshooting" and "MySQL Cookbook, 4th Edition".
