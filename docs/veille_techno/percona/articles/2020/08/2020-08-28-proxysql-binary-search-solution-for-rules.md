---
title: ProxySQL Binary Search Solution for Rules
source:
  name: Percona Blog
  url: https://www.percona.com/blog/proxysql-binary-search-solution-for-rules/
  post_id: 22800
source_author:
  name: David Ducos
  slug: david-ducos
  url: https://www.percona.com/blog/author/david-ducos/
  website: ''
published_at: '2020-08-28T16:05:48'
published_at_gmt: '2020-08-28T16:05:48'
modified_at: '2026-04-27T22:08:30'
modified_at_gmt: '2026-04-27T22:08:30'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- ProxySQL
matched_filters:
- category:mysql:83
- category:proxysql:2261
- search:proxysql
categories:
- Insight for DBAs
- MySQL
- Open Source
- ProxySQL
category_slugs:
- insight-for-dbas
- mysql
- open-source
- proxysql
tags:
- MySQL
- mysql-and-variants
- ProxySQL
- search database
tag_slugs:
- mysql
- mysql-and-variants
- proxysql
- search-database
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Proxy.png
image_count: 5
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# ProxySQL Binary Search Solution for Rules

Source: [Percona Blog](https://www.percona.com/blog/proxysql-binary-search-solution-for-rules/)

Auteur source: [David Ducos](https://www.percona.com/blog/author/david-ducos/)

Publication: 2020-08-28T16:05:48

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

We sometimes receive challenging requests… this is a story about one of those times. The customer has implemented a sharding solution and would like us to review alternatives or improvements. We analyzed the possibility of using ProxySQL as it looked to be a simple implementation. However, as we had 200 shards we had to implement … Continued

## Structure detectee

- H2: One Problem, Two Solutions
- H2: Solution
- H3: Base case
- H3: Binary search use case
- H3: Rule evaluation
- H3: Tests
- H3: Conclusions

## Images et graphiques reperes

- featured / image: [ProxySQL Binary Search Solution for Rules](https://www.percona.com/wp-content/uploads/2026/03/Proxy.png)
- content / image: [Proxy-300x157.png](https://www.percona.com/wp-content/uploads/2026/03/Proxy-300x157.png)
- content / image: [Rules-Map-BP.png](https://www.percona.com/wp-content/uploads/2026/03/Rules-Map-BP.png)
- content / image: [CPU_Average-1-1024x512.png](https://www.percona.com/wp-content/uploads/2026/03/CPU_Average-1-1024x512.png)
- content / image: [CPU_Average-1024x512.png](https://www.percona.com/wp-content/uploads/2026/03/CPU_Average-1024x512.png)

## Auteur source

David studied Computer Science in National University of La Plata and has worked as a DBA consultant since 2008. For the past 3 years he worked with a worldwide platform of free classifieds up until he joined Percona's consulting team in November 2014. David lives near Buenos Aires, Argentina and in his free time loves to spend time with his family.
