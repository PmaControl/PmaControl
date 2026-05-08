---
title: MongoDB Explain – Using PMM-QAN for MongoDB Query Analytics
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mongodb-explain-using-pmm-qan-for-mongodb-query-analytics/
  post_id: 18809
source_author:
  name: Dev Montiontactic
  slug: mt_admin
  url: https://www.percona.com/blog/author/mt_admin/
  website: ''
published_at: '2018-06-26T12:12:37'
published_at_gmt: '2018-06-26T12:12:37'
modified_at: '2026-03-26T20:19:00'
modified_at_gmt: '2026-03-26T20:19:00'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- search:percona-monitoring-and-management
- search:pmm
categories:
- Insight for DBAs
- MongoDB
- Percona Software
category_slugs:
- insight-for-dbas
- mongodb
- percona-software
tags:
- Explain command
- indexes
- indexing
- pmm qan
tag_slugs:
- explain-command
- indexes
- indexing
- pmm-qan
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/mongo-db-explain-plan.jpg
image_count: 9
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MongoDB Explain – Using PMM-QAN for MongoDB Query Analytics

Source: [Percona Blog](https://www.percona.com/blog/mongodb-explain-using-pmm-qan-for-mongodb-query-analytics/)

Auteur source: [Dev Montiontactic](https://www.percona.com/blog/author/mt_admin/)

Publication: 2018-06-26T12:12:37

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we will walk through PMM-Query Analytics for MongoDB. We will see how to analyze MongoDB query performance; review the initial parameters that we need to check; and find out how to compare MongoDB query performance with and without indexes with the help of EXPLAIN plan. The Percona Monitoring and Management QAN (PMM-QAN) dashboard … Continued

## Structure detectee

- H2: Test Case Environment
- H2: QAN Analysis
- H3: Query Time
- H3: EXPLAIN PLAN
- H3: COMPARISON OF PERFORMANCE

## Images et graphiques reperes

- featured / image: [MongoDB Explain – Using PMM-QAN for MongoDB Query Analytics](https://www.percona.com/wp-content/uploads/2026/03/mongo-db-explain-plan.jpg)
- content / image: [1-4.png](https://www.percona.com/wp-content/uploads/2026/03/1-4.png)
- content / image: [find_withoutIndex.png](https://www.percona.com/wp-content/uploads/2026/03/find_withoutIndex.png)
- content / image: [find_WithIndex.png](https://www.percona.com/wp-content/uploads/2026/03/find_WithIndex.png)
- content / image: [Explain_toggle.png](https://www.percona.com/wp-content/uploads/2026/03/Explain_toggle.png)
- content / image: [collscan.png](https://www.percona.com/wp-content/uploads/2026/03/collscan.png)
- content / image: [IXSCAN.png](https://www.percona.com/wp-content/uploads/2026/03/IXSCAN.png)
- content / image: [returnNoINdex.png](https://www.percona.com/wp-content/uploads/2026/03/returnNoINdex.png)
- content / image: [returnIndex.png](https://www.percona.com/wp-content/uploads/2026/03/returnIndex.png)
