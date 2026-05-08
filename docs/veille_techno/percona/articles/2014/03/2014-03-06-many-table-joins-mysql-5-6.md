---
title: Many-table joins in MySQL 5.6
source:
  name: Percona Blog
  url: https://www.percona.com/blog/many-table-joins-mysql-5-6/
  post_id: 7905
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2014-03-06T14:37:25'
published_at_gmt: '2014-03-06T14:37:25'
modified_at: '2026-05-04T22:15:52'
modified_at_gmt: '2026-05-04T22:15:52'
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
- joins
- MySQL 5.6
- Optimizer
- Stephane Combaudon
tag_slugs:
- explain
- joins
- mysql-5-6
- optimizer
- stephane-combaudon
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Many-table joins in MySQL 5.6

Source: [Percona Blog](https://www.percona.com/blog/many-table-joins-mysql-5-6/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2014-03-06T14:37:25

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I recently worked on an uncommon slow query: less than 100 rows were read and returned, the whole dataset was fitting in memory but the query took several seconds to run. Long story short: the query was a join involving 21 tables, running on MySQL 5.1. But by default MySQL 5.1 is not good at … Continued

## Structure detectee

- H2: Isolating the problem
- H2: MySQL 5.6
- H2: Conclusions

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
