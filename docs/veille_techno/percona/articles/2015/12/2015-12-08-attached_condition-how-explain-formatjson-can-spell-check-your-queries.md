---
title: 'attached_condition: How EXPLAIN FORMAT=JSON can spell-check your queries'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/attached_condition-how-explain-formatjson-can-spell-check-your-queries/
  post_id: 10233
source_author:
  name: Sveta Smirnova
  slug: sveta-smirnova
  url: https://www.percona.com/blog/author/sveta-smirnova/
  website: http://www.percona.com/blog/
published_at: '2015-12-08T18:49:38'
published_at_gmt: '2015-12-08T18:49:38'
modified_at: '2026-04-28T22:46:13'
modified_at_gmt: '2026-04-28T22:46:13'
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
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/query_1_time.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# attached_condition: How EXPLAIN FORMAT=JSON can spell-check your queries

Source: [Percona Blog](https://www.percona.com/blog/attached_condition-how-explain-formatjson-can-spell-check-your-queries/)

Auteur source: [Sveta Smirnova](https://www.percona.com/blog/author/sveta-smirnova/)

Publication: 2015-12-08T18:49:38

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

When you work with complicated queries, especially ones which contain subqueries, it is easy to make a typo or misinterpret column name. While in many cases you will receive a column not found error, sometimes you can get strange results instead. Like finding 4079 countries in Antarctica: MySQL mysql> select count(*) from City where CountryCode in (select CountryCode from Country where Continent = 'Antarctica'); +----------+ | count(*) | +----------+ | 4079 | +----------+ 1 row in set (0.05 sec) 1 2 3 4 5 6 7 mysql > select count (*) from City where CountryCode in ( select CountryCode from Country where Continent = 'Antarctica' ); +----------+ | count (*) | +----------+ | 4079 | +----------+ 1 row in set (0.05 sec) Or not finding any cities in Georgia: MySQL mysql> select Name, Language from City join CountryLanguage using (CountryCode) where CountryCode in (select Code from Country w...

## Images et graphiques reperes

- featured / image: [attached_condition: How EXPLAIN FORMAT=JSON can spell-check your queries](https://www.percona.com/wp-content/uploads/2026/03/query_1_time.png)
- content / image: [attached_condition](https://www.percona.com/wp-content/uploads/2026/03/shutterstock_100808500-300x300.jpg)

## Auteur source

Sveta joined Percona in 2015. Her main professional interests are problem solving, working with tricky issues, bugs, finding patterns that can solve typical issues quicker and teaching others how to deal with MySQL issues, bugs and gotchas effectively. Before joining Percona Sveta worked as a Support Engineer in the MySQL Bugs Analysis Support Group in MySQL AB-Sun-Oracle. She is the author of the books "MySQL Troubleshooting" and "MySQL Cookbook, 4th Edition".
