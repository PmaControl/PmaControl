---
title: Is there a performance difference between JOIN and WHERE?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/is-there-a-performance-difference-between-join-and-where/
  post_id: 2283
source_author:
  name: Baron Schwartz
  slug: baron
  url: https://www.percona.com/blog/author/baron/
  website: http://www.percona.com/
published_at: '2010-04-15T01:06:13'
published_at_gmt: '2010-04-15T01:06:13'
modified_at: '2026-04-28T21:10:49'
modified_at_gmt: '2026-04-28T21:10:49'
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
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Is there a performance difference between JOIN and WHERE?

Source: [Percona Blog](https://www.percona.com/blog/is-there-a-performance-difference-between-join-and-where/)

Auteur source: [Baron Schwartz](https://www.percona.com/blog/author/baron/)

Publication: 2010-04-15T01:06:13

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I’ve heard this question a lot, but never thought to blog about the answer. “Is there a performance difference between putting the JOIN conditions in the ON clause or the WHERE clause in MySQL?” No, there’s no difference. The following queries are algebraically equivalent inside MySQL and will have the same execution plan. … Continued

## Auteur source

Baron is the lead author of High Performance MySQL. He is a former Percona employee.
