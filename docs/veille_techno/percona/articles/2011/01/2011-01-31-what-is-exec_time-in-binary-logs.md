---
title: What is exec_time in binary logs?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/what-is-exec_time-in-binary-logs/
  post_id: 2670
source_author:
  name: Baron Schwartz
  slug: baron
  url: https://www.percona.com/blog/author/baron/
  website: http://www.percona.com/
published_at: '2011-01-31T21:00:53'
published_at_gmt: '2011-01-31T21:00:53'
modified_at: '2026-03-23T21:51:01'
modified_at_gmt: '2026-03-23T21:51:01'
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
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# What is exec_time in binary logs?

Source: [Percona Blog](https://www.percona.com/blog/what-is-exec_time-in-binary-logs/)

Auteur source: [Baron Schwartz](https://www.percona.com/blog/author/baron/)

Publication: 2011-01-31T21:00:53

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

If you’ve used MySQL’s mysqlbinlog tool, you’ve probably seen something like the following in the output: “exec_time=0” What is the exec_time? It seems to be the query’s execution time, but it is not.

## Auteur source

Baron is the lead author of High Performance MySQL. He is a former Percona employee.
