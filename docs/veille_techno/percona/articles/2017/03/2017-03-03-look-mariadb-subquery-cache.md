---
title: A Look at MariaDB Subquery Cache
source:
  name: Percona Blog
  url: https://www.percona.com/blog/look-mariadb-subquery-cache/
  post_id: 16268
source_author:
  name: Federico Razzoli
  slug: federico-razzoli
  url: https://www.percona.com/blog/author/federico-razzoli/
  website: ''
published_at: '2017-03-03T02:11:59'
published_at_gmt: '2017-03-03T02:11:59'
modified_at: '2026-05-05T18:31:17'
modified_at_gmt: '2026-05-05T18:31:17'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MariaDB
- MySQL
matched_filters:
- category:mariadb:1281
- category:mysql:83
categories:
- MariaDB
- MySQL
category_slugs:
- mariadb
- mysql
tags:
- MariaDB
- Performance
- Subquery cache
tag_slugs:
- mariadb
- performance
- subquery-cache
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MariaDB-Subquery-Cache-e1488506349867.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# A Look at MariaDB Subquery Cache

Source: [Percona Blog](https://www.percona.com/blog/look-mariadb-subquery-cache/)

Auteur source: [Federico Razzoli](https://www.percona.com/blog/author/federico-razzoli/)

Publication: 2017-03-03T02:11:59

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The MariaDB subquery cache feature added in MariaDB 5.3 is not widely known. Let’s see what it is and how it works. What is a subquery cache? The MariaDB subquery cache optimizes the execution of correlated subqueries. Correlated subqueries refer to a value from the parent query. For example: Shell SELECT id FROM product WHERE price NOT IN (SELECT MAX(price) FROM product GROUP BY category); 1 SELECT id FROM product WHERE price NOT IN ( SELECT MAX ( price ) FROM product GROUP BY category ) ; MariaDB only uses this optimization … Continued

## Structure detectee

- H2: What is a subquery cache?
- H2: How does subquery cache work?
- H2: Isn’t this subquery materialization?
- H3: Some considerations

## Images et graphiques reperes

- featured / image: [A Look at MariaDB Subquery Cache](https://www.percona.com/wp-content/uploads/2026/03/MariaDB-Subquery-Cache-e1488506349867.jpg)
