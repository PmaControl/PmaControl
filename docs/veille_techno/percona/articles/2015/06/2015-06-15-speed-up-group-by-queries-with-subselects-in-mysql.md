---
title: Speed up MySQL Queries GROUP BY with subselects
source:
  name: Percona Blog
  url: https://www.percona.com/blog/speed-up-group-by-queries-with-subselects-in-mysql/
  post_id: 9264
source_author:
  name: David Ducos
  slug: david-ducos
  url: https://www.percona.com/blog/author/david-ducos/
  website: ''
published_at: '2015-06-15T18:32:18'
published_at_gmt: '2015-06-15T18:32:18'
modified_at: '2026-04-28T22:20:21'
modified_at_gmt: '2026-04-28T22:20:21'
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
- David Ducos
- GROUP BY
- indexes
- MySQL
- Primary
- queries
- subselects
tag_slugs:
- david-ducos
- group-by
- indexes
- mysql
- primary
- queries
- subselects
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Speed-up-queries-on-MySQL.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Speed up MySQL Queries GROUP BY with subselects

Source: [Percona Blog](https://www.percona.com/blog/speed-up-group-by-queries-with-subselects-in-mysql/)

Auteur source: [David Ducos](https://www.percona.com/blog/author/david-ducos/)

Publication: 2015-06-15T18:32:18

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

We usually try to avoid subselects because sometimes they force the use of a temporary table and limits the use of indexes. But, when is good to use a subselect? This example was tested over table a (1310723 rows), b, c and d ( 5 rows each) and with MySQL version 5.5 and 5.6. Let’s … Continued

## Structure detectee

- H2: Speed up MySQL queries

## Images et graphiques reperes

- featured / image: [Speed up MySQL Queries GROUP BY with subselects](https://www.percona.com/wp-content/uploads/2026/03/Speed-up-queries-on-MySQL.png)

## Auteur source

David studied Computer Science in National University of La Plata and has worked as a DBA consultant since 2008. For the past 3 years he worked with a worldwide platform of free classifieds up until he joined Percona's consulting team in November 2014. David lives near Buenos Aires, Argentina and in his free time loves to spend time with his family.
