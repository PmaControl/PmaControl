---
title: 'EXPLAIN FORMAT=JSON: buffer_result is not hidden!'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/explain-format-json-buffer-result-not-hidden/
  post_id: 10330
source_author:
  name: Sveta Smirnova
  slug: sveta-smirnova
  url: https://www.percona.com/blog/author/sveta-smirnova/
  website: http://www.percona.com/blog/
published_at: '2016-02-09T20:41:42'
published_at_gmt: '2016-02-09T20:41:42'
modified_at: '2026-04-28T22:48:21'
modified_at_gmt: '2026-04-28T22:48:21'
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
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/shutterstock_150140366-e1481649416297.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# EXPLAIN FORMAT=JSON: buffer_result is not hidden!

Source: [Percona Blog](https://www.percona.com/blog/explain-format-json-buffer-result-not-hidden/)

Auteur source: [Sveta Smirnova](https://www.percona.com/blog/author/sveta-smirnova/)

Publication: 2016-02-09T20:41:42

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Time for another entry in the EXPLAIN FORMAT=JSON is cool! series. Today we’re going to look at how you can view the buffer result using JSON (instead of the regular EXPLAIN command. Regular EXPLAIN does not identify if SQL_BUFFER_RESULT was used at all. To demonstrate, let’s run this query: MySQL mysql> explain select * from salariesG *************************** 1. row *************************** id: 1 select_type: SIMPLE table: salaries partitions: NULL type: ALL possible_keys: NULL key: NULL key_len: NULL ref: NULL rows: 2557022 filtered: 100.00 Extra: NULL 1 row in set, 1 warning (0.01 sec) Note (Code 1003): /* select#1 */ select `employees`.`salaries`.`emp_no` AS `emp_no`,`employees`.`salaries`.`salary` AS `salary`,`employees`.`salaries`.`from_date` AS `from_date`,`employees`.`salaries`.`to_date` AS `to_date` from `employees`.`salaries` 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 m...

## Images et graphiques reperes

- featured / image: [EXPLAIN FORMAT=JSON: buffer_result is not hidden!](https://www.percona.com/wp-content/uploads/2026/03/shutterstock_150140366-e1481649416297.jpg)
- content / image: [EXPLAIN FORMAT=JSON](https://www.percona.com/wp-content/uploads/2026/03/shutterstock_150140366-300x225.jpg)

## Auteur source

Sveta joined Percona in 2015. Her main professional interests are problem solving, working with tricky issues, bugs, finding patterns that can solve typical issues quicker and teaching others how to deal with MySQL issues, bugs and gotchas effectively. Before joining Percona Sveta worked as a Support Engineer in the MySQL Bugs Analysis Support Group in MySQL AB-Sun-Oracle. She is the author of the books "MySQL Troubleshooting" and "MySQL Cookbook, 4th Edition".
