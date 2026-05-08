---
title: 'used_columns: EXPLAIN FORMAT=JSON tells when you should use covered indexes'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/used_columns-explain-formatjson-tells-when-you-should-use-covered-index/
  post_id: 10251
source_author:
  name: Sveta Smirnova
  slug: sveta-smirnova
  url: https://www.percona.com/blog/author/sveta-smirnova/
  website: http://www.percona.com/blog/
published_at: '2015-12-14T23:04:00'
published_at_gmt: '2015-12-14T23:04:00'
modified_at: '2026-04-08T15:12:40'
modified_at_gmt: '2026-04-08T15:12:40'
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
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/shutterstock_290445947-300x200.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# used_columns: EXPLAIN FORMAT=JSON tells when you should use covered indexes

Source: [Percona Blog](https://www.percona.com/blog/used_columns-explain-formatjson-tells-when-you-should-use-covered-index/)

Auteur source: [Sveta Smirnova](https://www.percona.com/blog/author/sveta-smirnova/)

Publication: 2015-12-14T23:04:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In the “MySQL Query tuning 101” video, Alexander Rubin provides an excellent example of when to use a covered index. On slide 25, he takes the query select name from City where CountryCode = 'USA' and District = 'Alaska' and population > 10000 and adds the index cov1(CountryCode, District, population, name) on table City . With Alex’s query tuning experience, making the right index decision is simple – but what about us mere mortals? If a query is more … Continued

## Images et graphiques reperes

- featured / image: [used_columns: EXPLAIN FORMAT=JSON tells when you should use covered indexes](https://www.percona.com/wp-content/uploads/2026/03/shutterstock_290445947-300x200.jpg)

## Auteur source

Sveta joined Percona in 2015. Her main professional interests are problem solving, working with tricky issues, bugs, finding patterns that can solve typical issues quicker and teaching others how to deal with MySQL issues, bugs and gotchas effectively. Before joining Percona Sveta worked as a Support Engineer in the MySQL Bugs Analysis Support Group in MySQL AB-Sun-Oracle. She is the author of the books "MySQL Troubleshooting" and "MySQL Cookbook, 4th Edition".
