---
title: EXPLAIN FORMAT=JSON provides insights on optimizer_switch effectiveness
source:
  name: Percona Blog
  url: https://www.percona.com/blog/explain-formatjson-provides-insights-on-optimizer_switch-effectiveness/
  post_id: 10283
source_author:
  name: Sveta Smirnova
  slug: sveta-smirnova
  url: https://www.percona.com/blog/author/sveta-smirnova/
  website: http://www.percona.com/blog/
published_at: '2015-12-28T19:20:15'
published_at_gmt: '2015-12-28T19:20:15'
modified_at: '2026-04-28T22:47:03'
modified_at_gmt: '2026-04-28T22:47:03'
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
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/shutterstock_288348143.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# EXPLAIN FORMAT=JSON provides insights on optimizer_switch effectiveness

Source: [Percona Blog](https://www.percona.com/blog/explain-formatjson-provides-insights-on-optimizer_switch-effectiveness/)

Auteur source: [Sveta Smirnova](https://www.percona.com/blog/author/sveta-smirnova/)

Publication: 2015-12-28T19:20:15

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The previous post in the EXPLAIN FORMAT=JSON is Cool! series showed an example of the query select dept_name from departments where dept_no in ( select dept_no from dept_manager where to_date is not null ) , where the subquery was materialized into a temporary table and then joined with the outer query. This is known as a semi-join optimization. But what happens if we turn off this optimization? EXPLAIN FORMAT = JSON can help us with this … Continued

## Images et graphiques reperes

- featured / image: [EXPLAIN FORMAT=JSON provides insights on optimizer_switch effectiveness](https://www.percona.com/wp-content/uploads/2026/03/shutterstock_288348143.jpg)
- content / image: [EXPLAIN FORMAT=JSON](https://www.percona.com/wp-content/uploads/2026/03/shutterstock_288348143-300x225.jpg)

## Auteur source

Sveta joined Percona in 2015. Her main professional interests are problem solving, working with tricky issues, bugs, finding patterns that can solve typical issues quicker and teaching others how to deal with MySQL issues, bugs and gotchas effectively. Before joining Percona Sveta worked as a Support Engineer in the MySQL Bugs Analysis Support Group in MySQL AB-Sun-Oracle. She is the author of the books "MySQL Troubleshooting" and "MySQL Cookbook, 4th Edition".
