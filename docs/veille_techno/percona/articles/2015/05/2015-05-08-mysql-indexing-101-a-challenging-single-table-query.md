---
title: 'MySQL indexing 101: a challenging single-table query'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-indexing-101-a-challenging-single-table-query/
  post_id: 9254
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2015-05-08T07:00:29'
published_at_gmt: '2015-05-08T07:00:29'
modified_at: '2026-05-04T22:37:37'
modified_at_gmt: '2026-05-04T22:37:37'
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
- InnoDB
- MySQL indexing
- Optimizer
- Primary
- single-table query
- Stephane Combaudon
tag_slugs:
- innodb
- mysql-indexing
- optimizer
- primary
- single-table-query
- stephane-combaudon
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL indexing 101: a challenging single-table query

Source: [Percona Blog](https://www.percona.com/blog/mysql-indexing-101-a-challenging-single-table-query/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2015-05-08T07:00:29

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

We discussed in an earlier post how to design indexes for many types of queries using a single table. Here is a real-world example of the challenges you will face when trying to optimize queries: two similar queries, but one is performing a full table scan while the other one is using the index we … Continued

## Structure detectee

- H2: Our two similar queries
- H2: Estimating the cost of an execution plan (simplified)
- H2: Optimizing our query
- H2: Conclusion

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
