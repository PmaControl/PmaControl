---
title: Why Optimization derived_merge can Break Your Queries
source:
  name: Percona Blog
  url: https://www.percona.com/blog/why-optimization-derived_merge-can-break-your-queries/
  post_id: 19215
source_author:
  name: Sveta Smirnova
  slug: sveta-smirnova
  url: https://www.percona.com/blog/author/sveta-smirnova/
  website: http://www.percona.com/blog/
published_at: '2018-09-25T14:51:40'
published_at_gmt: '2018-09-25T14:51:40'
modified_at: '2026-05-05T20:21:30'
modified_at_gmt: '2026-05-05T20:21:30'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
category_slugs:
- mysql
tags:
- Bugs
- Debugging
- derived_merge
- MySQL Optimizer
- MySQL Query Tuning
- Optimizer
- optimizer_switch
tag_slugs:
- bugs
- debugging
- derived_merge
- mysql-optimizer
- mysql-query-tuning
- optimizer
- optimizer_switch
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/three-mysql-query-bugs.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Why Optimization derived_merge can Break Your Queries

Source: [Percona Blog](https://www.percona.com/blog/why-optimization-derived_merge-can-break-your-queries/)

Auteur source: [Sveta Smirnova](https://www.percona.com/blog/author/sveta-smirnova/)

Publication: 2018-09-25T14:51:40

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Lately, I worked on several queries which started returning wrong results after upgrading MySQL Server to version 5.7 The reason for the failure was derived merge optimization which is one of the default optimizer_switch options. Issues were solved, though at the price of performance, when we turned it OFF . But, more importantly, we could … Continued

## Structure detectee

- H2: Analyzing the problem
- H2: Case Study 1: a Query from Bug # 85117
- H2: Case Study 2: a Query from Bug # 91418
- H2: Case Study 3: a Query from Bug # 91878
- H2: Conclusion and recommendations

## Images et graphiques reperes

- featured / image: [Why Optimization derived_merge can Break Your Queries](https://www.percona.com/wp-content/uploads/2026/03/three-mysql-query-bugs.jpg)

## Auteur source

Sveta joined Percona in 2015. Her main professional interests are problem solving, working with tricky issues, bugs, finding patterns that can solve typical issues quicker and teaching others how to deal with MySQL issues, bugs and gotchas effectively. Before joining Percona Sveta worked as a Support Engineer in the MySQL Bugs Analysis Support Group in MySQL AB-Sun-Oracle. She is the author of the books "MySQL Troubleshooting" and "MySQL Cookbook, 4th Edition".
