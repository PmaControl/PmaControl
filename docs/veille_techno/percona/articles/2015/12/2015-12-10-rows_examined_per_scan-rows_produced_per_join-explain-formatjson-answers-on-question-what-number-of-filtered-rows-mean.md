---
title: 'rows_examined_per_scan, rows_produced_per_join: EXPLAIN FORMAT=JSON answers on question "What number of filtered rows
  mean?"'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/rows_examined_per_scan-rows_produced_per_join-explain-formatjson-answers-on-question-what-number-of-filtered-rows-mean/
  post_id: 10248
source_author:
  name: Sveta Smirnova
  slug: sveta-smirnova
  url: https://www.percona.com/blog/author/sveta-smirnova/
  website: http://www.percona.com/blog/
published_at: '2015-12-10T23:00:16'
published_at_gmt: '2015-12-10T23:00:16'
modified_at: '2026-05-05T17:01:16'
modified_at_gmt: '2026-05-05T17:01:16'
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
- MySQL Query Tuning
tag_slugs:
- explain
- explain-formatjson-is-cool
- mysql-query-tuning
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/shutterstock_290056829.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# rows_examined_per_scan, rows_produced_per_join: EXPLAIN FORMAT=JSON answers on question "What number of filtered rows mean?"

Source: [Percona Blog](https://www.percona.com/blog/rows_examined_per_scan-rows_produced_per_join-explain-formatjson-answers-on-question-what-number-of-filtered-rows-mean/)

Auteur source: [Sveta Smirnova](https://www.percona.com/blog/author/sveta-smirnova/)

Publication: 2015-12-10T23:00:16

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

At the end of my talk “Troubleshooting MySQL Performance” at the LinuxPiter conference, a user asked me a question: “What does the EXPLAIN ‘filtered’ field mean, and how do I use it?” I explained that this is the percentage of rows that were actually needed, against the equal or bigger number of resolved rows. While … Continued

## Images et graphiques reperes

- featured / image: [rows_examined_per_scan, rows_produced_per_join: EXPLAIN FORMAT=JSON answers on question "What number of filtered rows mean?"](https://www.percona.com/wp-content/uploads/2026/03/shutterstock_290056829.jpg)
- content / image: [EXPLAIN](https://www.percona.com/wp-content/uploads/2026/03/shutterstock_290056829-300x200.jpg)

## Auteur source

Sveta joined Percona in 2015. Her main professional interests are problem solving, working with tricky issues, bugs, finding patterns that can solve typical issues quicker and teaching others how to deal with MySQL issues, bugs and gotchas effectively. Before joining Percona Sveta worked as a Support Engineer in the MySQL Bugs Analysis Support Group in MySQL AB-Sun-Oracle. She is the author of the books "MySQL Troubleshooting" and "MySQL Cookbook, 4th Edition".
