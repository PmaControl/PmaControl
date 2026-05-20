---
title: 'EXPLAIN FORMAT=JSON: cost_info knows why optimizer prefers one index to another'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/cost_info-knows-why-optimizer-prefers-one-index-to-another/
  post_id: 10338
source_author:
  name: Sveta Smirnova
  slug: sveta-smirnova
  url: https://www.percona.com/blog/author/sveta-smirnova/
  website: http://www.percona.com/blog/
published_at: '2016-02-22T16:41:39'
published_at_gmt: '2016-02-22T16:41:39'
modified_at: '2026-04-28T22:48:37'
modified_at_gmt: '2026-04-28T22:48:37'
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
- cost_info
- explain
- EXPLAIN FORMAT=JSON
- EXPLAIN FORMAT=JSON is Cool!
- json
- MySQL Query Tuning
tag_slugs:
- cost_info
- explain
- explain-formatjson
- explain-formatjson-is-cool
- json
- mysql-query-tuning
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/EXPLAIN-FORMATJSON.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# EXPLAIN FORMAT=JSON: cost_info knows why optimizer prefers one index to another

Source: [Percona Blog](https://www.percona.com/blog/cost_info-knows-why-optimizer-prefers-one-index-to-another/)

Auteur source: [Sveta Smirnova](https://www.percona.com/blog/author/sveta-smirnova/)

Publication: 2016-02-22T16:41:39

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Time for another entry in the EXPLAIN FORMAT=JSON is cool! series of blog posts. This time we’ll discuss how using EXPLAIN FORMAT=JSON allows you to see that cost_info knows why the optimizer prefers one index to another. Tables often have more than one index. Any of these indexes can be used to resolve query. The … Continued

## Images et graphiques reperes

- featured / image: [EXPLAIN FORMAT=JSON: cost_info knows why optimizer prefers one index to another](https://www.percona.com/wp-content/uploads/2026/03/EXPLAIN-FORMATJSON.png)

## Auteur source

Sveta joined Percona in 2015. Her main professional interests are problem solving, working with tricky issues, bugs, finding patterns that can solve typical issues quicker and teaching others how to deal with MySQL issues, bugs and gotchas effectively. Before joining Percona Sveta worked as a Support Engineer in the MySQL Bugs Analysis Support Group in MySQL AB-Sun-Oracle. She is the author of the books "MySQL Troubleshooting" and "MySQL Cookbook, 4th Edition".
