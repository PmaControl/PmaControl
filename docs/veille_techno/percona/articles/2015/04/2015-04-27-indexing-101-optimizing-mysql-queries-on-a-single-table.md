---
title: 'Indexing 101: Optimizing MySQL queries on a single table'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/indexing-101-optimizing-mysql-queries-on-a-single-table/
  post_id: 9199
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2015-04-27T10:00:11'
published_at_gmt: '2015-04-27T10:00:11'
modified_at: '2026-05-04T22:36:44'
modified_at_gmt: '2026-05-04T22:36:44'
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
- MySQL optimization
- MySQL Performance
- MySQL queries
- Primary
- Stephane Combaudon
tag_slugs:
- mysql-optimization
- mysql-performance
- mysql-queries
- primary
- stephane-combaudon
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Indexing 101: Optimizing MySQL queries on a single table

Source: [Percona Blog](https://www.percona.com/blog/indexing-101-optimizing-mysql-queries-on-a-single-table/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2015-04-27T10:00:11

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I have recently seen several cases when performance for MySQL queries on a single table was terrible. The reason was simple: the wrong indexes were added and so the execution plan was poor. Here are guidelines to help you optimize various kinds of single-table queries. Disclaimer: I will be presenting general guidelines and I do … Continued

## Structure detectee

- H2: What an index can do for you
- H2: Single equality
- H2: Multiple equalities
- H2: Equality and inequality
- H2: Multiple inequalities
- H2: Equalities and sort
- H2: Inequality and sort
- H2: Conclusion

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
