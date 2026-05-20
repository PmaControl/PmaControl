---
title: 'MySQL Limitations Part 3: Subqueries'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-limitations-part-3-subqueries/
  post_id: 2481
source_author:
  name: Baron Schwartz
  slug: baron
  url: https://www.percona.com/blog/author/baron/
  website: http://www.percona.com/
published_at: '2010-10-25T18:22:05'
published_at_gmt: '2010-10-25T18:22:05'
modified_at: '2026-04-28T21:06:12'
modified_at_gmt: '2026-04-28T21:06:12'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for Developers
- MySQL
category_slugs:
- insight-for-developers
- mysql
tags:
- subquery
tag_slugs:
- subquery
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL Limitations Part 3: Subqueries

Source: [Percona Blog](https://www.percona.com/blog/mysql-limitations-part-3-subqueries/)

Auteur source: [Baron Schwartz](https://www.percona.com/blog/author/baron/)

Publication: 2010-10-25T18:22:05

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This is the third in a series on whatâ€™s seriously limiting MySQL in certain circumstances (links: part 1, 2). This post is about subqueries, which in some cases execute outside-in instead of inside-out as users expect.

## Auteur source

Baron is the lead author of High Performance MySQL. He is a former Percona employee.
